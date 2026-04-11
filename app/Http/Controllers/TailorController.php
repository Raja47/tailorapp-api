<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tailor;
use App\Models\Status;
use App\Models\TailorStatusSetting;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


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

       $request->validate([
            'name' => 'required|max:255',

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:tailors,email',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->email_verified && $request->filled('email')) {
                        $fail('Email is required.');
                    }
                },
            ],

            'number' => [
                'nullable',
                'max:15',
                'unique:tailors,number',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->number_verified && !$request->filled('number')) {
                        $fail('Number is required.');
                    }
                },
            ],

            'password' => 'required|min:4|max:12',
            'username' => 'required|max:20|unique:tailors,username',
        ]);

        // validation passed
        $tailor = new Tailor();
        $tailor->name               = $request->input('name');
        $tailor->email_verified     = $request->input('email_verified') ?? false;
        $tailor->number_verified    = $request->input('number_verified') ?? false;
        $tailor->email              = $request->input('email');
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
        $request->validate([
            'name' => 'required|max:255',

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:tailors,email',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->number_verified && empty($value)) {
                        $fail('Email is required when number is not verified.');
                    }
                },
            ],

            'number' => [
                'nullable',
                'max:15',
                'unique:tailors,number',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->email_verified && empty($value)) {
                        $fail('Phone number is required when email is not verified.');
                    }
                },
            ],

            'password' => 'required|min:4|max:12',
            'username' => 'required|max:99|unique:tailors,username',

            'email_verified' => 'required|boolean',
            'number_verified' => 'required|boolean',
        ]);

        $type = $request->type;
        if($type == 'email'){
            $tailor = \App\Models\Tailor::with('shops')->where('email', $request->identifier)->first();
        } else {
            $tailor = \App\Models\Tailor::with('shops')->where('number', $request->identifier)->first();
        }

        if (empty($tailor)) {
            return response()->json(['success' => false, 'message' => 'Tailor does not exist'], 404);
        }

        if($tailor->password != $request->input('password')){
            return response()->json(['success' => false, 'message' => 'Invalid Credentials'], 409);
        }
        
        $token = $tailor->createToken('auth_token')->plainTextToken;

        $statuses = TailorStatusSetting::select('statuses.*')->where('tailor_id', $tailor->id)
                ->where('is_active', 1)
                ->join('statuses', 'statuses.id', '=', 'tailor_status_settings.status_id')
                ->orderBy('statuses.sort_order')
                ->get();

        return response()->json(['success' => true, 'data' => ['tailor' => $tailor->toArray(), 'token' => $token , 'statuses' => $statuses]], 200);
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
        $request->validate([
            'password' => 'required|min:4|max:12',
            'type' => 'required|in:email,phone',
            'identifier' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {

                    if ($request->type === 'email') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail('InValid Email format.');
                        }
                    }

                    if ($request->type === 'phone') {
                        // E.164 format: +923001234567
                        if (!preg_match('/^\+[1-9]\d{7,14}$/', $value)) {
                            $fail('Invalid Mobile Number , see (e.g. +923001234567).');
                        }
                    }
                }
            ],
        ]);

        $type = $request->type;
        if($type == 'email'){
            $tailor = \App\Models\Tailor::where('email', $request->identifier)->first();
        } else {
            $tailor = \App\Models\Tailor::where('number', $request->identifier)->first();
        }

        if(empty($tailor)){
            return response()->json(['success'=>false, 'message'=>"Tailor not found with this $type"] ,404);
        }
        
        $password = $request->input('password');
        $tailor->password = $password;

        if ($tailor->save()) {
            return response()->json(['success' => true, 'message' => '', 'data' => []], 200);
        }else {
            return response()->json(['success' => false, 'message' => 'Failed to update password', 'data' => []], 500);
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
