<?php

namespace App\Http\Controllers;

use App\Models\CategoryQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category_questions = CategoryQuestion::where('status', 1)->get();
        if (count($category_questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No questions to show'], 404);
        } else {
            foreach ($category_questions as $category_question) {
                $category_question->options = json_decode($category_question->options);
            }
            return response()->json(['success' => true, 'data' => $category_questions], 200);
        }
    }

    public function categoryQuestion($category_id)
    {
        $category_questions = CategoryQuestion::where([['category_id', $category_id], ['status', 1]])->get();
        if (count($category_questions) === 0) {
            return response()->json(['success' => false, 'message' => 'No questions to show in this question'], 404);
        } else {
            return response()->json(['success' => true, 'data' => $category_questions], 200);
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
            'question_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Question data validation error', 'data' => $validation->errors()], 422);
        } else {
            $category_question = CategoryQuestion::create([
                'category_id' => $request->category_id,
                'question_id' => $request->question_id,
                'status' => 1,
            ]);
            if ($category_question->save()) {
                return response()->json(['success' => true, 'data' => $category_question], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Category Question creation failed'], 404);
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
