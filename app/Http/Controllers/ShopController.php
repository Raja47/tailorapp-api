<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Tailor;
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
        $tailorId = auth('sanctum')->user()->id;
        return response()->json(['success' => true, 'data' => Shop::where('tailor_id', $tailorId)->get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|min:4|max:29',
            'contact_number'    => 'max:12|required',
            'contact_number2'   => 'max:12',
            'address'           => 'required',
            'country_code'      => 'required',
            'services_to_gender'=> 'required',          
        ]);

        $tailorId = auth()->user()->id;

        $tailor = Tailor::find($tailorId);
        if(empty($tailor)){
            return response()->json(['success' => false , 'message' => 'Tailor not found' , 'data' => [] ] , 404);
        }

        // validation passed
        $shop = new Shop();
        $shop->tailor_id            = $request->input('tailor_id');
        $shop->name                 = $request->input('name');
        $shop->contact_number       = $request->input('contact_number');
        $shop->country_code         = $tailor->country_code;
        $shop->contact_number2      = $request->input('contact_number2');
        $shop->city_name            = $request->input('city_name');
        $shop->address              = $request->input('address');
        $shop->picture              = $request->input('picture');
        $shop->services_to_gender   = $request->input('services_to_gender');
        
        if($shop->save()){
            // Tailor is created
            return response()->json(['success' => true , 'message' => 'Shop Created Successfully' , 'data' => ['shop' => $shop ] ] , 200);
        } 
        
        return response()->json(['success' => false , 'message' => 'Shop Creation Failed' , 'data' => [] ] , 500);
    }   

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {   
        $request->validate([
            'shop_id'           => 'required',
            'name'              => 'required|min:4|max:29',
            'contact_number'    => 'required',
            'address'           => 'required',
            'countrue_code'     => 'required',
            'services_to_gender'=> 'required',
        ]);
    
        $shop = Shop::find($request->input('shop_id'));

        if( empty($shop) || $shop->tailor_id != auth()->user()->id){
            return response()->json(['success' => false , 'message' => 'Shop not found' , 'data' => [] ] , 404);
        }

        $shop->name                 = $request->input('name');
        $shop->contact_number       = $request->input('contact_number');
        $shop->contact_number2      = $request->input('contact_number2');
        $shop->country_code         = $request->input('country_code');
        $shop->address              = $request->input('address');
        $shop->picture              = $request->input('picture');
        $shop->city_name            = $request->input('city_name');
        $shop->services_to_gender   = $request->input('services_to_gender') ;
    
        if($shop->save()){
            // Shop is updated
            return response()->json(['success' => true , 'message' => 'Shop Updated Successfully' , 'data' => ['shop' => $shop ] ] , 200);
        }
        
        return response()->json(['success' => false , 'message' => 'Shop Updation failed' , 'data' => [] ] , 500); 
    }  

    public function destroy(Request $request , $shopId)
    {
        $request->validate([
            'shop_id' => 'required'
        ]);

        $shop = Shop::find($request->input('shop_id'));
        
        if(empty($shop)){
            return response()->json(['success' => false , 'message' => 'Shop not found' , 'data' => [] ] , 404);
        }

        if($shop->tailor_id  != auth()->user()->id) {  // Improve this check if you want to allow admins to delete any shop only
            return response()->json(['success' => false , 'message' => 'Unauthorized Action' , 'data' => [] ] , 401);
        }; 

        if($shop->delete()){
            // Shop is deleted
            return response()->json(['success' => true , 'message' => 'Shop Deleted Successfully' , 'data' => [] ] , 200);
        }

        return response()->json(['success' => false , 'message' => 'Shop Deletion Failed' , 'data' => [] ] , 500); 
     }


    
}
