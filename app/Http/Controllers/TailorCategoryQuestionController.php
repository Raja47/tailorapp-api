<?php

namespace App\Http\Controllers;

use App\Models\CategoryQuestion;
use App\Models\TailorCategory;
use App\Models\TailorCategoryQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TailorCategoryQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/tailors/questions/",
     *     summary="Get all questions of tailor",
     *     tags={"Questions"},
     *     security={{"bearerAuth": {}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Questions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="question", type="string", example="Specify the collar style for the garment"),
     *                     @OA\Property(property="type", type="string", example="multi-icon"),
     *                     @OA\Property(property="options", type="string", example="[{'label':'Standard Collar','value':'standard'}]"),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-27 10:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-27 10:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Questions not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="bool", example=false),
     *             @OA\Property(property="message", type="string", example="No Questions to show in this category")
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

    public function index()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $tal_questions = TailorCategoryQuestion::where([['tailor_id', $tailor_id], ['status', 1]])->get();
        if (count($tal_questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No Questions to show'], 404);
        } else {
            foreach ($tal_questions as $tal_question) {
                $tal_question->options = json_decode($tal_question->options);
            }
            return response()->json(['success' => true, 'data' => $tal_questions], 200);
        }
    }

    //swagger annotations
    /**
     * @OA\Get(
     *     path="/tailors/categories/{category_id}/questions/",
     *     summary="Get questions by tailor and category",
     *     tags={"Questions"},
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
     *         description="Questions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="question", type="string", example="Specify the collar style for the garment"),
     *                     @OA\Property(property="type", type="string", example="multi-icon"),
     *                     @OA\Property(property="options", type="string", example="[{'label':'Standard Collar','value':'standard'}]"),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-27 10:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-27 10:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Questions not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="No Questions to show in this category")
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
    public function tailorCatQuestions($category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $tal_cat_questions = TailorCategoryQuestion::where([['tailor_id', $tailor_id], ['category_id', $category_id]])->get();

        if (count($tal_cat_questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No Questions to show in this category'], 404);
        } else {
            foreach ($tal_cat_questions as $tal_cat_question) {
                $tal_cat_question->options = json_decode($tal_cat_question->options);
            }
            return response()->json(['success' => true, 'message' => 'error', 'data' => $tal_cat_questions], 200);
        }
    }

    public function default($tailor_id)
    {
        $cat_questions = CategoryQuestion::all();
        foreach ($cat_questions as $cat_question) {
            $tailor_category = TailorCategory::where([['tailor_id', $tailor_id], ['category_id', $cat_question->category_id]])->first();
            $tailor_cat_question = TailorCategoryQuestion::create([
                'tailor_id' => $tailor_id,
                'category_id' => $tailor_category->id,
                'question_id' => $cat_question->id,
                'question' => $cat_question->question,
                'type' => $cat_question->type,
                'options' => $cat_question->options,
                'status' => $cat_question->status,
            ]);
        }
        if ($tailor_cat_question->save()) {
            return response()->json(['success' => true, 'message' => 'Default questions added successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Default Question Creation Failed'], 422);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'category_question_id' => 'required',
            'question' => 'required',
            'type' => 'required',
            'options' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Question data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tal_cat_question = TailorCategoryQuestion::create([
                'category_question_id' => $request->category_question_id,
                'question' => $request->question,
                'type' => $request->type,
                'options' => $request->options,
                'tailor_id' => $tailor_id,
                'status' => 1,
            ]);
            if ($tal_cat_question->save()) {
                return response()->json(['success' => true, 'data' => $tal_cat_question], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Question creation failed'], 404);
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function destroy($id)
    {
        //
    }
}
