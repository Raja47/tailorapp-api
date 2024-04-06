<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCategory;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class TailorCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tailor_id)
    {
        $categories = TailorCategory::where('tailor_id',$tailor_id)->get();
        if (count($categories)===0)
        { return response()->json(['tailor_id'=>$tailor_id, 'categories'=>'No categories added'],404); }
        else
        { return response()->json(['tailor_id'=>$tailor_id, 'categories'=>$categories],200); }
    }

    public function default($tailor_id)
    {
        $categories = Category::all();
        foreach($categories as $category)
        {
            $tailor_category = TailorCategory::create([
                'tailor_id' => $tailor_id,
                'category_id' => $category->id,
                'name' => $category->name,
                'label' => $category->label,
                'gender' => $category->gender,
                'image' => $category->image,
                'is_suggested' => $category->is_suggested,
                'status' => $category->status,
            ]);
        }
        if($tailor_category->save()){
            return response()->json(['success' => true , 'message' => 'Default Categories added successfully' ] , 200);
        }else{
            return response()->json(['success' => false , 'message' => 'Default Category Creation Failed' ] , 422);
        }   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($tailor_id,Request $request)
    {
        $rules = [
            // 'tailor_id' => 'required',
            'name' => 'required',
            'label' => '',
            'gender' => '',
            'image' => '',
            'is_suggested' => '',
        ];

        $validation = Validator::make($request->all(), $rules);
        
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Category Validation Error','data' => $validation->errors()] , 422); }
        else
        {
            $tailor_category = TailorCategory::create([
                'tailor_id' => $tailor_id,
                'category_id' => 0,
                'name' => $request->name,
                'label' => $request->label,
                'gender' => $request->gender,
                'image' => $request->image,
                'is_suggested' => $request->is_suggested,
                'status' => 1,
            ]);

            if($tailor_category->save())
            { return response()->json(['success' => true , 'message' => 'Category Created Successfully' , 'data' => ['id' => $tailor_category->id ] ] , 200); }
            else
            { return response()->json(['success' => false , 'message' => 'Category Creation Failed' , 'data' => [] ] , 422); }   
        
        }
    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tailor_id,$category_id)
    {
        $category = TailorCategory::where([['id',$category_id],['tailor_id',$tailor_id]])->first();
        if(empty($category))
        { return response()->json(['success' => false , 'message' => 'Category Not Found' , 'data' => [] ] , 404); }
        else
        { return response()->json(['success' => true , 'message' => 'Category Found' , 'data' => ['category'=>$category] ] , 200); }
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'tailor_id' => 'required',
            'name' => 'required',
            'label' => '',
            'gender' => '',
            'image' => '',
            'is_suggested' => '',
            'status' => '',
        ];

        $validation = Validator::make($request->all(), $rules);
        
        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Category Validation Error','data' => $validation->errors()] , 422); }
        else
        {
            $tailor_category = TailorCategory::where([['tailor_id',$request->tailor_id],['id',$id]])->first();
            if(empty($tailor_category))
            { return response()->json(['success' => false , 'message' => 'Category does not exist.'] , 404); }
            else
            {
                $tailor_category->name = $request->name;
                $tailor_category->label = $request->label;
                $tailor_category->gender = $request->gender;
                $tailor_category->image = $request->image;
                $tailor_category->is_suggested = $request->is_suggested;
                $tailor_category->status = $request->status;;
            }

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
