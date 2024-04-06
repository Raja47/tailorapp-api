<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryParameter;
use Illuminate\Support\Facades\Validator;

class CategoryParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($category_id)
    {
        $parameters = CategoryParameter::where('category_id',$category_id)->get();
        if(count($parameters)===0)
        { return response()->json(['category_id'=>$category_id,'parameters'=>'Not Found']); }
        else
        { return response()->json(['category_id'=>$category_id,'parameters'=>$parameters]); }
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
            'label' => '',
            'category_id' => 'required',
            'parameter_id' => 'required',
            'part' => '',
            'status' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Catgeory parameter data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $category_parameter = CategoryParameter::create([
                'label' => $request->label,
                'category_id' => $request->category_id,
                'parameter_id' => $request->parameter_id,
                'part' => $request->part,
                'status' => $request->status,
            ]);

            if($category_parameter->save()){
                return response()->json(['success' => true , 'message' => 'Catgeory parameter Created Successfully' , 'data' => ['id' => $category_parameter->id ] ] , 200);
            }else{
                return response()->json(['success' => false , 'message' => 'Catgeory parameter Creation Failed' , 'data' => [] ] , 422);
            }   
        }
    }

}
