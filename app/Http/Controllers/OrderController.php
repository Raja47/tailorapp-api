<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

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
        $page = $request->input('page');
        $perpage = $request->input('perpage');

        $orders = Order::where('tailor_id', $tailor_id)
            ->where(function ($query) {
                $query->where('status', 0)
                    ->orwhere('status', 1);
            })
            ->forpage($page, $perpage)
            ->orderBy('created_at', 'desc')
            ->get();

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
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'tailor_id' => $tailor_id,
                'shop_id' => $request->shop_id,
                'name' => 'order-1',
                'status' => 0,
            ]);

            if ($order->save()) {
                return response()->json(['success' => true, 'message' => 'Order Created Successfully', 'data' => ['id' => $order->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Order Creation Failed', 'data' => []], 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/tailors/orders/tab",
     *     summary="Fetch orders based on tab selection",
     *     description="Retrieves orders based on the specified tab. Possible tab values: all, new, inProgress, completed, delivered, canceled.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     * 
     *     @OA\Parameter(
     *         name="tabName",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"all", "new", "inProgress", "completed", "delivered", "canceled"}),
     *         description="The tab name to filter orders"
     *     ),
     * 
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
            'tabName' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Order data validation error', 'data' => $validation->errors()], 422);
        } else {

        $tailor_id = auth('sanctum')->user()->id;
            $tabName = $request->input('tabName');
            $tailor_orders = collect([]);

            switch ($tabName) {
                case 'all':
        $tailor_orders = Order::where('tailor_id', $tailor_id)->get();
                    break;

                case 'new':
                    $tailor_orders = Order::where([['tailor_id', $tailor_id], ['status', 0]])->get();
                    break;

                case 'inProgress':
                    $tailor_orders = Order::where([['tailor_id', $tailor_id], ['status', 1]])->get();
                    break;

                case 'completed':
                    $tailor_orders = Order::where([['tailor_id', $tailor_id], ['status', 2]])->get();
                    break;

                case 'delivered':
                    $tailor_orders = Order::where([['tailor_id', $tailor_id], ['status', 3]])->get();
                    break;

                case 'canceled';
                    $tailor_orders = Order::where([['tailor_id', $tailor_id], ['status', 4]])->get();
                    break;
            }

        if (count($tailor_orders) === 0) {
            return response()->json(['success' => false, 'message' => 'No Order Found'], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Orders Found', 'data' => ['Orders' => $tailor_orders]], 200);
            }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $order = Order::where([['id', $order_id], ['tailor_id', $tailor_id]])->first();
        if (empty($order)) {
            return response()->json(['success' => false, 'message' => 'Order Not Found'], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Order Found', 'data' => ['Order' => $order]], 200);
        }
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
