<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parameter;
use App\Models\TailorParameter;
use Illuminate\Support\Facades\Validator;


class TailorParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Get(
     *     path="/tailors/parameters",
     *     summary="Get all parameters for a specific tailor",
     *     tags={"Parameters"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="tailor_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="parameters",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Parameter 1"),
     *                     @OA\Property(property="type", type="string", example="string"),
     *                     @OA\Property(property="value", type="string", example="Some value"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-03T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-03T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No parameters added",
     *         @OA\JsonContent(
     *             @OA\Property(property="tailor_id", type="integer", example=1),
     *             @OA\Property(property="parameters", type="string", example="No parameters added")
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
        $parameters = TailorParameter::where('tailor_id', $tailor_id)->get();
        if (count($parameters) === 0) {
            return response()->json(['tailor_id' => $tailor_id, 'parameters' => 'No parameters added'], 404);
        } else {
            return response()->json(['tailor_id' => $tailor_id, 'parameters' => $parameters], 200);
        }
    }


    public function default()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $parameters = Parameter::all();
        foreach ($parameters as $parameter) {
            $tailor_parameter = TailorParameter::create([
                'tailor_id' => $tailor_id,
                'parameter_id' => $parameter->id,
                'name' => $parameter->name,
                'label' => $parameter->label,
                'image' => 'parameters/' . $parameter->image,
            ]);
        }
        if ($tailor_parameter->save()) {
            return response()->json(['success' => true, 'message' => 'Default parameters added successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Default parameters Creation Failed'], 422);
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
            'name' => 'required|unique:tailor_parameters',
            'label' => '',
            'image' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Category Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailor_parameter = TailorParameter::create([
                'tailor_id' => $tailor_id,
                'parameter_id' => 0,
                'name' => $request->name,
                'label' => $request->label,
                'image' => $request->image,
            ]);

            if ($tailor_parameter->save()) {
                return response()->json(['success' => true, 'message' => 'Parameter Created Successfully', 'data' => ['id' => $tailor_parameter->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Parameter Creation Failed', 'data' => []], 422);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'label' => '',
            'image' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Category Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailor_parameter = TailorParameter::where([['tailor_id', $tailor_id], ['id', $id]])->first();
            if (empty($tailor_parameter)) {
                return response()->json(['success' => false, 'message' => 'Category does not exist.'], 404);
            } else {
                $tailor_parameter->name = $request->name;
                $tailor_parameter->label = $request->label;
                $tailor_parameter->image = $request->image;
                if ($tailor_parameter->save()) {
                    return response()->json(['success' => true, 'message' => 'Parameter Updated Successfully', 'data' => ['id' => $tailor_parameter->id]], 200);
                } else {
                    return response()->json(['success' => false, 'message' => 'Parameter Updation Failed', 'data' => []], 422);
                }
            }
        }
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
