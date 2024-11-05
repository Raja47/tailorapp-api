<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Http\Controllers\MeasurementValueController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class MeasurementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/measurements/dresses/{dress_id}",
     *     summary="Get dress measurements",
     *     tags={"Measurements"},
     *     security={{"bearerAuth": {}}},
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
            $measurement_values = MeasurementValue::where('measurement_id', $measurement->id)->get();
            return response()->json(['success' => true, 'data' => ['dress_id' => $dress_id, 'measurements' => $measurement_values]], $status = 200);
        } else {
            return response()->json(['success' => false, 'data' => ['dress_id' => $dress_id]], $status = 200);
        }
    }

    public function updateMeasurementWithValues(Request $request)
    {
        $validation = Validator::make($request->all(), ['measurementBoxes' => 'required']);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurementBoxes = $request->measurementBoxes;
            $responses = [];
            foreach ($measurementBoxes as $measurementBox) {
                $responses[] = MeasurementValue::updateMeasurementValue($measurementBox);
            }
            return $responses;
        }
    }

    /**
     * @OA\Post(
     *     path="/measurements/dresses/store",
     *     summary="Add new measurement with values",
     *     description="Adds new measurements with the specified values for a dress.",
     *     tags={"Measurements"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"dress_id", "measurementBoxes"},
     *             @OA\Property(property="dress_id", type="integer", description="Dress ID", example=1),
     *             @OA\Property(
     *                 property="measurementBoxes",
     *                 type="array",
     *                 description="Array of measurement values",
     *                 @OA\Items(
     *                     type="object",
     *                     required={ "parameter_id", "value"},
     *                     @OA\Property(property="parameter_id", type="integer", description="Parameter ID", example=2),
     *                     @OA\Property(property="value", type="string", description="Measurement value", example="34")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measurement Created Successfully",
     *         @OA\JsonContent(
     *             type="integer",
     *             description="The ID of the new measurement",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Measurement data validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Measurement data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */

    public function newMeasurementWithValues(Request $request)
    {
        $rules = [
            'dress_id' => 'required',
            'measurementBoxes' => 'required|array',

        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $data = [
                'model' => 'dress',
                'model_id' => $request->dress_id,
            ];
            $measurement_id = $this->newMeasurement($data);
            $responses = [];
            foreach ($request->measurementBoxes as $measurementBox) {
                $measurementBox['measurement_id'] = $measurement_id;
                $responses[] = MeasurementValue::newMeasurementValue($measurementBox);
            }
            return $measurement_id;
        }
    }

    public function newMeasurement(array $data)
    {
        $rules = [
            'model' => 'required',
            'model_id' => 'required',
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Measurement data validation error', 'data' => $validation->errors()], 422);
        } else {
            $measurement = Measurement::create([
                'model' => $data['model'],
                'model_id' => $data['model_id'],
                'status' => 1,
            ]);
            return $measurement->id;
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
            // ->select('measurements.id', 'measurements.name', 'measurements.notes', 'categories.name AS categoryName')
            ->select('measurements.id', 'measurements.notes', 'categories.name AS categoryName')
            ->leftjoin('categories', 'measurements.category_id', 'categories.id')
            ->where('customer_id', $customer_id)
            ->orderBy('measurements.id', 'desc')
            ->get();

        if (!empty($customer_measurements)) {
            return response()->json(['success' => true, 'data' => [$customer_measurements]], 200);
        } else {
            return response()->json(['success' => false], 200);
        }
    }

    public function deleteMeasurement($measurement_id)
    {
        $measurement = Measurement::where('id', $measurement_id);
        if (empty($measurement)) {
            return response()->json(['success' => false, 'message' => 'Measurement not found.'], 200);
        } else {
            $measurement->delete();
            return response()->json(['success' => true, 'message' => 'Measurement deleted successfully'], 200);
        }
    }
}
