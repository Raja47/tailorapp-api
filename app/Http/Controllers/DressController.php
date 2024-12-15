<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Dress;
use App\Models\Order;
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
     *     security={{"bearerAuth":{}}},
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
     *             @OA\Property(property="measurementBoxes", type="array", @OA\Items(type="object", @OA\Property(property="parameter_id", type="integer"), @OA\Property(property="value", type="number", format="float"))),
     *             @OA\Property(property="questionAnswers", type="array", @OA\Items(type="object", @OA\Property(property="question_id", type="integer"), @OA\Property(property="value", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string"), @OA\Property(property="value", type="string"), @OA\Property(property="icon", type="string", format="uri")))))
     *          )    
     *      ),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Dress created"), @OA\Property(property="data", type="object", @OA\Property(property="order_id", type="integer"), @OA\Property(property="dress_id", type="integer"), @OA\Property(property="measurement_id", type="integer")))),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="message", type="string", example="Validation error"), @OA\Property(property="errors", type="object", additionalProperties={"type": "array", "items": {"type": "string"}}))),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object", @OA\Property(property="success", type="boolean", example=false), @OA\Property(property="message", type="string", example="Server error"), @OA\Property(property="error", type="string")))
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
                TailorCategoryAnswer::create([
                    'tailor_id' => $tailor_id,
                    'dress_id' => $dress->id,
                    'question_id' => $questionAnswer['question_id'],
                    'value' => json_encode($questionAnswer['value']),
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

    public function getTabDresses(Request $request)
    {
        $rules = [
            'shop_id' => 'required',
            'tabName' => 'required',
            'search' => '',
        ];
        $now = Carbon::now();
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $shop_id = $request->shop_id;
            $tabName = $request->tabName;
            $search = $request->search;
            $query = DB::table('dresses')
                ->select('dresses.*', 'categories.name AS catName', 'pictures.path AS picture', 'customers.name AS customername')
                ->leftjoin('categories', 'categories.id', '=', 'dresses.category_id')
                ->leftjoin('orders', 'orders.id', '=', 'dresses.order_id')
                ->leftjoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftjoin('pictures', function ($join) {
                    $join->on('pictures.model_id', '=', 'dresses.id');
                    $join->where('pictures.model', '=', 'dress');
                });

            switch ($tabName) {
                case 'new':
                    if (empty($search)) {
                        $dresses = $query->where('dresses.status', '=', 0)->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->where('dresses.status', '=', 0)
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'urgent':
                    if (empty($search)) {
                        $dresses = $query->where('dresses.is_urgent', '=', 0)->whereNotIn('dresses.status', [2, 3, 4, 5])->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->where('dresses.is_urgent', '=', 0)
                            ->whereNotIn('dresses.status', [2, 3, 4, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'dueDresses':
                    $due_time_list = [$now->format('y-m-d'), $now->copy()->addDays(1)->format('y-m-d'), $now->copy()->addDays(2)->format('y-m-d')];
                    if (empty($search)) {
                        $dresses = $query->whereIn('dresses.delivery_date', $due_time_list)->whereNotIn('dresses.status', [2, 3, 4, 5])->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->whereIn('dresses.delivery_date', $due_time_list)
                            ->whereNotIn('dresses.status', [2, 3, 4, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'lateDresses':
                    $currentDate = Carbon::now();
                    if (empty($search)) {
                        $dresses = $query->whereNotIn('dresses.status', [1, 2, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where('dresses.delivery_date', '<', $currentDate)
                            ->get();
                    } else {
                        $dresses = $query->whereNotIn('dresses.status', [1, 2, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where('delivery_date', '<', $currentDate)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'inProgress':
                    if (empty($search)) {
                        $dresses = $query->where('dresses.status', 1)->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->where('dresses.status', 1)
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'completed/delivered':
                    if (empty($search)) {
                        $dresses = $query->whereIn('dresses.status', [2, 3])->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->whereIn('dresses.status', [2, 3])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;
            }

            return $dresses;
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

    public function getOrderDresses($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $query = DB::table('dresses')
            ->select('dresses.*', 'categories.name AS catName', 'pictures.path AS picture')
            ->leftjoin('categories', 'categories.id', '=', 'dresses.category_id')
            ->leftjoin('pictures', function ($join) {
                $join->on('pictures.model_id', '=', 'dresses.id');
                $join->where('pictures.model', '=', 'dress');
            });
        $dresses = $query->where('tailor_id', $tailor_id)->where('order_id', $order_id)->get();
        return $dresses;
    }

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
            $dress->status = $request->status_id;

            if ($dress->save()) {
                return response()->json(['success' => true, 'message' => 'Dress Status Updated Successfully', 'data' => ['Dress id' => $request->dress_id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress Status could bot be updated'], 500);
            }
        }
    }
}
