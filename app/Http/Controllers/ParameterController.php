<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parameter;
use Illuminate\Support\Facades\Validator;

class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Get(
     *     path="/parameters",
     *     summary="Get all parameters",
     *     tags={"Parameters"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of parameters",
     *         @OA\JsonContent(
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
        $parameters = Parameter::all();
        if (count($parameters) === 0) {
            return response()->json(['parameters' => 'No parameters added'], 404);
        } else {
            return response()->json(['parameters' => $parameters], 200);
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
            'name' => 'required|unique:parameters',
            'label' => '',
            'image' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if($validation->fails())
        { return response()->json(['success'=>false, 'message'=>'Parameter data validation error','data' => $validation->errors() ] , 422); }

        else 
        {
            $parameter = Parameter::create([
                'name' => $request->name,
                'label' => $request->label,
                'image' => $request->image,
            ]);

            if($parameter->save()){
                return response()->json(['success' => true , 'message' => 'Parameter Created Successfully' , 'data' => ['id' => $parameter->id ] ] , 200);
            }else{
                return response()->json(['success' => false , 'message' => 'Parameter Creation Failed' , 'data' => [] ] , 422);
            }            
        }
    }

}