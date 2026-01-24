<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/tailors/payments/",
     *     summary="Get all payments for a tailor",
     *     description="Retrieves all payments associated with the authenticated tailor.",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="timeFilter",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"all","today", "yesterday", "thisweek", "lastweek", "thismonth", "lastmonth", "thisyear", "lastyear"}),
     *         description="The time to filter orders"
     *     ),
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Text to search orders",
     *         example="cash"
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
     *         description="Tailor payments retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tailor Payments Found"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $rules = [
            'timeFilter' => 'required',
            'page' => 'required',
            'perpage' => 'required',
            'searchText' => 'nullable'
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Payments data validation error', 'data' => $validation->errors()], 422);
        }

        $tailor_id = auth('sanctum')->user()->id;
        $timeFilter = $request->input('timeFilter');
        $searchText = $request->input('searchText');
        $page = $request->input('page');
        $perpage = $request->input('perpage');
        $today = Carbon::today();

        $query = DB::table('payments')
            ->select('payments.*', 'orders.name AS order_name','tailor_customers.name AS customer_name')
            ->leftjoin('orders', 'orders.id', 'payments.order_id')
            ->leftjoin('tailor_customers', 'tailor_customers.id', 'payments.customer_id')
            ->where('payments.tailor_id', $tailor_id);

        switch ($timeFilter) {
            case 'all':
                break;

            case 'today':
                $query->whereDate('payments.created_at', $today);
                break;

            case 'yesterday':
                $query->where('payments.created_at', Carbon::now()->subDays(1));
                break;

            case 'thisweek':
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('payments.created_at', [$startOfWeek, $endOfWeek]);
                break;

            case 'lastweek':
                $last_week_start = Carbon::now()->startOfWeek()->subDays(7);
                $last_week_end = Carbon::now()->endOfWeek()->subDays(7);
                $query->whereDate('payments.created_at', '>=', $last_week_start)->whereDate('payments.created_at', '<=', $last_week_end);
                break;

            case 'thismonth':
                $query->whereMonth('payments.created_at', $today->month)->whereYear('payments.created_at', $today->year);
                break;

            case 'lastmonth':
                $last_month = Carbon::today()->subMonth();
                $query->whereMonth('payments.created_at', $last_month->month)->whereYear('payments.created_at', $last_month->year);
                break;

            case 'thisyear':
                $query->whereYear('payments.created_at', $today->year);
                break;

            case 'lastyear':
                $last_year = Carbon::today()->subYear();
                $query->whereYear('payments.created_at', $last_year->year);
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid time filter'], 500);
        }

        if ($request->filled('searchText')) {
            $query->where('customers.name', 'like', '%' . $searchText . '%')
                ->orWhere('payments.method', 'like', '%' . $searchText . '%')
                ->orWhere('orders.name', 'like', '%' . $searchText . '%');
        }

        $totalAmount = (Float) (clone $query)->sum('payments.amount');

        $tailor_payments = $query->orderBy('payments.created_at', 'desc')
            ->forpage($page, $perpage)
            ->get()
            ->map(function ($payment) {
                $payment->created_at = Carbon::parse($payment->created_at)->toIso8601ZuluString();
                return $payment;
            });;

        if (count($tailor_payments) === 0) {
            return response()->json(['success' => false, 'message' => 'No Payments Found', 'data' => []], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Tailor Payments Found', 'total' => $totalAmount, 'data' => $tailor_payments], 200);
        }
    }


    /**
     * @OA\Get(
     *     path="/tailors/payments/orders/{order_id}",
     *     summary="Get payments for an order",
     *     description="Retrieves all payments associated with a specific order for the authenticated tailor.",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID of the order",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order payments retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Payments Found"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Invalid Order ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid Order ID")
     *         )
     *     )
     * )
     */
    public function orderPayments($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $order = Order::where([['id', $order_id], ['tailor_id', $tailor_id]])->first();
        
        if (!$order) {
            return response()->json(['success' => true, 'message' => 'Invalid Order ID'], 500);
        }

        $order_payments = Payment::where('order_id', $order_id)->get();
        
        return response()->json(['success' => true, 'message' => 'Order Payments Found', 'data' => ['payments' => $order_payments]], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     *     path="/tailors/payments/orders",
     *     summary="Add a new payment",
     *     description="Creates a new payment record for an order.",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "date", "order_id", "customer_id"},
     *             @OA\Property(property="title", type="string", example="Advance Payment"),
     *             @OA\Property(property="method", type="string", example="Bank Transfer"),
     *             @OA\Property(property="amount", type="number", format="float", example=5000),
     *             @OA\Property(property="date", type="string", format="date", example="2025-04-01"),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="customer_id", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment Added Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Payment data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Payment creation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Payment cannot be added")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'string',
            'method' => 'string',
            'amount' => 'required',
            'date' => 'required',
            'order_id' => 'required',
            'customer_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Payment data validation error', 'data' => $validation->errors()], 422);
        }

        $tailor_id = auth('sanctum')->user()->id;

        $payment = Payment::create([
            'title' => $request->title,
            'method' => $request->method,
            'amount' => $request->amount,
            'date' => $request->date,
            'order_id' => $request->order_id,
            'tailor_id' => $tailor_id,
            'customer_id' => $request->customer_id,
        ]);

        if ($payment->save()) {
            $payment_order = $payment->order;
            $payment_order->increment('total_payment', $request->amount);
            return response()->json(['success' => true, 'message' => 'Payment Added Successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Payment cannot be added'], 500);
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     *     path="/tailors/payments/{payment_id}/destroy",
     *     summary="Delete a payment",
     *     description="Deletes a payment by its ID.",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="payment_id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment to delete",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found or already deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Payment already deleted")
     *         )
     *     )
     * )
     */
    public function destroy($payment_id)
    {
        $payment = Payment::find($payment_id);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment already deleted'], 200);
        }

        $payment->delete();
        return response()->json(['success' => true, 'message' => 'Payment deleted successfully'], 200);
    }
}
