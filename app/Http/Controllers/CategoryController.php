<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        if(count($categories)===0)
        { return response()->json(['success'=>false, 'message'=>'No Category Exists','data' => NULL ] , 422); }
        else
        { return response()->json(['success'=>true, 'message'=>'Categories Found','data' => ['categories'=>$categories] ] , 200); }

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
            'name' => 'required|unique:categories',
            'label' => '',
            'gender' => '',
            'image' => '',
            'is_suggested' => '',
            // 'status' => '',    
        ];

        $validation = Validator::make($request->all(), $rules);
        
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Category Validation Error','data' => $validation->errors()] , 422); }
        else
        {
            $category = Category::create([
                'name' => $request->name,
                'label' => $request->label,
                'gender' => $request->gender,
                'image' => $request->image,
                'is_suggested' => $request->is_suggested,
                'status' => 1,
            ]);
            if($category->save()){
                return response()->json(['success' => true , 'message' => 'Category Created Successfully' , 'data' => ['id' => $category->id ] ] , 200);
            }else{
                return response()->json(['success' => false , 'message' => 'Category Creation Failed' , 'data' => [] ] , 422);
            }   
        }

        
    }

    public function show($category_id)
    {
        $category = Category::where('id',$category_id)->first();
        if(empty($category))
        { return response()->json(['success' => false , 'message' => 'Category Not Found' , 'data' => [] ] , 404); }
        else
        { return response()->json(['success' => true , 'message' => 'Category Found' , 'data' => ['category'=>$category] ] , 200); }
    }
}
