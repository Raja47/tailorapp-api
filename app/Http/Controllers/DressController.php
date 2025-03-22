<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Dress;
use App\Models\Cloth;
use App\Models\DressImage;
use App\Models\Order;
use App\Models\Recording;
use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\TailorCategoryAnswer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DressController extends Controller
{
    //swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/dresses/create",
     *     summary="Create a dress with related entities",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"customer_id", "shop_id", "category_id", "type", "quantity", "price", "delivery_date", "trial_date", "measurementBoxes", "questionAnswers"},
     *             @OA\Property(property="customer_id", type="integer", description="Customer ID"),
     *             @OA\Property(property="shop_id", type="integer", description="Shop ID"),
     *             @OA\Property(property="category_id", type="integer", description="Category ID"),
     *             @OA\Property(property="type", type="string", enum={"stitching", "alteration"}, description="Dress type"),
     *             @OA\Property(property="quantity", type="integer", minimum=1, description="Dress quantity"),
     *             @OA\Property(property="price", type="number", description="Dress price"),
     *             @OA\Property(property="delivery_date", type="string", format="date", description="Delivery date"),
     *             @OA\Property(property="trial_date", type="string", format="date", description="Trial date"),
     *             @OA\Property(property="notes", type="string", nullable=true, description="Additional notes"),
     *             @OA\Property(
     *                 property="measurementBoxes",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="parameter_id", type="integer"),
     *                     @OA\Property(property="value", type="number", format="float")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="questionAnswers",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="question_id", type="integer"),
     *                     @OA\Property(
     *                         property="value",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string",
     *                             example="value1"
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="designImages",
     *                 type="array",
     *                 description="Array of design image paths",
     *                 @OA\Items(type="string", example="public\dress\image1.png")
     *             ),
     *             @OA\Property(
     *                 property="clothImages",
     *                 type="array",
     *                 description="Array of cloth image objects",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="path", type="string", example="public\dress\image4.png", description="Path of the cloth image"),
     *                     @OA\Property(property="title", type="string", example="cloth1", description="Title of the cloth"),
     *                     @OA\Property(property="length", type="number", example=56, description="Length of the cloth"),
     *                     @OA\Property(property="provided_by", type="string", example="tailor", description="Provider of the cloth"),
     *                     @OA\Property(property="price", type="number", example=1790, description="Price of the cloth")
     *                 )
     *             ),
     *            @OA\Property(property="audio", type="string", description="Path of audio recording")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dress created"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_id", type="integer"),
     *                 @OA\Property(property="dress_id", type="integer"),
     *                 @OA\Property(property="measurement_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "array", "items": {"type": "string"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function create(Request $request)
    {
        $rules = [
            'order_id' => 'nullable|exists:orders,id',
            'customer_id' => 'required|exists:tailor_customers,id',
            'shop_id' => 'required|exists:shops,id',
            'category_id' => 'required|exists:tailor_categories,id',
            'type' => 'required|in:stitching,alteration',
            'quantity' => 'required|integer|min:1',
            'price' => 'required',
            'delivery_date' => 'required|date',
            'trial_date' => 'required|date',
            'notes' => '',
            'measurementBoxes' => 'required|array',
            'questionAnswers' => 'required|array',
            'designImages' => 'required|array',
            'clothImages' => 'required|array',
            'audio' => ''
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Data validation error', 'error' => $validation->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $tailor_id = auth('sanctum')->user()->id;

            // if order id is not provided then it mean order is new with first dress being so create order
            if ($request->has('order_id')) {
                $order_id = $request->order_id;
            } else {
                $order_id = Order::create([
                    'customer_id' => $request->customer_id,
                    'tailor_id' => $tailor_id,
                    'shop_id' => $request->shop_id,
                    'name' => 'order-1',
                    'status' => 0,
                ])->id;
            }

            $dress = Dress::create([
                'order_id' => $order_id,
                'tailor_id' => $tailor_id,
                'shop_id' => $request->shop_id,
                'category_id' => $request->category_id,
                'name' => '',
                'type' => $request->type,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'delivery_date' => $request->delivery_date,
                'trial_date' => $request->trial_date,
                'notes' => $request->notes,
                'status' => 0,
            ]);
            $dress->update(['name' => '#D-' . $request->category_id . '-' . $dress->id]);

            $measurement = Measurement::create([
                'model' => 'dress',
                'model_id' => $dress->id,
                'status' => 1,
            ]);
            foreach ($request->measurementBoxes as $measurementBox) {
                $measurementBox['measurement_id'] = $measurement->id;
                MeasurementValue::newMeasurementValue($measurementBox);
            }

            foreach ($request->questionAnswers as $questionAnswer) {
                foreach ($questionAnswer['value'] as $value) {
                    TailorCategoryAnswer::create([
                        'tailor_id' => $tailor_id,
                        'dress_id' => $dress->id,
                        'question_id' => $questionAnswer['question_id'],
                        'value' => $value,
                    ]);
                }
            }

            foreach ($request->designImages as $designImage) {
                DressImage::create([
                    'tailor_id' => $tailor_id,
                    'dress_id' => $dress->id,
                    'order_id' => $order_id,
                    'type' => 'design',
                    'path' => $designImage
                ]);
            }

            foreach ($request->clothImages as $clothImage) {
                $dress_image = DressImage::create([
                    'tailor_id' => $tailor_id,
                    'dress_id' => $dress->id,
                    'order_id' => $order_id,
                    'type' => 'cloth',
                    'path' => $clothImage['path']
                ]);
                Cloth::create([
                    'title' => $clothImage['title'],
                    'dress_image_id' => $dress_image->id,
                    'length' => $clothImage['length'],
                    'provided_by' => $clothImage['provided_by'],
                    'price' => $clothImage['price']

                ]);
            }

            Recording::create([
                'dress_id' => $dress->id,
                'duration' => 0,
                'path' => $request->audio
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Dress and associated data created successfully',
                'data' => [
                    'order_id' => $order_id,
                    'dress_id' => $dress->id,
                    'measurement_id' => $measurement->id,
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function getOrderDressMeasurement($dress_id)
    {
        $measurement = Measurement::where([['model', 'dress'], ['model_id', $dress_id]])->first();
        return response()->json(['success' => true, 'message' => 'Dress Measurement', 'data' => ['Dress id' => $dress_id, 'Measurement' => $measurement]], 200);
    }

    /**
     * @OA\Post(
     *     path="/tailors/dresses/image",
     *     summary="Upload a dress image",
     *     description="Stores an image in 'public/dress'.",
     *     operationId="uploadDressImage",
     *     tags={"Dresses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(type="object", required={"image"},
     *                 @OA\Property(property="image", type="string", format="binary", description="Image file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Image uploaded",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Image uploaded"),
     *             @OA\Property(property="data", type="string", example="storage/dress/image.jpg")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */

    public function uploadImage(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded',
                'data' => $request->all() // Debugging: Check what is actually sent
            ], 400);
        }

        $validation = Validator::make([$request->image], ['required|image|mimes:jpeg,png,jpg,gif,svg']);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Data validation error', 'data' => $validation->errors()], 422);
        }
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/dress', $filename);

        $base_url = url('');
        $path = $base_url . '/storage/dress/' . $filename;

        return response()->json(['success' => true, 'message' => 'Image uploaded', 'data' => $path], 200);
    }

    /**
     * @OA\Post(
     *     path="/tailors/dresses/images",
     *     summary="Upload dress images",
     *     description="Stores images in 'public/dress'.",
     *     operationId="uploadDressImages",
     *     tags={"Dresses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(type="object", required={"images[]"},
     *                 @OA\Property(property="images[]", type="array", 
     *                      @OA\Items(type="string", format="binary"),
     *                      description="Array of image files")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Images uploaded",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Image uploaded"),
     *             @OA\Property(property="data", type="string", example="storage/dress/image.jpg")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function uploadImages(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
        ]);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Data validation error', 'data' => $validation->errors()], 422);
        }

        $uploadedImages = [];

        $files = is_array($request->file('images'))
            ? $request->file('images')
            : [$request->file('images')];

        foreach ($files as $file) {
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/dress', $filename);

            $base_url = url('');
            $uploadedImages[] = $base_url . '/storage/dress/' . $filename;
        }

        return response()->json(['success' => true, 'message' => 'Images uploaded succesfully', 'data' => $uploadedImages], 200);
    }

    /**
     * @OA\Post(
     *     path="/tailors/dresses/audio",
     *     summary="Upload audio file for a dress",
     *     description="Uploads an audio file, calculates its duration, and stores it.",
     *     operationId="uploadAudio",
     *     tags={"Dresses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(required={"audio"},
     *                 @OA\Property(property="audio", type="string", format="binary", description="Audio file to upload")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Audio uploaded successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Audio uploaded"),
     *             @OA\Property(property="data", type="string", example="00:03:45", description="Duration of the uploaded audio in HH:mm:ss format")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data validation error"),
     *             @OA\Property(property="data", type="object", example={"audio": {"The audio field is required."}})
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     )
     * )
     */
    public function uploadAudio(Request $request)
    {
        $validation = Validator::make($request->all(), ['audio' => 'required|mimes:aac,midi,mid,mp3,ogg,opus,wav,weba,aif,aiff,m4a,wma,au,snd,flac,adts,amr,ra,ram,asf']);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Data validation error', 'data' => $validation->errors()], 422);
        }
        $audio = $request->file('audio');
        $audioname = time() . '.' . $audio->getClientOriginalExtension();
        $audio->storeAs('public/dress', $audioname);

        $base_url = url('');
        $path = $base_url . '/storage/dress/' . $audioname;

        return response()->json(['success' => true, 'message' => 'Audio uploaded', 'data' => $path], 200);
    }

    /**
     * @OA\Get(
     *     path="/tailors/dresses/tab",
     *     summary="Get a list of tailor dresses based on filters",
     *     tags={"Dresses"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="timeFilter",
     *         in="query",
     *         required=true,
     *         description="Filter dresses based on time (e.g., all, today, due, late, last15days, thismonth, lastmonth, thisyear, lastyear)",
     *         @OA\Schema(type="string", enum={"all", "today", "due", "late", "last15days", "thismonth", "lastmonth", "thisyear", "lastyear"})
     *     ),
     *     @OA\Parameter(
     *         name="statusFilter",
     *         in="query",
     *         required=false,
     *         description="Filter dresses by status (nullable)",
     *         @OA\Schema(type="integer", enum={0, 1, 2, 3, 4}),
     *     ),
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         required=false,
     *         description="Search by dress name",
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="shop_id",
     *         in="query",
     *         required=true,
     *         description="Shop ID to filter dresses",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The page number for pagination",
     *         example=1
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Number of orders to retrieve per page",
     *         example=10
     *     ), 
     *     @OA\Response(
     *         response=200,
     *         description="Dresses found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dresses Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="Dresses",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Bridal Dress"),
     *                         @OA\Property(property="status", type="integer", example=2),
     *                         @OA\Property(property="delivery_date", type="string", format="date", example="2025-03-10"),
     *                         @OA\Property(property="picture", type="string", example="uploads/dress1.jpg"),
     *                         @OA\Property(property="customername", type="string", example="Ayesha Khan")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Invalid time filter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid time filter")
     *         )
     *     )
     * )
     */

    public function getTabDresses(Request $request)
    {
        $rules = [
            'timeFilter' => 'required',
            'statusFilter' => 'nullable|integer',
            'searchText' => 'nullable',
            'shop_id' => 'required',
            'page' => 'required|numeric|min:1',
            'perpage' => 'required|numeric|min:1'
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Order data validation error', 'data' => $validation->errors()], 422);
        }

        $tailor_id = auth('sanctum')->user()->id;
        $timeFilter = $request->input('timeFilter');
        $statusFilter = $request->input('statusFilter');
        $searchText = $request->input('searchText');
        $shop_id = $request->input('shop_id');
        $page = $request->input('page');
        $perpage = $request->input('perpage');
        $tailor_dresses = collect([]);
        $today = Carbon::today();

        $query = DB::table('dresses')
            ->select('dresses.*', 'orders.status', 'dress_images.path AS image', 'tailor_customers.name AS customername')
            ->leftjoin('orders', 'orders.id', '=', 'dresses.order_id')
            ->leftjoin('tailor_customers', 'tailor_customers.id', '=', 'orders.customer_id')
            ->leftjoin('dress_images', function ($join) {
                $join->on('dress_images.dress_id', '=', 'dresses.id');
                $join->where('dress_images.type', '=', 'design');
            })
            ->where('dresses.tailor_id', $tailor_id)
            ->where('dresses.shop_id', $shop_id);

        switch ($timeFilter) {
            case 'all':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter);
                }
                break;

            case 'today':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereDate('dresses.created_at', $today);
                } else {
                    $query->whereDate('dresses.created_at', $today);
                }
                break;

            case 'due':
                $due_time_list = [$today->format('y-m-d'), $today->copy()->addDays(1)->format('y-m-d'), $today->copy()->addDays(2)->format('y-m-d')];
                $query->whereIn('dresses.delivery_date', $due_time_list);
                break;

            case 'late':
                $query->where('dresses.delivery_date', '<', $today)->whereNotIn('orders.status', [2, 3]);
                break;

            case 'last15days':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->where('dresses.created_at', '>=', Carbon::now()->subDays(15));
                } else {
                    $query->where('dresses.created_at', '>=', Carbon::now()->subDays(15));
                }
                break;

            case 'thismonth':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereMonth('dresses.created_at', $today->month)->whereYear('dresses.created_at', $today->year);
                } else {
                    $query->whereMonth('dresses.created_at', $today->month)->whereYear('dresses.created_at', $today->year);
                }
                break;

            case 'lastmonth':
                $last_month = Carbon::today()->subMonth();
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereMonth('dresses.created_at', $last_month->month)->whereYear('dresses.created_at', $last_month->year);
                } else {
                    $query->whereMonth('dresses.created_at', $last_month->month)->whereYear('dresses.created_at', $last_month->year);
                }
                break;

            case 'thisyear':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereYear('dresses.created_at', $today->year);
                } else {
                    $query->whereYear('dresses.created_at', $today->year);
                }
                break;

            case 'lastyear':
                $last_year = Carbon::today()->subYear();
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereYear('dresses.created_at', $last_year->year);
                } else {
                    $query->whereYear('dresses.created_at', $last_year->year);
                }
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid time filter'], 500);
                break;
        }

        if ($request->filled('searchText')) {
            $query->where('dresses.name', 'like', '%' . $searchText . '%');
        }
        $tailor_dresses = $query->orderBy('dresses.updated_at', 'desc')->forpage($page, $perpage)->get()
            ->map(function ($dress) {
                $dress->delivery_date = Carbon::parse($dress->delivery_date)->toIso8601ZuluString();
                $dress->trial_date = Carbon::parse($dress->trial_date)->toIso8601ZuluString();
                $dress->created_at = Carbon::parse($dress->created_at)->toIso8601ZuluString();
                return $dress;
            });

        if (count($tailor_dresses) === 0) {
            return response()->json(['success' => true, 'message' => 'No Dresses Found', 'data' => ['Dresses' => $tailor_dresses]], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Dresses Found', 'data' => ['Dresses' => $tailor_dresses]], 200);
        }
    }

    //swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/dresses/store",
     *     summary="Create a new dress",
     *     description="Creates a new dress for a specific order and shop.",
     *     tags={"Dresses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"order_id", "shop_id", "category_id", "type", "quantity", "price"},
     *             @OA\Property(property="order_id", type="integer", description="Order ID", example=1),
     *             @OA\Property(property="shop_id", type="integer", description="Shop ID", example=1),
     *             @OA\Property(property="category_id", type="integer", description="Category ID", example=1),
     *             @OA\Property(property="type", type="string", description="Type of dress", example="casual"),
     *             @OA\Property(property="quantity", type="integer", description="Quantity of dresses", example=2),
     *             @OA\Property(property="price", type="number", format="float", description="Price of the dress", example=1500.50),
     *             @OA\Property(property="name", type="string", description="Dress name", example="Dress A"),
     *             @OA\Property(property="delivery_date", type="string", format="date", description="Delivery date", example="2024-10-01"),
     *             @OA\Property(property="trial_date", type="string", format="date", description="Trial date", example="2024-09-25"),
     *             @OA\Property(property="is_urgent", type="boolean", description="Is the dress urgent", example=true),
     *             @OA\Property(property="notes", type="string", description="Additional notes for the dress", example="Add lace at the sleeves")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dress Created Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dress Created Successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="Dress id", type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dress data validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dress data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Dress could not be created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dress could not be created"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */

    public function addDress(Request $request)
    {
        $rules = [
            'order_id' => 'required',
            'shop_id' => 'required',
            'category_id' => 'required',
            'type' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            'name' => '',
            'delivery_date' => '',
            'trial_date' => '',
            'is_urgent' => '',
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $dress = Dress::create([
                'order_id' => $request->order_id,
                'tailor_id' => $tailor_id,
                'shop_id' => $request->shop_id,
                'category_id' => $request->category_id,
                'name' => '',
                'type' => $request->type,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'delivery_date' => $request->delivery_date,
                'trial_date' => $request->trial_date,
                'is_urgent' => $request->is_urgent,
                'status' => 0,
                'notes' => $request->notes,

            ]);

            if ($dress->save()) {
                $dress_name = Dress::where('id', $dress->id)->first();
                $dress_name->name = '#D-' . $dress->category_id . '-' . $dress->id;
                return response()->json(['success' => true, 'message' => 'Dress Created Successfully', 'data' => ['Dress id' => $dress->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress could bot be created'], 500);
            }
        }
    }

    public function updateDress(Request $request)
    {
        $rules = [
            'dress_id' => 'required'
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $dress = Dress::where([['id', $request->dress_id], ['tailor_id', $tailor_id]])->first();
            $dress->category_id = $request->category_id;
            $dress->name = '';
            $dress->type = $request->type;
            $dress->quantity = $request->quantity;
            $dress->price = $request->price;
            $dress->delivery_date = $request->delivery_date;
            $dress->trial_date = $request->trial_date;
            $dress->is_urgent = $request->is_urgent;
            $dress->notes = $request->notes;

            if ($dress->save()) {
                return response()->json(['success' => true, 'message' => 'Dress Updated Successfully', 'data' => ['Dress id' => $dress->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress cannot be updated'], 500);
            }
        }
    }

    public function countDressesByStatus($shop_id, $index)
    {
        $now = Carbon::now();
        $tailor_id = auth('sanctum')->user()->id;
        switch ($index) {
            case 0:
                //this month
                $time = $now->copy()->subDay(10);
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->where('created_at', '<', $time)
                    ->groupBy('status')
                    ->get();
                break;

            case 1:
                //last 30 days
                $time = $now->copy()->subDay(30);
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->where('created_at', '<', $time)
                    ->groupBy('status')
                    ->get();
                break;

            case 2:
                //all
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->groupBy('status')
                    ->get();
                break;

            default:
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->groupBy('status')
                    ->get();
                break;
        }

        return $dresses;
    }

    public function countDresses()
    {
        $dress = Dress::where('status', '!=', 4)
            ->selectRaw('SUM(quantity) as countDress')
            ->selectRaw('SUM(quantity * price) as totalAmount')
            ->get();
        return $dress;
    }

    public function delete($dress_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $dress = Dress::where([['id', $dress_id], ['tailor_id', $tailor_id]])->get();
        $dress->delete();
        return response()->json(['success' => true, 'message' => 'Dress Deleted', 'data' => ['countDeletes' => $dress->count()]], 200);
    }


    /**
     * @OA\Get(
     *     path="/tailors/dresses/orders/{order_id}",
     *     summary="Get dresses for a specific order",
     *     description="Retrieves a list of dresses associated with a specific order, based on the tailor's authentication.",
     *     operationId="getOrderDresses",
     *     tags={"Dresses"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID of the order",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of dresses retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Elegant Dress"),
     *                 @OA\Property(property="category_id", type="integer", example=3),
     *                 @OA\Property(property="catName", type="string", example="Party Wear"),
     *                 @OA\Property(property="image", type="string", example="path/to/image.jpg"),
     *                 @OA\Property(property="order_id", type="integer", example=5),
     *                 @OA\Property(property="tailor_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-03T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-03T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found.")
     *         )
     *     )
     * )
     */
    public function getOrderDresses($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;

        $order_dresses = DB::table('dresses')
            ->select('dresses.*', 'tailor_categories.name AS catName', 'dress_images.path AS image')
            ->leftjoin('tailor_categories', 'tailor_categories.id', '=', 'dresses.category_id')
            ->leftjoin('dress_images', function ($join) {
                $join->on('dress_images.dress_id', '=', 'dresses.id');
                $join->where('dress_images.type', '=', 'design');
            })
            ->where('dresses.tailor_id', $tailor_id)->where('dresses.order_id', $order_id)->get()
            ->map(function ($dress) {
                $dress->delivery_date = Carbon::parse($dress->delivery_date)->toIso8601ZuluString();
                $dress->trial_date = Carbon::parse($dress->trial_date)->toIso8601ZuluString();
                return $dress;
            });

        if (count($order_dresses) === 0) {
            return response()->json(['success' => false, 'message' => 'No Dresses Found'], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Dresses Found', 'data' => ['Dresses' => $order_dresses]], 200);
        }
    }


    /**
     * @OA\Post(
     *     path="/tailors/dresses/updatestatus",
     *     summary="Update the status of a dress",
     *     description="Allows a tailor to update the status of a dress based on dress ID.",
     *     operationId="updateDressStatus",
     *     tags={"Dresses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dress_id", "status"},
     *             @OA\Property(property="dress_id", type="integer", example=101, description="ID of the dress to update"),
     *             @OA\Property(property="status", type="integer", example=2, description="New status of the dress")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dress status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dress Status Updated Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Dress id", type="integer", example=101, description="Updated dress ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dress data validation error"),
     *             @OA\Property(property="data", type="object", example={"dress_id": {"The dress_id field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dress Status could not be updated")
     *         )
     *     )
     * )
     */

    public function updateStatus(Request $request)
    {
        $rules = [
            'dress_id' => 'required',
            'status' => 'required'
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $dress = Dress::where([['id', $request->dress_id], ['tailor_id', $tailor_id]])->first();
            $dress->status = $request->status;

            if ($dress->save()) {
                return response()->json(['success' => true, 'message' => 'Dress Status Updated Successfully', 'data' => ['Dress id' => $request->dress_id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress Status could bot be updated'], 500);
            }
        }
    }
}
