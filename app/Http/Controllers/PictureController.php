<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Picture;

class PictureController extends Controller
{
    public function getPictures($model, $model_id)
    {
        $pictures = Picture::where([['model', $model], ['model_id', $model_id]])->get();
        return $pictures;
    }

    public function addPicture(Request $request)
    {
        $rules = [
            'model' => 'required',
            'model_id' => 'required',
            'path' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Picture data validation error', 'data' => $validation->errors()], 422);
        } else {
            $picture = Picture::create([
                'model' => $request->model,
                'model_id' => $request->model_id,
                'type' => $request->type,
                'path' => $request->path,
                'notes' => $request->notes,
            ]);

            if ($picture->save()) {
                return response()->json(['success' => true, 'message' => 'Picture Added Successfully'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Picture cannot be added'], 500);
            }
        }
    }

    public function deletePicture($picture_id)
    {
        $picture = Picture::where('id', $picture_id)->get();
        if (empty($picture)) {
            return response()->json(['success' => true, 'message' => 'Picture does not exist'], 200);
        } else {
            $picture->delete();
            return response()->json(['success' => true, 'message' => 'Picture Deleted Successfully'], 200);
        }
    }

    public function updatePicture(Request $request)
    {
        $rules = [
            'picture_id' => 'required'
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Picture data validation error', 'data' => $validation->errors()], 422);
        } else {
            $picture = Picture::where('id', $request->picture_id)->first();
            if (empty($picture)) {
                return response()->json(['success' => true, 'message' => 'Picture does not exist'], 200);
            } else {
                $picture->type = $request->type;
                $picture->path = $request->path;
                $picture->notes = $request->notes;
                return response()->json(['success' => true, 'message' => 'Picture Updated Successfully'], 200);
            }
        }
    }

    public function getModelPictures($model,$model_id)
    {
        $pictures = Picture::where([['model',$model],['model_id',$model_id]])->get();
        return $pictures;
    }
}
