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
            'tailorName'    => 'required|max:255',
            'password'      => 'required|min:4|max:12',
            'username'      => 'required|unique:tailors|max:99',
            'tailorNumber'  => 'required|unique:tailors|max:15'
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Validation Error' , 'data' => $validation->errors() ] , 422);
        } 
        // validation passed
        $tailor = new Tailor();
        $tailor->tailorName         = $request->input('tailorName');
        $tailor->password           = $request->input('password');
        $tailor->username           = $request->input('username');
        $tailor->tailorNumber       = $request->input('tailorNumber');
        $tailor->picture            = $request->input('picture');
        $tailor->country_id         = $request->input('country_id');
        $tailor->city_id            = $request->input('city_id');
        $tailor->address            = $request->input('address');
        $tailor->servicesToGender   = $request->input('servicesToGender');
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
        if( empty($request->all()) )
        {
            return response()->json(['success' => false  ,'message' => 'Search Criteria failed' , 'data' => [] ] , 422);
        }

        $tailorNumber = $request->input('tailorNumber');

        $tailor = Tailor::where('tailorNumber' , $tailorNumber)->first();
        
        if( !empty($tailor) ) 
        {
            return response()->json(['success' => true  ,'message' => '' , 'data' => ['tailor' => $tailor->toArray() ] ] , 200);     
        }

        return response()->json(['success' => true  ,'message' => '' , 'data' => [] ] , 200);

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
            'password'      => 'required',
            'tailorNumber'  => 'required'
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Validation failed' , 'data' => $validation->errors() ] , 422);
        } 
        
       

        $tailorNumber = $request->input('tailorNumber');
        $password = $request->input('password');

        $tailor = Tailor::where('tailorNumber' , $tailorNumber)->where('password' ,$password)->first();
        
        if( !empty($tailor) ) 
        {
            return response()->json(['success' => true  ,'message' => 'Incorrect Mobile number password' , 'data' => [] ] , 200);     
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
    public function changePassword(Request $request, $id)
    {
        $validation = Validator::make( $request->all() ,[
            'password'      => 'required',
            'tailorNumber'  => 'required|min:6|max:12'
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Validation failed' , 'data' => $validation->errors() ] , 422);
        } 
        
        $tailorNumber = $request->input('tailorNumber');
        $password = $request->input('password');

        $tailor = Tailor::where('tailorNumber' , $tailorNumber)->first();
        
        if( !empty($tailor) ) 
        {
            return response()->json(['success' => true  ,'message' => 'Incorrect Mobile number password' , 'data' => [] ] , 200);     
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
    public function destroy($id)
    {
        //
    }
}
