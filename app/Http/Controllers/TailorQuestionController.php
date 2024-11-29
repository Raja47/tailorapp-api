<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\TailorQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TailorQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tailor_id = auth('sanctum')->user->id;
        $questions = TailorQuestion::where([['tailor_id',$tailor_id],['status', 1]])->get();
        if (count($questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No questions to show'], 404);
        } else {
            return response()->json(['success' => true, 'data' => $questions], 200);
        }
    }

    public function default($tailor_id)
    {
        $questions = Question::all();
        foreach ($questions as $question) {
            $tailor_question = TailorQuestion::create([
                'question_id' => $question->id,
                'tailor_id' => $tailor_id,
                'question' => $question->question,
                'type' => $question->type,
                'options' => $question->options,
                'status' => $question->status,
            ]);
        }
        if ($tailor_question->save()) {
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
            'question' => 'required',
            'type' => 'required',
            'options' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Question data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user->id;
            $question = TailorQuestion::create([
                'question_id' => null,
                'tailor_id' => $tailor_id,
                'question' => $request->question,
                'type' => $request->type,
                'options' => $request->options,
                'status' => 1,
            ]);
            if ($question->save()) {
                return response()->json(['success' => true, 'data' => $question], 200);
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
