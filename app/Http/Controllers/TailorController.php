<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tailor;
use App\Models\Status;
use App\Models\TailorStatusSetting;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class TailorController extends Controller
{

    use SoftDeletes;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Tailor::all());
    }

    public function exists(Request $request)
    {
        $rules = ['number' => 'required'];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor = Tailor::where('number', $request->number)->first();
            if (empty($tailor)) {
                return response()->json(['success' => false], 422);
            } else {
                return response()->json(['success' => true], 200);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/tailors/username/{username}",
     *     summary="Check if username exists",
     *     security={{"bearerAuth": {}}},
     *     tags={"Tailors"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The username to check"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Username check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="exists", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    
    public function if_username($username)
    {
        $username = Tailor::where('username', $username)->first();
        if (empty($username)) {
            return response()->json(['exists' => false]);
        } else {
            return response()->json(['exists' => true]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/store",
     *     summary="Create a new tailor",
     *     tags={"Tailors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="Name of the tailor", maxLength=255),
     *             @OA\Property(property="password", type="string", description="Password of the tailor", minLength=4, maxLength=12),
     *             @OA\Property(property="username", type="string", description="Unique username of the tailor", maxLength=99),
     *             @OA\Property(property="number", type="string", description="Unique mobile number of the tailor", maxLength=15),
     *             @OA\Property(property="picture", type="string", description="Picture of the tailor"),
     *             @OA\Property(property="country_code", type="string", description="Country code of the tailor"),
     *             @OA\Property(property="city_id", type="integer", description="City ID of the tailor"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tailor Created Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", additionalProperties={ "type": "string" })
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'     => 'required|max:255',
            'password' => 'required|min:4|max:12',
            'username' => 'required|unique:tailors|max:99',
            'number'   => 'required|unique:tailors|max:15'
        ]);

        if ($validation->fails()) {
            // validation failed
            return response()->json(['success' => false, 'message' => 'Validation Error', 'data' => $validation->errors()], 422);
        }
        // validation passed
        $tailor = new Tailor();
        $tailor->name               = $request->input('name');
        $tailor->password           = $request->input('password');
        $tailor->username           = $request->input('username');
        $tailor->number             = $request->input('number');
        $tailor->picture            = $request->input('picture');
        $tailor->country_code       = $request->input('country_code');
        $tailor->city_id            = $request->input('city_id');
        $tailor->status             = 1;

        if ($tailor->save()) {
            // Tailor is created
            $categories = app('App\Http\Controllers\TailorCategoryController')->default($tailor->id);
            $cat_parameters = app('App\Http\Controllers\TailorCategoryParameterController')->default($tailor->id);
            $cat_questions = app('App\Http\Controllers\TailorCategoryQuestionController')->default($tailor->id);

            $statuses = Status::all();
            foreach ($statuses as $status) {
                TailorStatusSetting::create([
                    'tailor_id' => $tailor->id,
                    'status_id' => $status->id,
                    'is_active' => true,
                    'sort_order' => $status->sort_order,
                ]);
            }

            $token = $tailor->createToken('auth_token')->plainTextToken;
            return response()->json(['success' => true, 'message' => 'Tailor Created Successfully', 'data' => ['tailor' => $tailor->toArray(), "token" => $token , 'statuses' => $statuses]], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if (empty($request->input('number'))) {
            return response()->json(['success' => false, 'message' => 'Search Criteria failed', 'data' => []], 422);
        }

        $tailorNumber = $request->input('number');

        $tailor = Tailor::where('number', $tailorNumber)->first();

        if (!empty($tailor)) {
            return response()->json(['success' => true, 'message' => '', 'data' => ['tailor' => $tailor->toArray()]], 200);
        }

        return response()->json(['success' => false, 'message' => '', 'data' => []], 422);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/login",
     *     summary="Login a tailor",
     *     tags={"TailorAuth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="number", type="string", description="Tailor's mobile number"),
     *             @OA\Property(property="password", type="string", description="Tailor's password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login Successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="tailor", type="object", additionalProperties={ "type": "string" }),
     *                 @OA\Property(property="token", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Failed / Incorrect Mobile number password",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'password' => 'required',
            'number'  => 'required'
        ]);

        if ($validation->fails()) {
            // validation failed
            return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => $validation->errors()], 422);
        }

        $tailorNumber = $request->input('number');
        $password = $request->input('password');

        $tailor = Tailor::with('shops')->where('number', $tailorNumber)->where('password', $password)->first();

        if (empty($tailor)) {
            return response()->json(['success' => false, 'message' => 'Incorrect Number or Password'], 422);
        }

        $token = $tailor->createToken('auth_token')->plainTextToken;

        $statuses  = TailorStatusSetting::where('tailor_id', $tailor->id)
                        ->where('is_active', 1)
                        ->with('status')
                        ->orderBy('status.sort_order' ,'asc')
                        ->get();
        $statusResponse = [];
        foreach ($statuses as $key => $status) {
            $statusResponse[$key] = $status->status;
        }           

        return response()->json(['success' => true, 'data' => ['tailor' => $tailor->toArray(), 'token' => $token , 'statuses' => $statusResponse]], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'password'      => 'required',
            'number'  => 'required|min:10|max:15'
        ]);

        if ($validation->fails()) {
            // validation failed
            return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => $validation->errors()], 422);
        }

        $tailorNumber = $request->input('number');
        $password = $request->input('password');

        $tailor = Tailor::where('number', $tailorNumber)->first();

        if (empty($tailor)) {
            return response()->json(['success' => false, 'message' => 'Incorrect Mobile number password', 'data' => []], 200);
        }

        $tailor->password = $password;

        if ($tailor->save()) {
            return response()->json(['success' => true, 'message' => '', 'data' => ['tailor' => $tailor->toArray()]], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'id'      => 'required|numeric',
        ]);

        if ($validation->fails()) {
            // validation failed
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'data' => $validation->errors()], 422);
        }

        $tailor = Tailor::find($request->input('id'));

        if ($tailor) {

            $tailor->delete();
            return response()->json(['status' => 'success', 'message' => 'Tailor Deleted successfully', 'data' => []], 200);
        } else {

            return response()->json(['status' => 'error', 'message' => 'Tailor doesnt exist', 'data' => []], 404);
        }
    }
    public function logout(Request $request)
    {
        // $tailor = Tailor::where('id',$request->tailor_id)->first();
        // $tailor->tokens()->delete();
        $request->user()->currentAccessToken()->delete();
        return 'logged out';
    }
}
