<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tailor;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class TailorController extends Controller
{

    use SoftDeletes;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Tailor::all());
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make( $request->all() ,[
            'name'     => 'required|max:255',
            'password' => 'required|min:4|max:12',
            'username' => 'required|unique:tailors|max:99',
            'number'   => 'required|unique:tailors|max:15'
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Validation Error' , 'data' => $validation->errors() ] , 422);
        } 
        // validation passed
        $tailor = new Tailor();
        $tailor->name               = $request->input('name');
        $tailor->password           = $request->input('password');
        $tailor->username           = $request->input('username');
        $tailor->number             = $request->input('number');
        $tailor->picture            = $request->input('picture');
        $tailor->country_code       = $request->input('country_code');
        $tailor->city_id            = $request->input('city_id');
        // $tailor->address            = $request->input('address');
        // $tailor->services_to_gender = $request->input('services_to_gender');
        $tailor->status             = $request->input('status');
        
        if($tailor->save()){
            // Tailor is created
            return response()->json(['success' => true , 'message' => 'Tailor Created Successfully' , 'data' => ['id' => $tailor->id ] ] , 200);
        }
        
        
    }   

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request) 
    {
        if( empty($request->input('number')) )
        {
            return response()->json(['success' => false  ,'message' => 'Search Criteria failed' , 'data' => [] ] , 422);
        }

        $tailorNumber = $request->input('number');

        $tailor = Tailor::where('number' , $tailorNumber)->first();
        
        if( !empty($tailor) ) 
        {
            return response()->json(['success' => true  ,'message' => '' , 'data' => ['tailor' => $tailor->toArray() ] ] , 200);     
        }

        return response()->json(['success' => false  ,'message' => '' , 'data' => [] ] , 422);

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request) 
    {   

        $validation = Validator::make( $request->all() ,[
            'password' => 'required',
            'number'  => 'required'
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Validation failed' , 'data' => $validation->errors() ] , 422);
        } 
        
       

        $tailorNumber = $request->input('number');
        $password = $request->input('password');

        $tailor = Tailor::with('shops')->where('number' , $tailorNumber)->where('password' ,$password)->first();
        
        if( empty($tailor) ) 
        {
            return response()->json(['success' => false  ,'message' => 'Incorrect Mobile number password' , 'data' => [] ] , 422);     
        }

        return response()->json(['success' => true  ,'message' => '' , 'data' => ['tailor' => $tailor->toArray() ] ] , 200);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validation = Validator::make( $request->all() ,[
            'password'      => 'required',
            'number'  => 'required|min:10|max:15'
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Validation failed' , 'data' => $validation->errors() ] , 422);
        } 
        
        $tailorNumber = $request->input('number');
        $password = $request->input('password');

        $tailor = Tailor::where('number' , $tailorNumber)->first();
        
        if( empty($tailor) ) 
        {
            return response()->json(['success' => false  ,'message' => 'Incorrect Mobile number password' , 'data' => [] ] , 200);     
        }

        $tailor->password = $password;

        if($tailor->save()){
            return response()->json(['success' => true  ,'message' => '' , 'data' => ['tailor' => $tailor->toArray() ] ] , 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   

        $validation = Validator::make( $request->all() ,[
            'id'      => 'required|numeric',
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['status' => 'error'  ,'message' => 'Validation failed' , 'data' => $validation->errors() ] , 422);
        } 
        
        $tailor = Tailor::find($request->input('id'));

        if( $tailor ) {
            
            $tailor->delete();
            return response()->json(['status' => 'success'  ,'message' => 'Tailor Deleted successfully' , 'data' => [] ] , 200);
        
        }else{

            return response()->json(['status' => 'error'  ,'message' => 'Tailor doesnt exist' , 'data' => [] ] , 404);
        
        }
    }

}



