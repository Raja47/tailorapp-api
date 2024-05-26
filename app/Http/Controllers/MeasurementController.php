<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Http\Controllers\MeasurementValueController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class MeasurementController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/measurements/dresses/{dress_id}",
 *     summary="Get dress measurements",
 *     tags={"Measurements"},
 *     @OA\Parameter(
 *         name="dress_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="dress_id", type="integer"),
 *                 @OA\Property(property="measurements", type="array",
 *                     @OA\Items(type="object")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
        
    public function getDressMeasurementWithValues($dress_id)
    {
        $measurement = Measurement::where([['model', 'dress'], ['model_id', $dress_id]])->first();
        if (!empty($measurement)) {
            $measurement_values = (new MeasurementValueController)->getDressMeasurementValues($measurement->id);
            return response()->json(['success' => true, 'data' => ['dress_id' => $dress_id, 'measurements' => $measurement_values]], $status = 200);
        } else {
            return response()->json(['success' => false, 'data' => ['dress_id' => $dress_id]], $status = 200);
        }
    }

    public function updateMeasurementWithValues(Request $request)
    {

        // update on basis of what, no id given???
    }
    public function newMeasurementWithValues($tailor_id, $dress_id, Request $request)
    {
        //
    }
    public function newMeasurement(Request $request)
    {
        $rules = [
            'model' => 'required',
            'model_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurement = Measurement::create([
                'model' => $request->model,
                'model_id' => $request->model_id,
            ]);
            if ($measurement->save()) {
                return response()->json(['success' => true, 'message' => 'Measurement Added Successfully'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Measurement cannot be added'], 500);
            }
        }
    }
    public function updateMeasurement(Request $request)
    {
        $validation = Validator::make($request->all(), ['measurement_id' => 'required']);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurement = Measurement::where('id', $request->measurement_id)->first();
            $measurement->notes = $request->notes;
            if ($measurement->save()) {
                return response()->json(['success' => true, 'message' => 'Measurement Added Successfully'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Measurement cannot be added'], 500);
            }
        }
    }
    public function getCustomerMeasurements($customer_id)
    {
        $customer_measurements = DB::table('measurements')
            ->select('measurements.id', 'measurements.name', 'measurements.notes', 'categories.name AS categoryName')
            ->leftjoin('categories', 'measurements.category_id', 'categories.id')
            ->where('customer_id', $customer_id)
            ->orderBy('measurements.id', 'desc')
            ->get();

        if (!empty($customer_measurements)) {
            return response()->json(['success' => true, 'data' => [$customer_measurements]], $status = 200);
        } else {
            return response()->json(['success' => false], $status = 200);
        }
    }
    public function deleteMeasurement($measurement_id)
    {
        $measurement = Measurement::where('id',$measurement_id)->get();
        if (empty($measurement)) {
            return response()->json(['success' => false, 'message' => 'Measurement not found.'], 200);
        } else {
            $measurement->delete();
            return response()->json(['success' => true, 'message' => 'Measurement deleted successfully'], 200);
        }
}
}
