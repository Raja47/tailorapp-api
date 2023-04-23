<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{

    use SoftDeletes;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Shop::all());
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
            'tailor_id'         => 'required|max:255',
            'name'              => 'required|min:4|max:29',
            'contact_number'    => 'max:12|required',
            'contact_number2'   => 'max:12',
            'address'           => 'required',
            'picture'           => '',
            'city_id'           => '',
            'services_to_gender'=> '',
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Shop Validation Error' , 'data' => $validation->errors() ] , 422);
        } 
        // validation passed
        $shop = new Shop();
        $shop->tailor_id            = $request->input('tailor_id');
        $shop->name                 = $request->input('name');
        $shop->contact_number       = $request->input('contact_number');
        $shop->contact_number2      = $request->input('contact_number2');
        $shop->address              = $request->input('address');
        $shop->picture              = $request->input('picture');
        $shop->services_to_gender   = $request->input('services_to_gender');
        
        if($shop->save()){
            // Tailor is created
            return response()->json(['success' => true , 'message' => 'Shop Created Successfully' , 'data' => ['id' => $shop->id ] ] , 200);
        }else{
            return response()->json(['success' => false , 'message' => 'Shop creation failed' , 'data' => [] ] , 200);
        }     
    }   

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {   

        $validation = Validator::make( $request->all() ,[
            'shop_id'           => 'required',
            'name'              => 'required|min:4|max:29',
            'contact_number'    => 'max:12|required',
            'contact_number2'   => 'max:12',
            'address'           => 'required',
        ]);

        if($validation->fails()) {
            // validation failed
            return response()->json(['success' => false  ,'message' => 'Shop Validation Error' , 'data' => $validation->errors() ] , 422);
        } 
        // validation passed
        $shop = Shop::find($request->input('shop_id'));
        if(!empty($shop)){
            $shop->name                 = $request->input('name');
            $shop->contact_number       = $request->input('contact_number');
            $shop->contact_number2      = $request->input('contact_number2');
            $shop->address              = $request->input('address');
            $shop->picture              = $request->input('picture');
            $shop->city_id              = $request->input('city_id');
            $shop->services_to_gender   = $request->input('services_to_gender') ;
        }else{
            return response()->json(['success' => false , 'message' => 'Shop creation failed' , 'data' => [] ] , 422);        
        }
        
        if($shop->save()){
            // Tailor is created
            return response()->json(['success' => true , 'message' => 'Shop Created Successfully' , 'data' => ['id' => $shop->id ] ] , 200);
        }else{
            return response()->json(['success' => false , 'message' => 'Shop creation failed' , 'data' => [] ] , 422); 
        }     
    }  


    
}
