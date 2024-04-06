<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parameter;
use Illuminate\Support\Facades\Validator;

class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Parameter::all());
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
            'name' => 'required',
            'label' => '',
            'image' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Parameter data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $parameter = Parameter::create([
                'name' => $request->name,
                'label' => $request->label,
                'image' => $request->image,
            ]);

            if($parameter->save()){
                return response()->json(['success' => true , 'message' => 'Parameter Created Successfully' , 'data' => ['id' => $parameter->id ] ] , 200);
            }else{
                return response()->json(['success' => false , 'message' => 'Parameter Creation Failed' , 'data' => [] ] , 422);
            }            
        }
    }
}