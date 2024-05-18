<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeasurementValue;
use Illuminate\Support\Facades\Validator;

class MeasurementValueController extends Controller
{
    public function getDressMeasurementValues($measurement_id)
    {
        $measurements = MeasurementValue::where('measurement_id', $measurement_id)->get();
        return $measurements;
    }

    public function newMeasurementValue(Request $request)
    {
        $rules = [
            'measurement_id' => 'required',
            'parameter_id' => 'required',
            'value' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurement_value = MeasurementValue::create([
                'measurement_id' => $request->measurement_id,
                'parameter_id' => $request->parameter_id,
                'value' => $request->value,
            ]);
            if ($measurement_value->save()) {
                return response()->json(['success' => true, 'message' => 'Measurement Added Successfully'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Measurement cannot be added'], 500);
            }
        }
    }

    public function updateMeasurementValue(Request $request)
    {
        $validation = Validator::make($request->all(), ['parameter_id' => 'required']);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurement = MeasurementValue::where('parameter_id', $request->parameter_id)->first();
            if (empty($measurement)) {
                return response()->json(['success' => true, 'message' => 'Measurement does not exist'], 200);
            } else {
                $measurement->value = $request->value;
                return response()->json(['success' => true, 'message' => 'Measurement Updated Successfully'], 200);
            }
        }
    }

    public function getMeasurementValues($measurement_id)
    {
        $measurement = MeasurementValue::where('measurement_id', $measurement_id)->get()->orderBy('id');
        return $measurement;
    }

    public function deleteInvalidMvs(Request $request)
    {
        $rules = [
            'measurement_id' => 'required',
            'valid_mvs' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurements = MeasurementValue::where('measurement_id', $request->measurement_id)->whereNotIn('id', $request->valid_mvs)->get();
            if (empty($measurements)) {
                return response()->json(['success' => true, 'message' => 'Measurement does not exist'], 200);
            } else {
                $measurements->delete();
                return response()->json(['success' => true, 'message' => 'Measurement Deleted Successfully'], 200);
            }
        }
    }
}
