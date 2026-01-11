<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCategoryParameter as TalCatParameter;
use App\Models\TailorCategory;
use App\Models\Category;
use App\Models\Dress;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class TailorCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Get(
     *     path="/tailors/categories",
     *     summary="Get all categories for a tailor",
     *     security={{"bearerAuth": {}}},
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="tailor_id", type="integer", example=1),
     *             @OA\Property(property="categories", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Category 1"),
     *                 @OA\Property(property="status", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-31T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-31T12:00:00Z")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No categories added",
     *         @OA\JsonContent(
     *             @OA\Property(property="tailor_id", type="integer", example=1),
     *             @OA\Property(property="categories", type="string", example="No categories added")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $categories = TailorCategory::where([['tailor_id', $tailor_id], ['status', 1]])->get();
        if (count($categories) === 0) {
            return response()->json(['tailor_id' => $tailor_id, 'categories' => 'No categories added'], 404);
        } else {
            return response()->json(['tailor_id' => $tailor_id, 'categories' => $categories], 200);
        }
    }

    public function default($tailor_id)
    {
        $categories = Category::all();
        foreach ($categories as $category) {
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
        if ($tailor_category->save()) {
            return response()->json(['success' => true, 'message' => 'Default Categories added successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Default Category Creation Failed'], 422);
        }
    }

    // swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/categories/{category_id}/status",
     *     summary="Update the status of a category",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the category"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status updated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error")
     *         )
     *     )
     * )
     */

    public function updateStatus(Request $request,$category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $category = TailorCategory::where([['id', $category_id], ['tailor_id', $tailor_id]])->first();
        if (empty($category)) {
            return response()->json(['success' => false, 'message' => 'Category Not Found'], 404);
        } else {
            $category->status = $category->status == 1 ? 0 : 1;
            // $tal_cat_params = TalCatParameter::where('category_id', $category->id)->get();
            // foreach ($tal_cat_params as $tal_cat_param) {
            //     $tal_cat_param->status = $tal_cat_param->status == 1 ? 0 : 1;
            // }

            if ($category->save()) {
                return response()->json(['success' => true, 'message' => 'Status updated.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Error'], 500);
            }
        }
    }
    /**
     * @OA\Get(
     *     path="/tailors/categories/exists",
     *     summary="Get all categories for a tailor with existing status",
     *     security={{"bearerAuth": {}}},
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="tailor_id", type="integer", example=1),
     *             @OA\Property(property="categories", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Category 1"),
     *                 @OA\Property(property="status", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-31T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-31T12:00:00Z"),
     *                 @OA\Property(property="exists", type="boolean", example="false")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No categories added",
     *         @OA\JsonContent(
     *             @OA\Property(property="tailor_id", type="integer", example=1),
     *             @OA\Property(property="categories", type="string", example="No categories added")
     *         )
     *     )
     * )
     */
    public function allCategoriesWithExistStatus()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $categories = Category::all();
        $talCategories = TailorCategory::where('tailor_id', $tailor_id)->get();
        $allCategories = [];
        $talCategoryIds = $talCategories->pluck('category_id')->toArray();
        foreach ($categories as $category) {
            if (in_array($category->id, $talCategoryIds)) {
                $allCategories[] = $talCategories->firstWhere('category_id', $category->id);
            } else {
                $allCategories[] = $category;
            }
        }
        // Load now custom categories;
        foreach ($talCategories as $category) {
           if(empty($category->category_id)){
               $allCategories[] = $category;
           }
        }
        
        return response()->json(['success' => true, 'data' => ['categories' => $allCategories ]], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/categories/store",
     *     summary="Store a new category for a tailor",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="Name of the category"),
     *             @OA\Property(property="label", type="string", description="Label of the category"),
     *             @OA\Property(property="gender", type="string", description="Gender for the category"),
     *             @OA\Property(property="image", type="string", format="binary", description="Image for the category"),
     *             @OA\Property(property="is_suggested", type="boolean", description="Is the category suggested")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category Created Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Category Validation Error / Category Creation Failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", additionalProperties={ "type": "string" })
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'label' => '',
            'gender' => '',
            'image' => '',
            'is_suggested' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Category Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailor_category = TailorCategory::create([
                'tailor_id' => $tailor_id,
                'category_id' => null,
                'name' => $request->name,
                'label' => $request->label,
                'gender' => $request->gender,
                'image' => $request->image,
                'is_suggested' => $request->is_suggested,
                'status' => 1,
            ]);

            if ($tailor_category->save()) {
                return response()->json(['success' => true, 'message' => 'Category Created Successfully', 'data' => ['id' => $tailor_category->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Category Creation Failed', 'data' => []], 422);
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $category = TailorCategory::where([['id', $category_id], ['tailor_id', $tailor_id]])->first();
        if (empty($category)) {
            return response()->json(['success' => false, 'message' => 'Category Not Found', 'data' => []], 404);
        } else {
            return response()->json(['success' => true, 'message' => 'Category Found', 'data' => ['category' => $category]], 200);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/tailors/categories/{id}/update",
     *     summary="Update a tailor category",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to be updated",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "label", "gender"},
     *             @OA\Property(property="name", type="string", description="Category name", example="Casual Wear"),
     *             @OA\Property(property="label", type="string", description="Category label", example="Casual"),
     *             @OA\Property(property="gender", type="string", description="Category gender", example="Male"),
     *             @OA\Property(property="image", type="string", description="Category image URL", example="http://example.com/image.jpg"),
     *             @OA\Property(property="is_suggested", type="boolean", description="Whether the category is suggested", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category Updated Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category Updated Successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category does not exist.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Category Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category Validation Error"),
     *             @OA\Property(property="data", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Category Updation Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category Updation Failed")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'label' => 'required',
            'gender' => 'required',
            'image' => '',
            'is_suggested' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Category Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailor_category = TailorCategory::where([['tailor_id', $tailor_id], ['id', $id]])->first();
            if (empty($tailor_category)) {
                return response()->json(['success' => false, 'message' => 'Category does not exist.'], 404);
            }
            $tailor_category->name = $request->name;
            $tailor_category->label = $request->label;
            $tailor_category->gender = $request->gender;
            $tailor_category->image = $request->image;
            $tailor_category->is_suggested = $request->is_suggested;

            if ($tailor_category->save()) {
                return response()->json(['success' => true, 'message' => 'Category Updated Successfully', 'data' => ['id' => $tailor_category->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Category Updation Failed'], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/categories/{category_id}/delete",
     *     summary="Delete a category for a specific tailor",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the category to delete"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully or Category deactivated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Category does not exist"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $tailor_category = TailorCategory::where([['tailor_id', $tailor_id], ['id', $id]])->first();

        if (empty($tailor_category)) {
            return response()->json(['status' => 'error', 'message' => 'Category does not exist', 'data' => []], 404);
        }

        $tal_cat_params = TalCatParameter::where('category_id', $tailor_category->id);
        $category_dress = Dress::where('category_id', $id)->first();

        // If there are no Dress records, delete the TailorCategory and its parameters
        if (empty($category_dress)) {
            $tal_cat_params->delete();
            $tailor_category->delete();
            return response()->json(['status' => 'success', 'message' => 'Category deleted successfully', 'data' => []], 200);
        }

        // If Dress records exist, deactivate the category and its parameters
        $tal_cat_params->update(['status' => 0]);
        $tailor_category->status = 0;
        $tailor_category->save();

        return response()->json(['status' => 'success', 'message' => 'Category deactivated successfully', 'data' => []], 200);
    }
}
