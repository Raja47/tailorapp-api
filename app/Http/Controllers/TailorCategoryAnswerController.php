<?php

namespace App\Http\Controllers;

use App\Models\TailorCategoryAnswer;
use App\Models\TailorCategoryQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TailorCategoryAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    public function getAnswers($dress_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $tal_cat_answers = TailorCategoryAnswer::where([['tailor_id', $tailor_id], ['dress_id', $dress_id]])->get();
        if (count($tal_cat_answers) === 0) {
            return response()->json(['success' => false, 'message' => 'No answers to show'], 404);
        } else {
            return response()->json(['success' => true, 'data' => $tal_cat_answers], 200);
        }
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
            'question_id' => 'required',
            'value' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Answer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tal_cat_question = TailorCategoryQuestion::where([['tailor_id', $tailor_id], ['question_id', $request->question_id]])->first();
            $value = json_decode($request->value);
            $tal_cat_answer = TailorCategoryAnswer::create([
                'tailor_id' => $tailor_id,
                'question_id' => $tal_cat_question->id,
                'value' => $value,
            ]);
            if ($tal_cat_answer->save()) {
                return response()->json(['success' => true, 'data' => $tal_cat_answer], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Answer creation failed'], 404);
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
