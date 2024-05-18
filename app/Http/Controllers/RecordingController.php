<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recording;
use Illuminate\Support\Facades\Validator;

class RecordingController extends Controller
{
    public function getRecording($dress_id)
    {
        $recording = Recording::where('dress_id', $dress_id)->orderBy('id', 'DESC')->get();
        return $recording;
    }

    public function getDressRecordings($dress_id)
    {
        $recording = Recording::where('dress_id', $dress_id)->orderBy('id', 'DESC')->get();
        return $recording;
    }

    public function addRecording(Request $request)
    {
        $rules = [
            'dress_id' => 'required',
            'path' => 'required',
            'duration' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Recording data validation error', 'data' => $validation->errors()], 422);
        } else {
            $recording = Recording::create([
                'dress_id' => $request->dress_id,
                'path' => $request->path,
                'duration' => $request->duration,
            ]);
            if ($recording->save()) {
                return response()->json(['success' => true, 'message' => 'Recording Added Successfully', 'data' => ['Recording id' => $recording->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Recording cannot be added'], 500);
            }
        }
    }

    public function removeRecording($dress_id)
    {
        $recording = Recording::where('dress_id', $dress_id)->get();
        if (empty($recording)) {
            return response()->json(['success' => true, 'message' => 'Recording does not exist'], 200);
        } else {
            $recording->delete();
            return response()->json(['success' => true, 'message' => 'Recording Deleted Successfully'], 200);
        }
    }
}
