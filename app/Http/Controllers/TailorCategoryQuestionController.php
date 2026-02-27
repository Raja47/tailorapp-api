<?php

namespace App\Http\Controllers;

use App\Models\CategoryQuestion;
use App\Models\TailorCategory;
use App\Models\Dress;
use App\Models\Recording;
use App\Models\TailorCategoryQuestion;
use App\Models\TailorCategoryAnswer;
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
     *             @OA\Property(property="message", type="string", example="No Questions to show in this category"),
     *             @OA\Property(property="icon", type="string", example="questions\options.jpg", format="uri")
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

    public function tailorCatQuestions($category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $category =  TailorCategory::find($category_id);
        
        if(empty($category)){
          return response()->json(['success' => false , 'message' => 'Category not Found'] , 404);  
        }

        $tal_cat_questions = TailorCategoryQuestion::where(['tailor_id' => $tailor_id , 'category_id'=> $category_id])->get();

        if (count($tal_cat_questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No Questions to show in this category'], 404);
        } else {
            foreach ($tal_cat_questions as $tal_cat_question) {
                $tal_cat_question->options = json_decode($tal_cat_question->options);
            }
            return response()->json(['success' => true, 'message' => 'error', 'data' => ['category' => $category,'questions' => $tal_cat_questions], 200]);
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
     *                     @OA\Property(property="options", type="string", example="[{'label':'Standard Collar','value':'standard','icon':'questions\\options.jpg'}]"),
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
    public function tailorCatActiveQuestions($category_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $category =  TailorCategory::find($category_id);
        
        if(empty($category)){
          return response()->json(['success' => false , 'message' => 'Category not Found'] , 404);  
        }

        $tal_cat_questions = TailorCategoryQuestion::where(['tailor_id' => $tailor_id , 'category_id'=> $category_id , 'status' => 1])->get();

        if (count($tal_cat_questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No Questions to show in this category'], 404);
        } else {
            foreach ($tal_cat_questions as $tal_cat_question) {
                $tal_cat_question->options = json_decode($tal_cat_question->options);
            }
            return response()->json(['success' => true, 'message' => 'error', 'data' => ['category' => $category,'questions' => $tal_cat_questions], 200]);
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
            'category_id' => 'required',
            'question' => 'required',
            'type' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Question data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tal_cat_question = TailorCategoryQuestion::create([
                'category_id' => $request->category_id,
                'question' => $request->question,
                'type' => $request->type,
                'options' => json_encode($request->options),
                'tailor_id' => $tailor_id,
                'status' => 1,
            ]);
            if ($tal_cat_question->save()) {
                return response()->json(['success' => true, 'data' => $tal_cat_question], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Question creation failed'], 500);
            }
        }
    }

    /**
     * 
     */
    public function update(Request $request, $id)
    {
        
        $rules = [
            'category_id' => 'required',
            'question' => 'required',
            'type' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Question data validation error', 'data' => $validation->errors()], 422);
        }

        $tal_cat_question = TailorCategoryQuestion::find($id);

        if (empty($tal_cat_question)) {
            return response()->json(['success' => false, 'message' => 'Question not found'], 404);
        }
        
        $tal_cat_question->question = $request->question;
        $tal_cat_question->type = $request->type;
        $tal_cat_question->options = json_encode($request->options);
        $tal_cat_question->status = $request->status;
        if ($tal_cat_question->save()) {
            return response()->json(['success' => true, 'data' => $tal_cat_question], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Question update failed'], 500);
        }
    }



        /**
     * @OA\Get(
     *     path="/dress/{id}/questions",
     *     summary="Get questions for a dress",
     *     description="Returns a list of questions for a dress based on the dress ID.",
     *     operationId="getQuestions",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,        
        *         @OA\Schema(type="integer"),
        *         description="Dress ID"
        *     ),
     *     @OA\Response(
        *         response=200,
        *         description="Questions retrieved successfully",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="data", type="array",
        *                 @OA\Items(
        *                     type="object",
        *                     @OA\Property(property="id", type="integer", example=1),
        *                     @OA\Property(property="tailor_id", type="integer", example=1),    
        *                     @OA\Property(property="category_id", type="integer", example=1),
        *                     @OA\Property(property="dress_id", type="integer", example=1),
        *                     @OA\Property(property="question", type="string", example="What is your favorite color?"),
        *                     @OA\Property(property="type", type="string", example="text"),
        *                     @OA\Property(property="options", type="array", @OA\Items(type="string", example="Red")),
        *                     @OA\Property(property="value", type="string", example="Red"),
        *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-01T10:00:00Z"),
        *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T10:00:00Z")
        *                 )
        *             ) 
        *         )
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Dress not found",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="message", type="string", example="Dress not found")
        *         )
        *     )
        * )        
     */
    public function getDressQuestions($id)
    {
        $dress = Dress::find($id);
        
        if (empty($dress)) {
            return response()->json(['message' => 'Dress not found'], 404);
        }

        $answers = TailorCategoryAnswer::with('question')->where('dress_id', $id)->get();
        $questions = $answers->map(function ($answer) {
            return [
                'id' => $answer->question?->id,
                'tailor_id' => $answer->tailor_id,
                'category_id' => $answer->question?->category_id,
                'dress_id' => $answer->dress_id,
                'question' => $answer->question?->question,
                'type' => $answer->question?->type,
                'options' => $answer->question?->options,
                'value' => ( $answer && $answer->question?->isMulti()) ? explode(',',$answer->value) : $answer->value, 
                'created_at' => $answer->created_at?->toIso8601ZuluString(),
                'updated_at' => $answer->updated_at?->toIso8601ZuluString(),
            ];
        });

        $recording = null;
        if(Recording::where('dress_id', $id)->exists()) {
            $recording = complete_url(Recording::where('dress_id', $id)->value('path'));
        }

        return response()->json(['message' => 'Questions retrieved successfully',
                'data' => [
                    'questions' => $questions ,
                    'notes' => $dress->notes,
                    'recording' => $recording ] 
                ],
            200);
    }

    /**
     * @OA\Put(
     *     path="/dress/{id}/questions",
     *     summary="Update questions for a dress",
     *     description="Updates the questions for a dress based on the dress ID.",
     *     operationId="updateQuestions",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,        
        *         @OA\Schema(type="integer"),
        *         description="Dress ID"
        *     ),     
     *     @OA\RequestBody(
        *         required=true,
        *         @OA\MediaType(
        *             mediaType="application/json",
        *             @OA\Schema(
        *                 @OA\Property(
        *                     property="questions",
        *                     type="array",
        *                     @OA\Items(
        *                         type="object",
        *                         @OA\Property(property="id", type="integer", example=1, description="Question ID"),
        *                         @OA\Property(property="value", type="string", example="Red", description="Answer value")
        *                     )
        *                 )
        *             )
        *         )
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Questions updated successfully",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="message", type="string", example="Dress questions updated successfully")
        *         )
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Dress not found",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="message", type="string", example="Dress not found")
        *         )
        *     ),
        *     @OA\Response(
        *         response=422,
        *         description="Validation error",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="success", type="boolean", example=false),
        *             @OA\Property(property="message", type="string", example="The given data was invalid."),
        *             @OA\Property(
        *                 property="errors",
        *                 type="object",
        *                 @OA\Property(
        *                     property="questions",
        *                     type="array",
        *                     @OA\Items(
        *                         type="string",
        *                         example="The questions field is required."
        *                     )
        *                 )
        *             )
        *         )
        *     )
        * )
     */
    public function updateDressQuestions(Request $request, $id)
    {
        
        $rules = [
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'required|integer|exists:tailor_category_questions,id',
            'questions.*.value' => 'nullable|string|max:1000',
            'notes' => 'nullable|string',
            'recording' => 'nullable|string', // Assuming recording is a string path
        ];

        $dress = Dress::find($id);

        if (!$dress) {
            return response()->json(['message' => 'Dress not found'], 404);
        }

        if ($request->has('questions')) {

            $questionData = collect($request->input('questions'))->mapWithKeys(function ($question) {
                return [$question['id'] => implode(',', (array) $question['value'])];
            });
            TailorCategoryAnswer::whereIn('tcq_id', $questionData->keys())
                ->where('dress_id', $id)
                ->each(function ($answer) use ($questionData) {
                    $answer->update(['value' => $questionData[$answer->tcq_id]]);
            });
        }    
        
        if ($request->has('notes')) {
            $dress->notes = $request->input('notes');
            $dress->save();
        }

        if ($request->has('recording')) {
            if (!empty($request->input('recording'))) {
                $recording = Recording::where('dress_id', $id)->first();
                if ($recording == null) {
                    $recording = new Recording();
                    $recording->dress_id = $id;
                }
                $recording->path = relative_url($request->input('recording'));
                $recording->duration = 0; // Assuming duration is not provided in the request
                $recording->save();
            } else {
                Recording::where('dress_id', $id)->delete();
            }
        }

        return response()->json(['message' => 'Dress questions updated successfully']);
    }

    /**
     * 
     */
    public function updateStatus(Request $request, $id){
        
        $question = TailorCategoryQuestion::find($id);
        if(empty($question)){
            return response()->json(['success' => false, 'message' => 'Question not found'], 404);
        }

        $question->status = $question->status == 1 ? 0 : 1;
        if ($question->save()) {
            return response()->json(['success' => true, 'message' => 'Question status updated successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Question status update failed'], 404);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $question = TailorCategoryQuestion::find($id);

        if(empty($question)){
            return response()->json(['success' => false, 'message' => 'Question deleted successfully'], 200);
        }

        if(!$question->isCustom()){
            return response()->json(['success' => false, 'message' => 'Question can be deactived only but not deleted'], 200);
        }

        if($question->delete()){
            return response()->json(['success' => true, 'message' => 'Question deleted successfully'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Question delete failed'], 500);
    }
}
