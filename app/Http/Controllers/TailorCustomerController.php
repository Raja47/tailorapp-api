<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCustomer;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TailorCustomerController extends Controller
{

    // swagger annotation
    /**
     * @OA\Get(
     *     path="/tailors/customers",
     *     summary="Retrieve a paginated list of customers for the authenticated tailor",
     *     tags={"Customers"},
     *     security={{"bearerAuth": {}}},
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
     *         description="Number of customers to retrieve per page",
     *         example=10
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customers retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customers Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="tailor_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="customers",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                         @OA\Property(property="phone", type="string", example="1234567890")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer data validation error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Validation error details"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No customers found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No Customer Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="tailor_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $rules = [
            'shop_id' => 'required',
            'page' => 'required',
            'perpage' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $page = $request->input('page');
            $perpage = $request->input('perpage');

            if (empty($page) or empty($perpage)) {
                $tailorcustomers = TailorCustomer::where(['tailor_id' => $tailor_id , 'shop_id' => $request->shop_id ])->get();
            } else {
                $tailorcustomers = TailorCustomer::where(['tailor_id' => $tailor_id , 'shop_id' => $request->shop_id ])->forpage($page, $perpage)->get();
            }
            
            return response()->json(['success' => true, 'message' => 'Customers Found', 'data' => ['tailor_id' => $tailor_id, 'customers' => $tailorcustomers]], 200);
        }
    }

    /**
     * @OA\Get(
     *     path="/tailors/customers/{customer_id}/orders",
     *     summary="Get orders for a specific customer by the authenticated tailor",
     *     description="Returns a list of orders placed by the specified customer for the currently authenticated tailor",
     *     operationId="getCustomerOrders",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orders Found"),
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
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No Orders Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No Orders Found"),
     *             @OA\Property(property="data", type="string", example="")
     *         )
     *     )
     * )
     */
    public function orders($customer_id, $page = 1 , $perpage = 20)
    {
        $query = DB::table('orders')
            ->select('orders.id', 'orders.name','tailor_customers.name as customer_name', 'orders.status', 'orders.created_at', 'orders.updated_at','orders.total_dress_amount', 'orders.total_payment', 'orders.total_expenses' , 'orders.total_discount' , DB::raw('SUM(dresses.quantity) as dress_count'))
            ->leftjoin('tailor_customers' , 'orders.customer_id','=','tailor_customers.id')
            ->leftjoin('dresses', 'orders.id', '=', 'dresses.order_id')
            ->where('orders.customer_id', $customer_id)
            ->groupBy('orders.id');

        $orders = $query
            ->orderBy('orders.updated_at', 'desc')
            ->get()->map(function ($order) {
                $order->updated_at = Carbon::parse($order->updated_at)->toIso8601ZuluString();
                $order->dress_count = (int) $order->dress_count;
                return $order;
            });

         response()->json(['success' => true, 'message' => 'Orders Found', 'data' => $orders], 200);        
    }

    /**
     * @OA\Get(
     *     path="/tailors/customers/{customer_id}/payments",
     *     summary="Get payments for a specific customer by the authenticated tailor",
     *     description="Returns a list of payments made by the specified customer to the currently authenticated tailor",
     *     operationId="getCustomerPayments",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payments Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payments Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No Payments Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No Payments Found"),
     *             @OA\Property(property="data", type="string", example="")
     *         )
     *     )
     * )
     */
    public function payments($customer_id, $page=1 , $perpage = 20)
    {
        $tailor_id = auth('sanctum')->user()->id;
         $query = DB::table('payments')
            ->select('payments.id', 'orders.name AS order_name', 'tailor_customers.name AS customer_name', 'payments.amount', 'payments.method', 'payments.created_at')
            ->leftjoin('orders', 'orders.id', 'payments.order_id')
            ->leftjoin('tailor_customers', 'tailor_customers.id', 'payments.customer_id')
            ->where('payments.customer_id', $customer_id);
        
        $payments = $query->orderBy('payments.created_at', 'desc')
            ->forpage($page, $perpage)
            ->get()
            ->map(function ($payment) {
                $payment->created_at = Carbon::parse($payment->created_at)->toIso8601ZuluString();
                return $payment;
        });

        return response()->json(['success' => true, 'message' => 'Payments Found', 'data' => $payments], 200);
        
    }

    //count of customers for specific tailor
    public function countCustomers()
    {
        $tailor_id = auth('sanctum')->user()->id;
        $countCustomers = TailorCustomer::where('tailor_id', $tailor_id)->count();
        return response()->json(['success' => true, 'message' => 'Customer Count', 'data' => ['tailor_id' => $tailor_id, 'countCustomer' => $countCustomers]], 200);
    }

    //param: phone number in request
    //get customer by phone number
    public function getCustomer(Request $request)
    {
        $rules = [
            'number' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $customer = TailorCustomer::where([['number', $request->number], ['tailor_id', $tailor_id]])->first();
            if (empty($customer)) {
                return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 200);
            } else {
                return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => ['customer' => $customer]], 200);
            }
        }
    }

    //param: customer_id in request
    //get customer by Id

    /**
     * @OA\Get(
     *     path="/tailors/customers/{customer_id}",
     *     tags={"Customers"},
     *     summary="Show customer by ID",
     *     description="Retrieve details of a customer by their ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to retrieve.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="customer_id", type="integer", example=1),
     *                     @OA\Property(property="number", type="string", example="1234567890"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="address", type="string", example="123 Main Street"),
     *                     @OA\Property(property="gender", type="string", example="Male"),
     *                     @OA\Property(property="city_name", type="string", example="Karachi"),
     *                     @OA\Property(property="picture", type="string", example="storage/customers/filename.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer Not Found"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getCustomerById($customer_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $customer = TailorCustomer::where([['id', $customer_id], ['tailor_id', $tailor_id]])->first();
        if (empty($customer)) {
            return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 404);
        } else {
            return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => [$customer]], 200);
        }
    }

    //swagger annotaions
    /**
     * @OA\Get(
     *     path="/tailors/customers/search",
     *     summary="Search for customers by name or number",
     *     tags={"Customers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         required=false,
     *         description="Text to search for in customer name or number",
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         in="query",
     *         required=true,
     *         description="Number of results per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer(s) found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer Found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="customer",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="number", type="string", example="123456789")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No customer found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer Not Found"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer data validation error"),
     *             @OA\Property(property="data", type="object", description="Validation error details")
     *         )
     *     )
     * )
     */

    public function search(Request $request)
    {
        $rules = [
            'searchText' => '',
            'page' => 'required',
            'perpage' => 'required'
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $text = $request->input('searchText');
            $page = $request->input('page');
            $perpage = $request->input('perpage');

            if (empty($text)) {
                if (empty($page) or empty($perpage)) {
                    $customer = TailorCustomer::where('tailor_id', $tailor_id)->orderBy('created_at', 'desc')->get();
                } else {
                    $customer = TailorCustomer::where('tailor_id', $tailor_id)->forpage($page, $perpage)->orderBy('created_at', 'desc')->get();
                }
                if (count($customer) === 0) {
                    return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 404);
                } else {
                    return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => ['customer' => $customer]], 200);
                }
            } else {
                if (empty($page) or empty($perpage)) {
                    $customer = TailorCustomer::where([['tailor_id', $tailor_id], ['number', 'LIKE', '%' . $text . '%']])->orwhere([['tailor_id', $tailor_id], ['name', 'LIKE', '%' . $text . '%']])->orderBy('created_at', 'desc')->get();
                } else {
                    $customer = TailorCustomer::where([['tailor_id', $tailor_id], ['number', 'LIKE', '%' . $text . '%']])->orwhere([['tailor_id', $tailor_id], ['name', 'LIKE', '%' . $text . '%']])->forpage($page, $perpage)->orderBy('created_at', 'desc')->get();
                }
                if (count($customer) === 0) {
                    return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 404);
                } else {
                    return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => ['customer' => $customer]], 200);
                }
            }
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
     *     path="/tailors/customers/store",
     *     summary="Create a new Tailor Customer",
     *     description="Store a new customer associated with a tailor. If a customer already exists for the tailor, an error is returned.",
     *     tags={"Customers"},
     *     security={{"bearerAuth": {}}},
     *     
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             type="object",
     *             required={"number", "name", "gender"},
     *             @OA\Property(property="number", type="string", description="Customer phone number, unique.", example="1234567890"),
     *             @OA\Property(property="name", type="string", description="Customer name.", example="John Doe"),
     *             @OA\Property(property="address", type="string", description="Customer address, optional.", example="123 Main Street"),
     *             @OA\Property(property="gender", type="string", description="Customer gender.", example="Male"),
     *             @OA\Property(property="city_name", type="string", description="Customer city name, optional.", example="Karachi"),
     *             @OA\Property(property="picture", type="string", format="binary", description="Picture of the customer (image upload).")
     *         )
     *     )
     * ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Customer Created Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Your Customer Created Successfully"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="Tailor Customer id", type="integer", description="ID of the created customer")
     *             ),
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Customer Creation Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer Creation Failed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Customer Already Exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer Already Exists"),
     *             @OA\Property(property="data", type="integer", description="ID of the existing customer")
     *         )
     *     ),
     * )
     */

    public function store(Request $request)
    {
        $rules = [
            'number' => 'required|max:12',
            'name' => 'required',
            'address' => 'max:70',
            'gender' => 'required',
            'shop_id' => 'required',
            'city_name' => '',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        }

        $tailor_id = auth('sanctum')->user()->id;
        $tailorcustomer = TailorCustomer::where([['number', $request->number], ['tailor_id', $tailor_id , 'shop_id' => $request->shop_id ]])->first();
        if (!empty($tailorcustomer)) {
            return response()->json(['success' => false, 'message' => 'Customer Already Exists', 'data' => $tailorcustomer->id], 400);
        }

        $path = null;
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/customers', $filename);
            $base_url = url('');
            $path = $base_url . '/storage/customers/' . $filename;
        }

        $customer = Customer::where('number', $request->number)->first();
        if (empty($customer)) {
            $customer = Customer::create([
                'number' => $request->number,
                'name' => $request->name,
                'address' => $request->address,
                'gender' => $request->gender,
                'city_name' => $request->city_name,
            ]);
        }

        $tailorcustomer = TailorCustomer::create([
            'number' => $request->number,
            'name' => $request->name,
            'address' => $request->address,
            'gender' => $request->gender,
            'picture' => $path,
            'city_name' => $request->city_name,
            'tailor_id' => $tailor_id,
            'shop_id' => $request->shop_id,
            'customer_id' => $customer->id,
        ]);

        if ($tailorcustomer->save()) {
            return response()->json(['success' => true, 'message' => 'Your Customer Created Successfully', 'data' => ['Customer' => $tailorcustomer]], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Customer Creation Failed', 'data' => []], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     *     path="/tailors/customers/update",
     *     tags={"Customers"},
     *     summary="Update a customer",
     *     description="Update an existing customer's details",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"customer_id", "number", "name", "gender"},
     *                 @OA\Property(
     *                     property="customer_id",
     *                     type="integer",
     *                     description="ID of the customer to be updated.",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="number",
     *                     type="string",
     *                     description="Customer phone number.",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Customer name.",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string",
     *                     description="Customer address, optional.",
     *                     example="123 Main Street"
     *                 ),
     *                 @OA\Property(
     *                     property="gender",
     *                     type="string",
     *                     description="Customer gender.",
     *                     example="Male"
     *                 ),
     *                 @OA\Property(
     *                     property="city_name",
     *                     type="string",
     *                     description="Customer city name, optional.",
     *                     example="Karachi"
     *                 ),
     *                 @OA\Property(
     *                     property="picture",
     *                     type="string",
     *                     format="binary",
     *                     description="Picture of the customer (image upload)."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Your Customer Updated Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer Updation Failed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer does not exist."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        $rules = [
            'customer_id' => 'required',
            'number' => 'max:12',
            'name' => 'required',
            'address' => 'max:70',
            'gender' => 'required',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'city_name' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;

            $path = null;
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/customers', $filename);

                $base_url = url('');
                $path = $base_url . '/storage/customers/' . $filename;
            }

            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id], ['tailor_id', $tailor_id]])->first();
            if (empty($tailorcustomer)) {
                return response()->json(['success' => false, 'message' => 'Customer does not exist.', 'data' => []], 404);
            } else {
                $tailorcustomer->name = $request->name;
                $tailorcustomer->address = $request->address;
                $tailorcustomer->gender = $request->gender;
                $tailorcustomer->city_name = $request->city_name;

                if ($request->picture != null) {
                    $tailorcustomer->picture = $path;
                }
                if ($tailorcustomer->save()) {
                    return response()->json(['success' => true, 'message' => 'Your Customer Updated Successfully', 'data' => ['id' => $tailorcustomer->id]], 200);
                } else {
                    return response()->json(['success' => false, 'message' => 'Customer Updation Failed', 'data' => []], 500);
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

    // swagger annotaion

    /**
     * @OA\Post(
     *     path="/tailors/customers/destroy",
     *     summary="Delete a customer for the authenticated tailor",
     *     tags={"Customers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_id", type="integer", example=1, description="The ID of the customer to delete")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer Deleted successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer does not exist."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer Validation Error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Validation error details"
     *             )
     *         )
     *     )
     * )
     */

    public function destroy(Request $request)
    {
        $rules = [
            'customer_id' => 'required|numeric',
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer Validation Error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id], ['tailor_id', $tailor_id]])->first();
            if (empty($tailorcustomer)) {
                return response()->json(['success' => false, 'message' => 'Customer does not exist.', 'data' => []], 404);
            } else {
                $tailorcustomer->delete();
                return response()->json(['success' => true, 'message' => 'Customer Deleted successfully', 'data' => []], 200);
            }
        }
    }
}
