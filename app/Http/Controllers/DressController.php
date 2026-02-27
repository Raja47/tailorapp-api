<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Dress;
use App\Models\Expense;
use App\Models\Cloth;
use App\Models\DressImage;
use App\Models\Order;
use App\Models\Recording;
use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Tailor;
use App\Models\TailorCategory;
use App\Models\TailorCategoryAnswer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class DressController extends Controller
{
    //swagger annotations
    /**
     * @OA\Post(
     *     path="/dress/create",
     *     summary="Create a dress with related entities",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"order_id", "customer_id", "shop_id", "category_id", "type", "quantity", "price", "delivery_date", "trial_date", "measurement_values", "questionAnswers"},
     *             @OA\Property(property="order_id", type="integer", description="Order ID", example=11),
     *             @OA\Property(property="customer_id", type="integer", description="Customer ID", example=1),
     *             @OA\Property(property="shop_id", type="integer", description="Shop ID", example=1),
     *             @OA\Property(property="category_id", type="integer", description="Category ID", example=4),
     *             @OA\Property(property="type", type="string", enum={"stitching", "alteration"}, description="Dress type", example="stitching"),
     *             @OA\Property(property="quantity", type="integer", minimum=1, description="Dress quantity", example=1),
     *             @OA\Property(property="price", type="number", description="Dress price", example=999),
     *             @OA\Property(property="delivery_date", type="string", format="date", description="Delivery date", example="2025-06-19"),
     *             @OA\Property(property="trial_date", type="string", format="date", description="Trial date", example="2025-06-19"),
     *             @OA\Property(property="notes", type="string", nullable=true, description="Additional notes", example="string"),
     *             @OA\Property(
     *                 property="measurement_values",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="parameter_id", type="integer", example=0),
     *                     @OA\Property(property="value", type="number", format="float", example=0)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="questionAnswers",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=0),
     *                     @OA\Property(property="question_id", type="integer", example=0),
     *                     @OA\Property(
     *                         property="value",
     *                         type="array",
     *                         @OA\Items(type="string", example="value1")
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
     *                 property="clothes",
     *                 type="array",
     *                 description="Array of cloth image objects",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="path", type="string", example="public\dress\image4.png", description="Path of the cloth image"),
     *                     @OA\Property(property="title", type="string", example="cloth1", description="Title of the cloth"),
     *                     @OA\Property(property="length", type="number", example=56, description="Length of the cloth"),
     *                     @OA\Property(property="provided_by", type="string", example="tailor", description="Provider of the cloth"),
     *                     @OA\Property(property="price", type="number", example=4, description="Price of the cloth")
     *                 )
     *             ),
     *             @OA\Property(property="recording", type="string", description="Path of recording recording", example="string")
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
            'shop_id' => 'required|exists:shops,id',
            'category_id' => 'required|exists:tailor_categories,id',
            'type' => 'required|in:stitching,alteration',
            'quantity' => 'required|integer|min:1',
            'price' => 'required',
            'delivery_date' => 'required|date',
            'trial_date' => 'nullable|date',
            'notes' => '',
            'measurement_values' => 'required|array',
            'questionAnswers' => 'nullable|array',
            'designImages' => 'nullable|array',
            'clothes' => 'nullable|array',
            'recording' => 'nullable|string',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Data validation error', 'error' => $validation->errors()], 422);
        }


        DB::beginTransaction();
        try {
            $tailor_id = auth('sanctum')->user()->id;

            // if order id is not provided then it mean order is new with first dress being so create order
            if ($request->has('order_id') && $request->order_id != null) {
                $order_id = $request->order_id;
            } else {
                $order_id = Order::create([
                    'customer_id' => $request->customer_id,
                    'tailor_id' => $tailor_id,
                    'shop_id' => $request->shop_id,
                    'payment_status' => 19,
                    'status' => 1,
                ])->id;
            }

            $order = Order::findOrFail($order_id);

            $tailorCategory = TailorCategory::findOrFail($request->category_id);

            $dress = Dress::create([
                'order_id' => $order_id,
                'tailor_id' => $tailor_id,
                'shop_id' => $request->shop_id,
                'category_id' => $request->category_id,
                'tailor_customer_id' => $order->customer_id,
                'order_name' => $order->name,
                'category_name' => $tailorCategory->name,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'delivery_date' => $request->delivery_date,
                'trial_date' => $request->trial_date,
                'notes' => $request->notes,
                'status' => 8,
            ]);

            $order->increment('total_dress_amount', $request->price * $request->quantity);

            $measurement = Measurement::create([
                'model' => 'dress',
                'model_id' => $dress->id,
                'status' => 1,
            ]);

            foreach ($request->measurement_values as $measurement_value) {
                $measurement_value['measurement_id'] = $measurement->id;
                MeasurementValue::newMeasurementValue($measurement_value);
            }

            $answersToInsert = [];
            foreach ($request->questionAnswers as $questionAnswer) {
                $answersToInsert[] = [
                    'tailor_id' => $tailor_id,
                    'dress_id' => $dress->id,
                    'tcq_id' => $questionAnswer['id'], // Assuming tcq_id is the same as question_id   
                    'question_id' => $questionAnswer['id'],
                    'value' => is_array($questionAnswer['value']) ? implode(",", $questionAnswer['value']) : $questionAnswer['value'],
                ];
            }
            // Insert all answers in one go
            TailorCategoryAnswer::insert($answersToInsert);

            foreach ($request->designImages as $designImage) {
                DressImage::create([
                    'tailor_id' => $tailor_id,
                    'dress_id' => $dress->id,
                    'order_id' => $order_id,
                    'type' => 'design',
                    'path' => relative_url($designImage['path']),
                    'thumb_path' => relative_thumb_url($designImage['path']),
                ]);
            }

            foreach ($request->clothes as $cloth) {
                $dressImage = null;
                if (isset($cloth['path']) && !empty($cloth['path'])) {
                    $dressImage = DressImage::create([
                        'tailor_id' => $tailor_id,
                        'dress_id' => $dress->id,
                        'order_id' => $order_id,
                        'type' => 'cloth',
                        'path' => relative_url($cloth['path']),
                        'thumb_path' => relative_thumb_url($cloth['path']),
                    ]);
                }

                $clothPrice =  (isset($cloth['price']) && $cloth['provided_by'] == 'tailor') ? $cloth['price'] : null;

                $clothRecord = Cloth::create([
                    'dress_id' => $dress->id,
                    'order_id' => $order_id,
                    'tailor_id' => $tailor_id,
                    'title' => $cloth['title'],
                    'dress_image_id' => $dressImage?->id,
                    'length' => $cloth['length'] ?? 0,
                    'unit'  => $cloth['unit'],
                    'provided_by' => $cloth['provided_by'],
                    'price' => $clothPrice
                ]);

                if ($clothPrice != null) {
                    $expense = Expense::create([
                        'amount' => $cloth['price'],
                        'order_id' => $order_id,
                        'title' => 'Cloth Expense',
                        'tailor_id' => $tailor_id,
                        'dress_id' => $dress->id,
                        'cloth_id' => $clothRecord->id,
                    ]);
                    // @todo: check if we can do this via expense observer
                    $order->increment('total_expenses', $expense->amount);
                }
            } // <-- This closes the foreach ($request->clothes as $clothImage) loop

            $order->refreshFinancialStatus();


            if (!empty($request->recording)) {
                Recording::create([
                    'dress_id' => $dress->id,
                    'duration' => 0,
                    'path' => relative_url($request->recording),
                ]);
            }

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
     *     path="/dress/image",
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
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        $path = 'storage/dress/' . $filename;
        $file->storeAs('public/dress', $filename);

        $compressed_file = Image::make($file)->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode($file->getClientOriginalExtension(), 50);
        $thumb_path = 'storage/dress/thumbnails/' . $filename;
        Storage::put('public/dress/thumbnails/' . $filename, $compressed_file);

        $uploadedImage = [
            'path' => complete_url($path),
            'thumb_path' => complete_url($thumb_path)
        ];
        return response()->json(['success' => true, 'message' => 'Image uploaded', 'data' => $uploadedImage], 200);
    }

    /**
     * @OA\Post(
     *     path="/dress/images",
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

            $path = 'storage/dress/' . $filename;
            $file->storeAs('public/dress', $filename);

            $compressed_file = Image::make($file)->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode($file->getClientOriginalExtension(), 50);
            $thumb_path = 'storage/dress/thumbnails/' . $filename;
            Storage::put('public/dress/thumbnails/' . $filename, $compressed_file);

            $uploadedImages[] = [
                'path' => complete_url($path),
                'thumb_path' => complete_url($thumb_path)
            ];
        }

        return response()->json(['success' => true, 'message' => 'Images uploaded succesfully', 'data' => $uploadedImages], 200);
    }

    /**
     * @OA\Post(
     *     path="/dress/recording",
     *     summary="Upload recording file for a dress",
     *     description="Uploads an recording file, calculates its duration, and stores it.",
     *     operationId="uploadAudio",
     *     tags={"Dresses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(required={"recording"},
     *                 @OA\Property(property="recording", type="string", format="binary", description="Recording file to upload")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Recording uploaded successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Recording uploaded"),
     *             @OA\Property(property="data", type="string", example="00:03:45", description="Duration of the uploaded recording in HH:mm:ss format")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data validation error"),
     *             @OA\Property(property="data", type="object", example={"recording": {"The recording field is required."}})
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
        $validation = Validator::make($request->all(), ['recording' => 'required']);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Data validation error', 'data' => $validation->errors()], 422);
        }
        $recording = $request->file('recording');
        $audioname = time() . '.' . $recording->getClientOriginalExtension();
        $recording->storeAs('public/dress', $audioname);

        $path = complete_url('storage/dress/' . $audioname);
        return response()->json(['success' => true, 'message' => 'Recording uploaded', 'data' => $path], 200);
    }

    /**
     * @OA\Get(
     *     path="/dress/tab",
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
            ->select('dresses.*', 'images.thumb_path AS image', 'tailor_customers.name AS customer_name')
            ->leftjoin('orders', 'orders.id', '=', 'dresses.order_id')
            ->leftjoin('tailor_customers', 'tailor_customers.id', '=', 'dresses.tailor_customer_id')
            ->leftJoin(DB::raw('(
                SELECT di1.dress_id, di1.thumb_path
                    FROM dress_images di1
                    INNER JOIN (
                        SELECT dress_id, MIN(id) AS min_id
                        FROM dress_images
                        WHERE type = "cloth"
                        GROUP BY dress_id
                    ) di2 ON di1.id = di2.min_id
                ) as images'), 'images.dress_id', '=', 'dresses.id')
            ->where('dresses.tailor_id', $tailor_id)
            ->where('dresses.shop_id', $shop_id);

        switch ($timeFilter) {
            case 'all':
                if ($request->filled('statusFilter')) {
                    $query->where('dresses.status', $statusFilter);
                }
                break;

            case 'today':
                if ($request->filled('statusFilter')) {
                    $query->where('dresses.status', $statusFilter)->whereDate('dresses.created_at', $today);
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
                    $query->where('dresses.status', $statusFilter)->where('dresses.created_at', '>=', Carbon::now()->subDays(15));
                } else {
                    $query->where('dresses.created_at', '>=', Carbon::now()->subDays(15));
                }
                break;

            case 'thismonth':
                if ($request->filled('statusFilter')) {
                    $query->where('dresses.status', $statusFilter)->whereMonth('dresses.created_at', $today->month)->whereYear('dresses.created_at', $today->year);
                } else {
                    $query->whereMonth('dresses.created_at', $today->month)->whereYear('dresses.created_at', $today->year);
                }
                break;

            case 'lastmonth':
                $last_month = Carbon::today()->subMonth();
                if ($request->filled('statusFilter')) {
                    $query->where('dresses.status', $statusFilter)->whereMonth('dresses.created_at', $last_month->month)->whereYear('dresses.created_at', $last_month->year);
                } else {
                    $query->whereMonth('dresses.created_at', $last_month->month)->whereYear('dresses.created_at', $last_month->year);
                }
                break;

            case 'thisyear':
                if ($request->filled('statusFilter')) {
                    $query->where('dress.status', $statusFilter)->whereYear('dresses.created_at', $today->year);
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
            $query->where('dresses.name', 'like', '%' . $searchText . '%')->orWhere('tailor_customers.name', 'like', '%' . $searchText . '%');
        }
        $tailor_dresses = $query->orderBy('dresses.updated_at', 'desc')->forpage($page, $perpage)->get()
            ->map(function ($dress) {
                $dress->image = $dress->image ? complete_url($dress->image) : null;
                $dress->delivery_date = Carbon::parse($dress->delivery_date)->toIso8601ZuluString();
                $dress->trial_date = Carbon::parse($dress->trial_date)->toIso8601ZuluString();
                $dress->created_at = Carbon::parse($dress->created_at)->toIso8601ZuluString();
                return $dress;
            });

        return response()->json(['success' => true, 'message' => 'Dresses Found', 'data' => ['dresses' => $tailor_dresses]], 200);
    }

    //swagger annotations
    /**
     * @OA\Post(
     *     path="/dress/store",
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

    // public function addDress(Request $request)
    // {
    //     $rules = [
    //         'order_id' => 'required',
    //         'shop_id' => 'required',
    //         'category_id' => 'required',
    //         'type' => 'required',
    //         'quantity' => 'required',
    //         'price' => 'required',
    //         'name' => '',
    //         'delivery_date' => '',
    //         'trial_date' => '',
    //         'is_urgent' => '',
    //     ];
    //     $validation = Validator::make($request->all(), $rules);

    //     if ($validation->fails()) {
    //         return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
    //     } else {
    //         $tailor_id = auth('sanctum')->user()->id;
    //         $dress = Dress::create([
    //             'order_id' => $request->order_id,
    //             'tailor_id' => $tailor_id,
    //             'shop_id' => $request->shop_id,
    //             'category_id' => $request->category_id,
    //             'name' => '',
    //             'type' => $request->type,
    //             'quantity' => $request->quantity,
    //             'price' => $request->price,
    //             'delivery_date' => $request->delivery_date,
    //             'trial_date' => $request->trial_date,
    //             'is_urgent' => $request->is_urgent,
    //             'status' => 0,
    //             'notes' => $request->notes,

    //         ]);

    //         if ($dress->save()) {
    //             $dress_name = Dress::where('id', $dress->id)->first();
    //             $dress_name->name = '#D-' . $dress->category_id . '-' . $dress->id;
    //             return response()->json(['success' => true, 'message' => 'Dress Created Successfully', 'data' => ['Dress id' => $dress->id]], 200);
    //         } else {
    //             return response()->json(['success' => false, 'message' => 'Dress could bot be created'], 500);
    //         }
    //     }
    // }

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

    /**
     * @OA\Get(
     *   path="/shops/{shop_id}/dresses-count-by-status",
     *   summary="Count dresses by status",
     *   description="Counts the number of dresses by their status for a specific shop.",
     *   operationId="countDressesByStatus",
     *   tags={"Dresses"},
     *   security={{ "bearerAuth": {} }},
     *   @OA\Parameter(
     *        name="shop_id",
     *        in="path",
     *        required=true,        
     *        @OA\Schema(type="integer"),
     *       description="ID of the shop to filter dresses"
     * *    ),
     *   @OA\Response(
     *       response=200,
     *       description="Count by status retrieved successfully",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="success", type="boolean", example=true),
     *           @OA\Property(property="message", type="string", example="Count by status retrieved successfully"),
     *           @OA\Property(property="data", type="array", @OA\Items(
     *               type="object",
     *               @OA\Property(property="status", type="string", example="stitching"),
     *               @OA\Property(property="count", type="integer", example=10)
     *           ))  
     *       )
     *   ),
     *   @OA\Response(
     *       response=422,
     *       description="Validation error",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="success", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Data validation error"),
     *           @OA\Property(property="data", type="object")
     *       )
     *   ),
     *   @OA\Response(
     *       response=500,
     *       description="Internal server error",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="success", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Internal server error"),
     *           @OA\Property(property="data", type="object")
     *       )
     *   )
     * )
     */
    public function countByStatus($shop_id)
    {
        $now = Carbon::now();
        $time = $now->copy()->subDay(30);

        $dresses = Dress::select('status')
            ->selectRaw('SUM(quantity) as count')
            ->where('shop_id', $shop_id)
            ->where('created_at', '>', $time)
            ->groupBy('status')
            ->get();

        return response()->json(['success' => true, 'message' => 'Count by status retrieved successfully', 'data' => $dresses], 200);
    }




    public function getMonthlyStatsOfDressesToBeDelivered($shop_id)
    {
        $start = now()->startOfDay();
        $end = now()->addDays(30)->endOfDay();

        $counts = Dress::selectRaw('delivery_date, sum(quantity) as total')
            ->where('shop_id', $shop_id)
            ->whereBetween('delivery_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->groupBy('delivery_date')
            ->pluck('total', 'delivery_date')
            ->map(fn ($value) => (int) $value);
        
        return response()->json($counts);
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

    public function destroy($id)
    {
        $tailor_id = auth('sanctum')->user()->id;
            
        $dress = Dress::with('order')->where('id', $id)
                    ->where('tailor_id', $tailor_id)
                    ->first();

        if (!$dress) {
            return response()->json([
                'success' => false,
                'message' => 'Dress not found'
            ], 404);
        }

        DB::beginTransaction();
        try {

            $order = $dress->order;
            if($dress->delete()) {
                $order->decrement('total_dress_amount', $dress->price * $dress->quantity);
                $order->refreshFinancialStatus();
            }
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Dress could not be deleted'
            ] ,500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dress Deleted'
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/dress/orders/{order_id}",
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
            ->select('dresses.*', 'tailor_categories.name AS catName', 'images.path AS image')
            ->leftjoin('tailor_categories', 'tailor_categories.id', '=', 'dresses.category_id')
            ->leftJoin(DB::raw('(
                SELECT di1.dress_id, di1.path
                    FROM dress_images di1
                    INNER JOIN (
                        SELECT dress_id, MIN(id) AS min_id
                        FROM dress_images
                        WHERE type = "cloth"
                        GROUP BY dress_id
                    ) di2 ON di1.id = di2.min_id
                ) as images'), 'images.dress_id', '=', 'dresses.id')
            ->where('dresses.tailor_id', $tailor_id)->where('dresses.order_id', $order_id)->get()
            ->map(function ($dress) {
                $dress->image = $dress->image ? complete_url($dress->image) : null;
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
     *     path="/dress/updatestatus",
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
            'status_id' => 'required'
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $dress = Dress::where([['id', $request->dress_id], ['tailor_id', $tailor_id]])->first();

            if (!$dress) {
                return response()->json(['success' => false, 'message' => 'Dress not found'], 404);
            }

            $dress->status = $request->status_id;

            if ($dress->save()) {
                return response()->json(['success' => true, 'message' => 'Dress Status Updated Successfully', 'data' => ['Dress id' => $request->dress_id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress Status could bot be updated'], 500);
            }
        }
    }


    /**
     * Get a dress by ID.
     * @OA\Get(
     *      path="/dress/{id}",
     *      summary="Get a dress by ID",
     *      description="Retrieves a dress by its ID, including its measurement, clothes, and images.",
     *      operationId="getDressById",
     *      tags={"Dresses.edit"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(type="integer"),
     *     description="Dress ID"
     *    ),
     * @OA\Response(
     *     response=200,
     *     description="Dress retrieved successfully",
     *    @OA\JsonContent(
     *        type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *          @OA\Property(property="name", type="string", example="Elegant Dress"),
     *          @OA\Property(property="category_id", type="integer", example=3),
     *          @OA\Property(property="type", type="string", example="alteration"),
     *          @OA\Property(property="quantity", type="integer", example=2),
     *          @OA\Property(property="price", type="number", format="float", example=1500.50),
     *          @OA\Property(property="delivery_date", type="string", format="date", example="2024-10-01"),
     *          @OA\Property(property="trial_date", type="string", format="date", example="2024-10-15"),
     *          @OA\Property(property="is_urgent", type="boolean", example=true),
     *          @OA\Property(property="status", type="integer", example=1),
     *          @OA\Property(property="notes", type="string", example="Add lace at the sleeves"),
     *          @OA\Property(property="measurement", type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *          @OA\Property(property="dress_id", type="integer", example=1),
     *          @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-03T12:00:00Z"),
     *          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-03T12:00:00Z")
     *          ),
     *        
     *     @OA\Property(property="measurement_values", type="array",
     *         @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="parameter_id", type="integer", example=1),
     *             @OA\Property(property="value", type="number", format="float", example=34.5),
     *             @OA\Property(property="image", type="string", example="uploads/measurement1.jpg"),
     *             @OA\Property(property="tcp_id", type="integer", example=1),   
     *             @OA\Property(property="label", type="string", example="Chest"),
     *             @OA\Property(property="part", type="string", example="Upper Body"),  
     *             @OA\Property(property="measurement_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Property(property="clothes", type="array",
     *         @OA\Items(
     *             type="object",  
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="dress_id", type="integer", example=1),
     *             @OA\Property(property="path", type="string", example="uploads/cloth1.jpg"),
     *             @OA\Property(property="thumb_path", type="string", example="uploads/thumb_cloth1.jpg"),
     *             @OA\Property(property="title", type="string", example="Cloth Title"),
     *             @OA\Property(property="length", type="string", example="2.5 meters"),
     *             @OA\Property(property="provided_by", type="string", example="Supplier Name"),
     *             @OA\Property(property="price", type="number", format="float", example=500.00),
     *             @OA\Property(property="order_name", type="string", example="1OR123"),
     *             @OA\Property(property="category_name", type="string", example="Shirt"),
     *             @OA\Property(property="customer_name", type="string", example="Raja Ram"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-03T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-03T12:00:00Z")
     *         )
     *     ),
     *     @OA\Property(property="designs", type="array",
     *         @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="path", type="string", example="uploads/design1.jpg"),
     *           @OA\Property(property="thumb_path", type="string", example="uploads/thumb_design1.jpg") 
     *         )
     *     ),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-03T12:00:00Z"),  
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-03T12:00:00Z")
     * ),
     * ), 
     * @OA\Response(
     *     response=404,
     *     description="Dress not found",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="message", type="string", example="Dress not found")
     *     )
     * ),
     * @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="message", type="string", example="An error occurred while retrieving the dress")
     *      )
     *  )
     *)
     */
    public function show($id)
    {
        $dress = Dress::with(['order','customer','category', 'measurement', 'designs'])->find($id);

        if (empty($dress)) {
            return response()->json(['message' => 'Dress not found'], 404);
        }

        // Getting measurements for the dress
        $dress->measurement_values = [];
        $values = [];
        if ($dress->measurement?->id) {
            $values = MeasurementValue::with(['parameter', 'tailorCatParameter'])->where('measurement_id', $dress->measurement->id)->get();
            $values = $values->map(function ($value) {
                return [
                    'id'   => $value->id,
                    'parameter_id' => $value->parameter_id,
                    'value' => $value->value ?? '',
                    'image' => $value->parameter?->image ? complete_url($value->parameter->image) : null,
                    'tcp_id' => $value->tcp_id,
                    'measurement_id' => $value->measurement_id,
                    'label' => $value->tailorCatParameter?->label,
                    'part' => $value->tailorCatParameter?->part,
                ];
            });
        }
        $dress->measurement_values = $values;
    
        // Getting clothes for the dress
        $dress->clothes = [];
        $clothes = Cloth::with('image')->where('dress_id', $id)->get();
        if ($clothes->isEmpty()) {
            $dress->clothes = [];
        } else {
            $dress->clothes = $clothes->map(function ($cloth) {
                return [
                    'id' => $cloth->id,
                    'dress_id' => $cloth->dress_id,
                    'path' => $cloth->image?->path ? complete_url($cloth->image->path) : null,
                    'thumb_path' => $cloth->image?->thumb_path ? complete_url($cloth->image->thumb_path) : null,
                    'title' => $cloth->title,
                    'length' => $cloth->length,
                    'unit' => $cloth->unit, 
                    'provided_by' => $cloth->provided_by,
                    'price' => $cloth->price,
                    'created_at' => $cloth->created_at->toIso8601ZuluString(),
                    'updated_at' => $cloth->updated_at->toIso8601ZuluString(),
                ];
            });
        }

        // Getting design images for the dress
        $onlyDesigns = $dress->designs->map(function ($design) {
            return [
                'id'        => $design->id,
                'path'      => complete_url($design->path),
                'thumb_path' => complete_url($design->thumb_path),
            ];
        })->values();
        $dress->setRelation('designs', $onlyDesigns);  // setRelation is used to replace the designs relation with the modified collection


        // Getting Question Answer for the dress
        $answers = TailorCategoryAnswer::with('question')->where('dress_id', $id)->get();
        $questions = $answers->map(function ($answer) {
            return [
                'id' => $answer->question?->id,
                'tailor_id' => $answer->tailor_id,
                'category_id' => $answer->question?->category_id,
                'dress_id' => $answer->dress_id,
                'question' => $answer->question?->question,
                'type' => $answer->question?->type,
                'options' => $answer->question?->options,
                'value' => ( $answer && $answer->question?->isMulti()) ? explode(',',$answer->value) : $answer->value, 
                'created_at' => $answer->created_at?->toIso8601ZuluString(),
                'updated_at' => $answer->updated_at?->toIso8601ZuluString(),
            ];
        });
        $dress->questions = $questions;
        
        // Getting recording for the dress
        $recording = null;
        if(Recording::where('dress_id', $id)->exists()) {
            $recording = complete_url(Recording::where('dress_id', $id)->value('path'));
        }
    
        $dress->recording = $recording;
        // Format date fields to ISO 8601 Zulu string
        $dress->order_name = $dress->order ? $dress->order->name : null;
        $dress->customer_name = $dress->customer ? $dress->customer->name : null;
        $dress->category_name = $dress->category ? $dress->category->name : null;
        $dress->delivery_date = Carbon::parse($dress->delivery_date)->toIso8601ZuluString();
        $dress->trial_date = Carbon::parse($dress->trial_date)->toIso8601ZuluString();
        $dress->created_at = Carbon::parse($dress->created_at)->toIso8601ZuluString();
        $dress->updated_at = Carbon::parse($dress->updated_at)->toIso8601ZuluString();


        return response()->json(['success' => true, 'message' => 'Dress Details', 'data' => $dress], 200);
    }


    /**
     * @OA\Get(
     *     path="/dress/{id}/details",
     *     summary="Get basic details for a dress",
     *     description="Returns basic details for a dress based on the dress ID.",
     *     operationId="getBasicDetails",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,        
     *         @OA\Schema(type="integer"),
     *         description="Dress ID"        
     *     ),
     *         @OA\Response(
     *         response=200,
     *         description="Basic details for the dress",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Basic details for the dress"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="dress_Id", type="integer", example=1),
     *                 @OA\Property(property="delivery_date", type="string", format="date", example="2023-09-01"),
     *                 @OA\Property(property="recording", type="string", example="https://example.com/recording.mp4"),
     *                 @OA\Property(property="trial_date", type="string", format="date", example="2023-09-01"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-09-01T10:00:00Z"),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", format="float", example=100.00),
     *                 @OA\Property(property="notes", type="string", example="Add lace to the sleeves")
     *             ) 
     *     )
     *  ), 
     *  @OA\Response(
     *         response=404,
     *         description="Dress not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Dress not found")            
     *         )
     *     )
     * )
     *)
     */
    public function getDetails($id)
    {
        $dress = Dress::find($id);

        if (empty($dress)) {
            return response()->json(['success' => false, 'message' => 'Dress not found'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Basic details for the dress', 
            'data' => [
                'dress_Id' => $dress->id,
                'created_at' => $dress->created_at->toIso8601ZuluString(),
                'delivery_date' => $dress->delivery_date->toIso8601ZuluString(),
                'trial_date' => $dress->trial_date ? $dress->trial_date->toIso8601ZuluString() : null,
                'quantity' => $dress->quantity,
                'price' => $dress->price,
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/dress/{id}/details",
     *     summary="Update basic details for a dress",
     *     description="Allows a tailor to update basic details for a dress based on the dress ID.",
     *     operationId="updateBasicDetails",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,        
     *         @OA\Schema(type="integer"),
     *         description="Dress ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",                
     *                 @OA\Property(property="delivery_date", type="string", format="date", example="2023-09-01"),
     *                 @OA\Property(property="trial_date", type="string", format="date", example="2023-09-01"),  
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", example=100),
     *                @OA\Property(property="notes", type="string", example="Add lace to the sleeves"),
     *                @OA\Property(property="recording", type="string", example="https://example.com/recording.mp4")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dress updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Dress updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dress not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Dress not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="delivery_date", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="trial_date", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="quantity", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function updateDetails(Request $request, $id)
    {

        $dress = Dress::findOrFail($id);

        $rules = [
            'delivery_date' => 'nullable|date',
            //'trial_date' => 'nullable|date',
            'quantity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('delivery_date') && !empty($request->input('delivery_date'))) {
            $dress->delivery_date = $request->input('delivery_date');
        }

        if ($request->has('trial_date')) {
            $dress->trial_date = $request->input('trial_date');
        }

        if ($request->has('quantity') && !empty($request->input('quantity'))) {
            $dress->quantity = $request->input('quantity');
        }

        if ($request->has('price') && !empty($request->input('price'))) {
            $dress->price = $request->input('price');
        }

        $dress->save();

        return response()->json([
            'message' => 'Dress updated successfully',
        ]);
    }

}
