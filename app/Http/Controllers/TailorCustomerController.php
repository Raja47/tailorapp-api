<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCustomer;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class TailorCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tailor_id)
    {
        if($tailor_id=='' || $tailor_id==NULL || $tailor_id=="" )
        {return 'tailor id is required';}
        else
        {
            $tailorcustomers = TailorCustomer::where('tailor_id',$tailor_id)->get();
            if(count($tailorcustomers)===0)
            { return response()->json(['success'=>false, 'message'=>'No Customers Added','data' => ['tailor_id'=>$tailor_id] ] , 200); }
            else
            { return response()->json(['success'=>true, 'message'=>'Customers Found','data' => ['tailor_id'=>$tailor_id, 'customers'=>$tailorcustomers] ] , 200); }
        
        }
    }

    //param: tailor_id in request
    //count of customers for specific tailor
    public function countCustomers($tailor_id)
    {
        $countCustomers = TailorCustomer::where('tailor_id',$tailor_id)->count();
        return response()->json(['success'=>true, 'message'=>'Customer Count','data' => ['tailor_id'=>$tailor_id, 'countCustomer'=>$countCustomers] ] , 200);
    }
    
    //param: phone number & tailor_id in request
    //get customer by phone number
    public function getCustomer($tailor_id, Request $request)
    {
        $rules = [
            'number' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
        $customer = TailorCustomer::where([['number',$request->number],['tailor_id',$tailor_id]])->first();
        if(empty($customer))
        { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 200); }
        else
        { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
        }
    }
    
    //param: customer_id & tailor_id in request
    //get customer by Id
    public function getCustomerById($tailor_id,$customer_id)
    {
        $customer = TailorCustomer::where([['customer_id',$customer_id],['tailor_id',$tailor_id]])->first();
        if(empty($customer))
        { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 200); }
        else
        { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
    }

    
    public function search($tailor_id, Request $request)
    {
        $rules = [
            'searchText' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $text = $request->searchText;
            if($text=='' || $text==NULL || $text=="")
            {
                $customer = TailorCustomer::where('tailor_id',$tailor_id)->get();
                if(count($customer)===0)
                { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 200); }
                else
                { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
            }
            else
            {
                $customer = TailorCustomer::where([['tailor_id',$tailor_id],['number','LIKE','%'.$text.'%']])->orwhere([['tailor_id',$tailor_id],['name','LIKE','%'.$text.'%']])->get();
                if(count($customer)===0)
                { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 200); }
                else
                { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
            }
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($tailor_id, Request $request)
    {
        $customer = Customer::where('number', $request->number)->first();

        if(empty($customer))
        { $customer = Customer::create(['number' => $request->number]); }

        $rules = [
            'number' => 'required|max:12',
            'name' => '',
            'address' => 'max:70',
            'picture' => '',
            'gender' => '',
            'city_name' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $tailorcustomer = TailorCustomer::where([['number', $request->number],['tailor_id',$tailor_id]])->first();
            if(!empty($tailorcustomer))
            { return response()->json(['success'=>false, 'message'=>'Customer Already Exists','data' => $tailorcustomer->id ] , 200); }
            else
            {
                $tailorcustomer = TailorCustomer::create([
                    'number' => $request->number,
                    'name' => $request->name,
                    'address' => $request->address,
                    'gender' => $request->gender,
                    'picture' => $request->picture,
                    'city_name' => $request->city_name,
                    'tailor_id' => $tailor_id,
                    'customer_id' => $customer->id,
                ]);
                
                if($tailorcustomer->save()){
                    return response()->json(['success' => true , 'message' => 'Your Customer Created Successfully' , 'data' => ['Tailor Customer id' => $tailorcustomer->id ] ] , 200);
                }else{
                    return response()->json(['success' => false , 'message' => 'Customer Creation Failed' , 'data' => [] ] , 500);
                }  
            }
    }  

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($tailor_id, Request $request)
    {
        $rules = [
            'customer_id' => 'required',
            'number' => 'required|max:12',
            'name' => '',
            'address' => 'max:70',
            'gender' => '',
            'picture' => '',
            'city_name' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id],['tailor_id',$tailor_id]])->first();
            if(empty($tailorcustomer))
            { return response()->json(['success' => false , 'message' => 'Customer does not exist.' , 'data' => [] ] , 200); }
            else
            {
                $tailorcustomer->number = $request->number;
                $tailorcustomer->name = $request->name;
                $tailorcustomer->address = $request->address;
                $tailorcustomer->gender = $request->gender;
                $tailorcustomer->picture = $request->picture;
                $tailorcustomer->city_name = $request->city_name;
                
                if($tailorcustomer->save()){
                    return response()->json(['success' => true , 'message' => 'Your Customer Updated Successfully' , 'data' => ['id' => $tailorcustomer->id ] ] , 200);
                }else{
                    return response()->json(['success' => false , 'message' => 'Customer Updation Failed' , 'data' => [] ] , 500);
                }  
            }
        }  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tailor_id, Request $request)
    {
        $rules = [
            'customer_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(),$rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer Validation Error','data' => $validation->errors() ] , 422); }
        else
        {
            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id],['tailor_id',$tailor_id]])->first();
            if (empty($tailorcustomer))
            { return response()->json(['success' => false , 'message' => 'Customer does not exist.' , 'data' => [] ] , 404);}
            else
            {
                $tailorcustomer->delete();
                return response()->json(['success' => true  ,'message' => 'Customer Deleted successfully' , 'data' => [] ] , 200);
            }
        }
    }
}
