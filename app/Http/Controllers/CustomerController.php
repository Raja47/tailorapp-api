<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Customer::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'number' => 'required|max:12|unique:customers',
            'name' => '',
            'address' => 'max:70',
            'picture' => '',
            'email' => '',
            'password' => 'digits:6',
            'gender' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $customer = Customer::create([
                'number' => $request->number,
                'name' => $request->name,
                'address' => $request->address,
                'gender' => $request->gender,
                'picture' => $request->picture,
                'email' => $request->email,
                'password' => $request->password,
                'gender' => $request->gender,

            ]);

            if($customer->save()){
                return response()->json(['success' => true , 'message' => 'Customer Created Successfully' , 'data' => ['id' => $customer->id ] ] , 200);
            }else{
                return response()->json(['success' => false , 'message' => 'Customer Creation Failed' , 'data' => [] ] , 422);
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
        $customer = Customer::find()->where('id', $request->customer_id);

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
            'customer_id' => 'required|numeric',
            'number' => 'required|max:12',
            'name' => '',
            'address' => 'max:70',
            'gender' => '',
            'picture' => '',
            'email' => '',
            'password' => 'digits:6',
            'gender' => '',
        ];
        
        $validation = Validator::make($request->all(), $rules);
        
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer Validation Error','data' => $validation->errors() ] , 422); }
        
        else 
        {
            $customer = Customer::where('id', $request->customer_id)->first();
            if (empty($customer))
            { return response()->json(['success' => false , 'message' => 'Customer does not exist.' , 'data' => [] ] , 422);}
            else
            {
                $customer->number = $request->number;
                $customer->name = $request->name;
                $customer->address = $request->address;
                $customer->gender = $request->gender;
                $customer->picture = $request->picture;
                $customer->email = $request->email;
                $customer->password = $request->password;
                $customer->gender = $request->gender;
    
                if($customer->save()){
                    return response()->json(['success' => true , 'message' => 'Customer Updated Successfully' , 'data' => ['id' => $customer->id ] ] , 200);
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
        $rules = ['customer_id' => 'required|numeric'];
        $validation = Validator::make($request->all(),$rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Customer Validation Error','data' => $validation->errors() ] , 422); }
        else
        {
            $customer = Customer::find()->where('id', $request->customer_id);
            if (empty($customer))
            { return response()->json(['success' => false , 'message' => 'Customer does not exist.' , 'data' => [] ] , 422);}
            else
            {
                $customer->delete();
                return response()->json(['success' => true  ,'message' => 'Customer Deleted successfully' , 'data' => [] ] , 200);
            }
            
        }
    }
}
