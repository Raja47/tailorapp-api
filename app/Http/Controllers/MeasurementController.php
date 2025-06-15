<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Models\Dress;
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



    
    /**
     * @OA\Get(
     *     path="/tailors/dresses/{id}/measurement",
     *     summary="Get the measurement of a dress",
     *     description="Allows a tailor to get the measurement of a dress based on dress ID.",     
     *     operationId="getDressMeasurement",
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
        *         description="Dress measurement retrieved successfully",    
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="measurement_values", type="array",
        *                 @OA\Items(
        *                     type="object",
        *                     @OA\Property(property="parameter_id", type="integer"),
        *                     @OA\Property(property="value", type="number", format="float"),
        *                     @OA\Property(property="parameter_name", type="string"),
        *                     @OA\Property(property="parameter_unit", type="string"),
        *                     @OA\Property(property="parameter_type", type="string"),
        *                     @OA\Property(property="tailor_cat_parameter_id", type="integer"),
        *               description="Measurement values for the dress")
        *         ),
        *           @OA\Property(property="type", type="string", example="stitching", description="Type of the dress")  
        *         )
        *     ),                         
        *     @OA\Response(
        *         response=404,                             
        *         description="Dress not found",    
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="message", type="string", example="Dress not found"),
        *         )
        *     )
        *     ),
        *     @OA\Response(
        *         response=500,
        *         description="Internal server error",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="message", type="string", example="An error occurred while retrieving the measurement") 
        *         ) 
        *     ),
        *     @OA\Response(
        *         response=422,
        *         description="Validation error",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="success", type="boolean", example=false),    
        *             @OA\Property(property="message", type="string", example="Dress data validation error"),
        *             @OA\Property(
        *                 property="data",
        *                 type="object",
        *                 @OA\Property(property="dress_id", type="string", example="The dress_id field is required."),
        *             ) 
        *         )     
        *     )
        * )
    */
    public function getDressMeasurement($id)
    {   
        $dress = Dress::with("measurement")->find($id); 

        if(empty($dress) || empty($dress->measurement?->id)) {
            return response()->json(['message' => 'Dress or Measurement not found'], 404);
        }

        $measurementId = $dress->measurement?->id;
        
        $values = MeasurementValue::with(['parameter', 'tailorCatParameter'])->where('measurement_id', $measurementId)->get();
        
        $values = $values->map(function ($value) {
            return [
                'id'   => $value->id,
                'parameter_id' => $value->parameter_id,
                'value' => $value->value,
                'image' => $value->parameter?->image,
                'tcp_id' => $value->tcp_id,
                'measurement_id' => $value->measurement_id,
                'label' => $value->tailorCatParameter?->label,
                'part' => $value->tailorCatParameter?->part,
            ];
        });
        
        return response()->json(['measurement_values' => $values , 'type' => $dress->type ], 200);   
    }


    /**
     * @OA\Put(
     *     path="/tailors/dresses/{id}/measurement",
     *     summary="Update the measurement of a dress",
     *     description="Allows a tailor to update the measurement of a dress based on the dress ID.",
     *     operationId="updateDressMeasurement",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Dress ID"
     *     ),
     *     
     *     @OA\RequestBody(
     *         description="Measurement data to update the dress",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     enum={"stitching", "alteration"},
     *                     description="Type of the dress"
     *                 ),
     *                 @OA\Property(
     *                     property="measurement_values",
     *                     type="array",
     *                     description="List of measurement values",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="tcp_id",
     *                             type="integer",
     *                             description="ID of the tailoring control point"
     *                         ),
     *                         @OA\Property(
     *                             property="value",
     *                             type="number",
     *                             format="float",
     *                             description="Measurement value"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Measurement updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Measurement updated"),
     *             @OA\Property(property="dress_id", type="integer", example=1, description="ID of the updated dress")
     *         )
     *     ),
     *     
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
    public function updateDressMeasurement(Request $request, $id)
    {
        $dress = Dress::find($id);

        if (empty($dress)) {
            return response()->json(['message' => 'Dress not found'], 404);
        }

        if ($request->has('type') ) {            
            $dress->type = $request->input('type');
            $dress->save();
        }

        $measurementId = $dress->measurement?->id;

        if ($request->has('measurement_values') && is_array($request->input('measurement_values'))) {
            // Validate the measurement values
            $measurementValues = $request->input('measurement_values');
            if ($measurementId) {
                // Update existing measurement values
                foreach ($measurementValues as $value) {
                    $measurementValue = MeasurementValue::where('measurement_id', $measurementId)
                        ->where('tcp_id', $value['tcp_id'])
                        ->first();

                    if ($measurementValue) {
                        $measurementValue->value = $value['value'];
                        $measurementValue->save();
                    } 
                }
            } 
        }

        return response()->json(['message' => 'Measurement updated', 'dress_id' => $id], 200);
    }



}
