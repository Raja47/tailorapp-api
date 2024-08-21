<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCategory;
use App\Models\Category;
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

    public function default()
    {
        $tailor_id = auth('sanctum')->user()->id;
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

    public function updateStatus($category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $category = TailorCategory::where([['id', $category_id], ['tailor_id', $tailor_id]])->first();
        if (empty($category)) {
            return response()->json(['success' => false, 'message' => 'Category Not Found'], 404);
        } else {
            if ($category->status == 1) {
                $category->status = 0;
            } elseif ($category->status == 0) {
                $category->status = 1;
            }
            if ($category->save()) {
                return response()->json(['success' => true, 'message' => 'Status updated.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Error'], 500);
            }
        }
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
                'category_id' => 0,
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
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'label' => '',
            'gender' => '',
            'image' => '',
            'is_suggested' => '',
            'status' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Category Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailor_category = TailorCategory::where([['tailor_id', $tailor_id], ['id', $id]])->first();
            if (empty($tailor_category)) {
                return response()->json(['success' => false, 'message' => 'Category does not exist.'], 404);
            } else {
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
     *         description="Category Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category Deleted successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Category does not exist"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $tailor_category = TailorCategory::where([['tailor_id', $tailor_id], ['id', $id]])->first();

        if ($tailor_category) {
            $tailor_category->delete();
            return response()->json(['status' => 'success', 'message' => 'Category Deleted successfully', 'data' => []], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Category does not exist', 'data' => []], 404);
        }
    }
}
