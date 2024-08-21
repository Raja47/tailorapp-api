<?php

namespace App\Http\Controllers;

use App\Models\TailorCategoryParameter as TalCatParameter;
use App\Models\CategoryParameter;
use App\Models\TailorCategory;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Http\Request;

class TailorCategoryParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // swagger annotations 
    /**
     * @OA\Get(
     *     path="/tailors/categories/{category_id}/parameters/",
     *     summary="Get parameters by tailor and category",
     *     tags={"Parameters"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the category"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parameters retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="parameters",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parameters not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="parameters", type="string", example="Not Found")
     *         )
     *     )
     * )
     */
    public function index($category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $parameters = TalCatParameter::where([['category_id', $category_id], ['tailor_id', $tailor_id]])->get();
        if (count($parameters) === 0) {
            return response()->json(['category_id' => $category_id, 'parameters' => 'Not Found']);
        } else {
            return response()->json(['category_id' => $category_id, 'parameters' => $parameters]);
        }
    }

    public function default()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $category_parameters = CategoryParameter::all();
        foreach ($category_parameters as $category_parameter) {
            $tailor_category = TailorCategory::where([['tailor_id', $tailor_id], ['category_id', $category_parameter->category_id]])->first();
            $tal_cat_parameter = TalCatParameter::create([
                'label' => $category_parameter->label,
                'tailor_id' => $tailor_id,
                'category_id' => $tailor_category->id,
                'parameter_id' => $category_parameter->parameter_id,
                'part' => $category_parameter->part,
                'status' => $category_parameter->status,
            ]);
        }
        if ($tal_cat_parameter->save()) {
            return response()->json(['success' => true, 'message' => 'Default Categories Parameters added successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Default Category Parameters Creation Failed'], 422);
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
     *     path="/tailors/categories/parameters/store",
     *     summary="Create new category parameters",
     *     tags={"Parameters"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1, description="The ID of the category"),
     *             @OA\Property(
     *                 property="parameter_id",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of parameter IDs"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category parameters created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category Parameter Added Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ids", type="array", @OA\Items(type="integer", example=1), description="Array of created parameter IDs")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category parameter data validation error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Contains validation error messages"
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $rules = [
            'category_id' => 'required|exists:tailor_categories,id',
            'parameter_id' => 'required|array',
            'parameter_id.*' => 'integer|exists:tailor_parameters,id',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Catgeory parameter data validation error', 'data' => $validation->errors()], 422);
        }
        $tailor_id = auth('sanctum')->user()->id;
        $createdParameters = [];
        foreach ($request->parameter_id as $parameter_id) {
            $category_parameter = TalCatParameter::create([
                'label' => null,
                'tailor_id' => $tailor_id,
                'category_id' => $request->category_id,
                'parameter_id' => $parameter_id,
                'part' => null,
                'status' => 1,
            ]);

            if ($category_parameter->save()) {
                $createdParameters[] = $category_parameter->id;
            } else {
                return response()->json(['success' => false, 'message' => 'Catgeory parameter Creation Failed', 'data' => []], 422);
            }
        }
        return response()->json(['success' => true, 'message' => 'Catgeory Parameter Added Successfully', 'data' => ['ids' => $createdParameters]], 200);
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
    public function update(Request $request, $id)
    {
        //
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
     *     path="/tailors/categories/parameters/destroy",
     *     summary="Delete a category parameter",
     *     tags={"Parameters"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="parameter_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parameter deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Parameter deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parameter not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Parameter Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Parameter validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Parameter Validation Error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request)
    {
        $rules = [
            'category_id' => 'required',
            'parameter_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Paramter Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $category_id = $request->category_id;
            $parameter_id = $request->parameter_id;
            $category_parameter = TalCatParameter::where([['category_id', $category_id], ['tailor_id', $tailor_id], ['parameter_id', $parameter_id]])->first();
            if (empty($category_parameter)) {
                return response()->json(['success' => false, 'message' => 'Parameter Not Found'], 404);
            } else {
                $category_parameter->delete();
                return response()->json(['success' => true, 'message' => 'Parameter deleted succesfully'], 200);
            }
        }
    }
}
