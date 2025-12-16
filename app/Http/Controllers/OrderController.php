<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Dress;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/tailors/orders/updatestatus",
     *     summary="Update the status of an order",
     *     description="Allows a tailor to update the status of an order based on order ID.",
     *     operationId="updateOrderStatus",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "status"},
     *             @OA\Property(property="order_id", type="integer", example=123, description="ID of the order to update"),
     *             @OA\Property(property="status", type="integer", example=2, description="New status of the order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Status Updated"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=12, description="Updated order ID")
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
     *             @OA\Property(property="data", type="object", example={"order_id": {"The order_id field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Status not updated"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request)
    {
        $rules = [
            'order_id' => 'required',
            'status' => 'required'
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Order data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $order = Order::where([['id', $request->order_id], ['tailor_id', $tailor_id]])->first();
            $order->status = $request->status;
            if ($order->save()) {
                return response()->json(['success' => true, 'message' => 'Order Status Updated', 'data' => ['id' => $order->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Status not updated', 'data' => []], 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/tailors/orders/recent",
     *     summary="Retrieve paginated recent orders for a tailor",
     *     description="Fetches paginated recent orders for the authenticated tailor with statuses 0 or 1, sorted by creation date in descending order.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         in="query",
     *         required=false,
     *         description="Number of orders per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of paginated recent orders",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="Order ID", example=1),
     *                     @OA\Property(property="customer_id", type="integer", description="Customer ID", example=2),
     *                     @OA\Property(property="tailor_id", type="integer", description="Tailor ID", example=1),
     *                     @OA\Property(property="shop_id", type="integer", description="Shop ID", example=3),
     *                     @OA\Property(property="name", type="string", description="Order name", example="Order-1"),
     *                     @OA\Property(property="status", type="integer", description="Order status", example=0),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Order creation date", example="2023-10-20T15:30:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Order update date", example="2023-10-21T10:20:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No orders to show",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No orders to show")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */


    public function recentOrders(Request $request)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $page = $request->input('page',1);
        $perpage = $request->input('perpage',10);

        $query = DB::table('orders')
        ->select('orders.id', 'orders.name', 'orders.status','orders.created_at', 'orders.updated_at', 'orders.total_dress_amount','tailor_customers.name as customer_name', DB::raw('COUNT(dresses.id) as dress_count'))
        ->leftjoin('dresses', 'orders.id', '=', 'dresses.order_id')
        ->leftjoin('tailor_customers', 'orders.customer_id','=','tailor_customers.id')
        ->where('orders.tailor_id', $tailor_id)
        ->whereIn('orders.status', [0, 1])
        ->groupBy('orders.id', 'orders.name', 'orders.status', 'orders.updated_at', 'orders.total_dress_amount', 'tailor_customers.name');

        $orders = $query
        ->forpage($page, $perpage)
        ->orderBy('orders.status', 'asc') 
        ->orderBy('orders.updated_at', 'desc')
        ->get()
        ->transform(function ($order) {
            $order->created_at = Carbon::parse($order->created_at)->toIso8601ZuluString();
            return $order;
        });

        if (count($orders) === 0) {
            return response()->json(['success' => false, 'message' => 'No orders to show'], 404);
        } else {
            return response()->json(['success' => true, 'data' => $orders], 200);
        }
    }

    public function countOrders()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $tailor_orders = Order::where([['tailor_id', $tailor_id], ['status', 1]])->get();
        $order_count = count($tailor_orders);
        return response()->json(['success' => true, 'data' => ['Orders' => $order_count]], 200);
    }
    //ambigous
    public function getCustomerByOrderid($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $order = Order::where([['tailor_id', $tailor_id], ['id', $order_id]])->first();
        $customer_id = $order->customer_id;
        $customer = Customer::where('id', $customer_id)->first();
        if (empty($customer)) {
            return response()->json(['success' => false, 'message' => 'Customer Not Found'], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => ['Order' => $order_id, 'Customer' => $customer]], 200);
        }
    }

    //swagger annotations
    /**
     * @OA\Post(
     *     path="/tailors/orders/store",
     *     summary="Create an empty order",
     *     description="Creates an empty order for a specific customer",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"customer_id", "shop_id"},
     *             @OA\Property(property="customer_id", type="integer", description="Customer ID", example=1),
     *             @OA\Property(property="name", type="string", description="Order name", example="Custom Order"),
     *             @OA\Property(property="shop_id", type="integer", description="Shop ID", example=1),
     *             @OA\Property(property="status", type="integer", description="Order status", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order Created Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Created Successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Order data validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Order Creation Failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order Creation Failed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */

    public function emptyOrder(Request $request)
    {
        $rules = [
            'customer_id' => 'required',
            'name' => '',
            'shop_id' => 'required',
            'status' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Order data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $shop_id = $request->shop_id;
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'tailor_id' => $tailor_id,
                'shop_id' => $shop_id,
                'name' => 'order-1',
                'status' => 0,
            ]);
            $order_id = $order->id;
            $order->name = 't' . $tailor_id . 's0' . $order_id;

            if ($order->save()) {
                return response()->json(['success' => true, 'message' => 'Order Created Successfully', 'data' => ['id' => $order_id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Order Creation Failed', 'data' => []], 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/tailors/orders/tab",
     *     summary="Fetch orders based on tab selection",
     *     description="Retrieves orders based on the specified tab.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     * 
     *     @OA\Parameter(
     *         name="timeFilter",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"all","today", "last15days", "thismonth", "lastmonth", "thisyear", "lastyear"}),
     *         description="The time to filter orders"
     *     ),
     *     @OA\Parameter(
     *         name="statusFilter",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1, 2, 3, 4}),
     *         description="The status to filter orders"
     *     ),
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Text to search orders",
     *         example="S02"
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
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orders Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="customer_name", type="string", example="John Doe"),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-27T12:34:56Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-27T14:00:00Z")
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
     *         description="Order Search Failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order Search Failed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */

    public function getTabOrders(Request $request)
    {
        $rules = [
            'timeFilter' => 'required|string',
            'statusFilter' => 'nullable|integer',
            'searchText' => 'nullable|string',
            'shop_id' => 'required|integer|exists:shops,id',
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
        $tailor_orders = collect([]);
        $today = Carbon::today();

        $query = DB::table('orders')
            ->select('orders.id', 'orders.name', 'orders.status', 'orders.created_at', 'orders.updated_at','orders.total_dress_amount', 'orders.total_payment', 'orders.total_expenses' , 'orders.total_discount' , DB::raw('SUM(dresses.quantity) as dress_count'))
            ->leftjoin('dresses', 'orders.id', '=', 'dresses.order_id')
            ->where([['orders.tailor_id', $tailor_id], ['orders.shop_id', $shop_id]])
            ->groupBy('orders.id');

        switch ($timeFilter) {
            case 'all':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter);
                }
                break;

            case 'today':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereDate('orders.created_at', $today);
                } else {
                    $query->whereDate('orders.created_at', $today);
                }
                break;

            case 'last15days':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->where('orders.created_at', '>=', Carbon::now()->subDays(15));
                } else {
                    $query->where('orders.created_at', '>=', Carbon::now()->subDays(15));
                }
                break;

            case 'thismonth':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereMonth('orders.created_at', $today->month)->whereYear('orders.created_at', $today->year);
                } else {
                    $query->whereMonth('orders.created_at', $today->month)->whereYear('orders.created_at', $today->year);
                }
                break;

            case 'lastmonth':
                $last_month = Carbon::today()->subMonth();
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereMonth('orders.created_at', $last_month->month)->whereYear('created_at', $last_month->year);
                } else {
                    $query->whereMonth('orders.created_at', $last_month->month)->whereYear('orders.created_at', $last_month->year);
                }
                break;

            case 'thisyear':
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereYear('orders.created_at', $today->year);
                } else {
                    $query->whereYear('orders.created_at', $today->year);
                }
                break;

            case 'lastyear':
                $last_year = Carbon::today()->subYear();
                if ($request->filled('statusFilter')) {
                    $query->where('orders.status', $statusFilter)->whereYear('orders.created_at', $last_year->year);
                } else {
                    $query->whereYear('orders.created_at', $last_year->year);
                }
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid time filter'], 500);
        }

        if ($request->filled('searchText')) {
            $query->where('orders.name', 'like', '%' . $searchText . '%');
        }
        $tailor_orders = $query->orderBy('orders.created_at', 'desc')->forpage($page, $perpage)->get()
            ->map(function ($order) {
                $order->created_at = Carbon::parse($order->created_at)->toIso8601ZuluString();
                $order->updated_at = Carbon::parse($order->updated_at)->toIso8601ZuluString();
                $order->dress_count = (int) $order->dress_count;
                return $order;
            });

        if (count($tailor_orders) === 0) {
            return response()->json(['success' => true, 'message' => 'No Orders Found', 'data' => ['Orders' => $tailor_orders]], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Orders Found', 'data' => ['Orders' => $tailor_orders]], 200);
        }
    }

    /**
     * @OA\Get(
     *     path="/tailors/orders/{order_id}/summary",
     *     summary="Get order amounts summary",
     *     description="Retrieves a summary of total dress amount, expenses, discounts, and payments for a given order.",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the order",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with order amount summary",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Amount Summary"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_dress_amount", type="integer", example=5000),
     *                 @OA\Property(property="total_expense_amount", type="integer", example=1500),
     *                 @OA\Property(
     *                     property="expense_summary",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Fabric Purchase"),
     *                         @OA\Property(property="amount", type="integer", example=1000)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_discount_amount", type="integer", example=500),
     *                 @OA\Property(
     *                     property="discount_summary",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Seasonal Discount"),
     *                         @OA\Property(property="amount", type="integer", example=200)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_payment_amount", type="integer", example=4000),
     *                 @OA\Property(
     *                     property="payment_summary",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="paymentmethod", type="string", example="Credit Card"),
     *                         @OA\Property(property="amount", type="integer", example=3000)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function orderAmounts($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $order = Order::where([['id', $order_id], ['tailor_id', $tailor_id]])->first();
        if (!$order) {
            return response()->json(['success' => true, 'message' => 'Invalid Order ID'], 200);
        }

        //dress_amounts
        $total_dress_amount = Dress::where('order_id', $order_id)->sum('price');

        //expense_amounts
        $total_expense_amount = Expense::where('order_id', $order_id)->sum('amount');
        $expense_summary = Expense::where('order_id', $order_id)->select('id','title', 'amount')->get();

        //discount_amounts
        $total_discount_amount = Discount::where('order_id', $order_id)->sum('amount');
        $discount_summary = Discount::where('order_id', $order_id)->select('id','title', 'amount')->get();

        //payment_amounts
        $total_payment_amount = Payment::where('order_id', $order_id)->sum('amount');
        $paymet_summary = Payment::where('order_id', $order_id)->select('id', 'title', 'method', 'amount')->get();

        $order->update([
            'total_dress_amount' => $total_dress_amount,
            'total_expenses' => $total_expense_amount,
            'total_discount' => $total_discount_amount,
            'total_payment' => $total_payment_amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order Amount Summary',
            'data' => [
                'total_dress_amount' => $total_dress_amount,
                'total_expense_amount' => $total_expense_amount,
                'expense_summary' => $expense_summary,
                'total_discount_amount' => $total_discount_amount,
                'discount_summary' => $discount_summary,
                'total_payment_amount' => $total_payment_amount,
                'payment_summary' => $paymet_summary
            ]
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

        /**
     * @OA\Get(
     *     path="/tailors/orders/{order_id}",
     *     summary="Get Order Detals",
     *     description="Retrieves a list of dresses associated with a specific order, based on the tailor's authentication.",
     *     operationId="Order Details",
     *     tags={"Orders"},
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
     *         description="Order Details retrieved successfully",
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
    public function show($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;

        $order = Order::where([['id', $order_id], ['tailor_id', $tailor_id]])->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Invalid Order ID'], 200);
        }

        $order_dresses = DB::table('dresses')
            ->select('dresses.*', 'tailor_categories.name AS catName', 'images.path AS image')
            ->leftjoin('tailor_categories', 'tailor_categories.id', '=', 'dresses.category_id')
            ->leftJoin(DB::raw('(
                SELECT di1.dress_id, di1.path
                    FROM dress_images di1
                    INNER JOIN (
                        SELECT dress_id, MIN(id) AS min_id
                        FROM dress_images
                        WHERE type = "design"
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
        }


        $dress_total = (Float) Dress::where('order_id', $order_id)->sum('price');

        //expense_amounts
        $expense_total = (Float) Expense::where('order_id', $order_id)->sum('amount');
        $expenses = Expense::where('order_id', $order_id)->select('title', 'amount')->get();

        //discount_amounts
        $discount_total = (Float) Discount::where('order_id', $order_id)->sum('amount');
        $discounts = Discount::where('order_id', $order_id)->select('title', 'amount')->get();

        //payment_amounts
        $payment_total = (Float) Payment::where('order_id', $order_id)->sum('amount');
        $payments = Payment::where('order_id', $order_id)->select('title', 'method', 'amount')->get();

        //@todo we dont need to update here the totals we should upate it while any transaction being added or deleted.
        $order->update([
            'total_dress_amount' => $dress_total,
            'total_expenses' => $expense_total,
            'total_discount' => $discount_total,
            'total_payment' => $payment_total,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order Found',
            'data' => [
                'order' => $order,
                'dresses' => $order_dresses,
                'dress_total' => $dress_total,
                'expenses' => $expenses,
                'expense_total' => $expense_total,
                'discounts' => $discounts,
                'discount_total' => $discount_total,
                'payments' => $payments,
                'payment_total' => $payment_total,
            ]
        ], 200);
    }

    public function countCustomerOrders(Request $request)
    {
        $validation = Validator::make($request->all(), ['customer_id' => 'required']);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Order data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $customer_orders = Order::where([['tailor_id', $tailor_id], ['customer_id', $request->customer_id]])->get();
            $order_count = count($customer_orders);
            return response()->json(['success' => true, 'data' => ['Customer Orders' => $order_count]], 200);
        }
    }

    public function getCustomerOrders(Request $request)
    {
        $validation = Validator::make($request->all(), ['customer_id' => 'required']);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Order data validation error', 'data' => $validation->errors()], 422);
        } else {
            $orders = Order::query()->leftJoin('emails', 'users.id', '=', 'emails.user_id')
                ->select('orders.*', 'customers.name as customername', 'dresses.order_id', 'dresses.price', 'dresses.quantity', '')
                ->where('orders.customer_id', 30)
                ->groupby('dresses.order_id')  //if ordering
                ->get();
        }
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
    public function destroy($id)
    {
        //
    }
}
