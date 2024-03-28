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
    public function index(Request $request)
    {
        $rules = ['tailor_id' => 'required|numeric'];
        $validation = Validator::make($request->all(), $rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer Validation Error','data' => $validation->errors()] , 422); }
        else
        {
            $tailorcustomers = TailorCustomer::where('tailor_id',$request->tailor_id)->get();
            return response()->json($tailorcustomers);
        }
    }
    
    //param: tailor_id in request
    //count of customers for specific tailor
    public function countCustomers(Request $request)
    {
        $rules = ['tailor_id' => 'required|numeric'];
        $validation = Validator::make($request->all(), $rules);
        
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer Validation Error','data' => $validation->errors()] , 422); }
        else
        {
            $countCustomers = TailorCustomer::where('tailor_id',$request->tailor_id)->count();
            return response()->json(['success'=>true, 'message'=>'Customer Count','data' => ['countCustomer'=>$countCustomers] ] , 200);
        }
    }
    
    //param: phone number & tailor_id in request
    //get customer by phone number
    public function getCustomer(Request $request)
    {
        $rules = [
            'number' => 'required',
            'tailor_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
        $customer = TailorCustomer::where([['number',$request->number],['tailor_id',$request->tailor_id]])->first();
        if(empty($customer))
        { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 422); }
        else
        { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
        }
    }
    
    //param: customer_id & tailor_id in request
    //get customer by Id
    public function getCustomerById(Request $request)
    {
        $rules = [
            'customer_id' => 'required|numeric',
            'tailor_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
        $customer = TailorCustomer::where([['customer_id',$request->customer_id],['tailor_id',$request->tailor_id]])->first();
        if(empty($customer))
        { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 422); }
        else
        { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
        }
    }

    //param: searchText & tailor_id in request
    //get customer by name from searchText
    public function getCustomers(Request $request)
    {
        $rules = [
            'searchText' => '',
            'tailor_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $text = $request->searchText;
            $tailor_id = $request->tailor_id;
            // dd($text,$tailor_id);
            if($text=='' || $text==NULL || $text=="")
            {
                $customer = TailorCustomer::where('tailor_id',$tailor_id)->get();
                if(empty($customer))
                { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 422); }
                else
                { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
            }
            else
            {
                $customer = TailorCustomer::where([['tailor_id',$tailor_id],['name','LIKE','%'.$text.'%']])->get();
                if(empty($customer))
                { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 422); }
                else
                { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
            }
        }
    }

    //param: searchText & tailor_id in request
    //get customer by phone number from searchText
    public function getCustomersByNumber(Request $request)
    {
        $rules = [
            'searchText' => '',
            'tailor_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $text = $request->searchText;
            $tailor_id = $request->tailor_id;
            if($text=='' || $text==NULL || $text=="")
            {
                $customer = TailorCustomer::where('tailor_id',$tailor_id)->get();
                if(empty($customer))
                { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 422); }
                else
                { return response()->json(['success'=>true, 'message'=>'Customer Found','data' => ['customer'=>$customer] ] , 200); }
            }
            else
            {
                $customer = TailorCustomer::where([['tailor_id',$tailor_id],['number','LIKE','%'.$text.'%']])->get();
                if(empty($customer))
                { return response()->json(['success'=>false, 'message'=>'Customer Not Found','data' => [] ] , 422); }
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
    public function store(Request $request)
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
            'tailor_id' => 'required|integer',
        ];
        $validation = Validator::make($request->all(), $rules);
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $tailorcustomer = TailorCustomer::where([['number', $request->number],['tailor_id',$request->tailor_id]])->first();
            if(!empty($tailorcustomer))
            { return response()->json(['success'=>false, 'message'=>'Customer Already Exists','data' => $tailorcustomer->id ] , 422); }
            else
            {
                $tailorcustomer = TailorCustomer::create([
                    'number' => $request->number,
                    'name' => $request->name,
                    'address' => $request->address,
                    'gender' => $request->gender,
                    'picture' => $request->picture,
                    'city_name' => $request->city_name,
                    'tailor_id' => $request->tailor_id,
                    'customer_id' => $customer->id,
                ]);
                
                if($tailorcustomer->save()){
                    return response()->json(['success' => true , 'message' => 'Your Customer Created Successfully' , 'data' => ['id' => $tailorcustomer->id ] ] , 200);
                }else{
                    return response()->json(['success' => false , 'message' => 'Customer Creation Failed' , 'data' => [] ] , 422);
                }  
            }
    }  

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $rules = [
            'tailor_id' => 'required',
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
            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id],['tailor_id',$request->tailor_id]])->first();
            if(empty($tailorcustomer))
            { return response()->json(['success' => false , 'message' => 'Customer does not exist.' , 'data' => [] ] , 422); }
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
                    return response()->json(['success' => false , 'message' => 'Customer Updation Failed' , 'data' => [] ] , 422);
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
    public function destroy(Request $request)
    {
        $rules = [
            'tailor_id' => 'required|numeric',
            'customer_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(),$rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer Validation Error','data' => $validation->errors() ] , 422); }
        else
        {
            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id],['tailor_id',$request->tailor_id]])->first();
            if (empty($tailorcustomer))
            { return response()->json(['success' => false , 'message' => 'Customer does not exist.' , 'data' => [] ] , 422);}
            else
            {
                $tailorcustomer->delete();
                return response()->json(['success' => true  ,'message' => 'Customer Deleted successfully' , 'data' => [] ] , 200);
            }
        }
    }
}
