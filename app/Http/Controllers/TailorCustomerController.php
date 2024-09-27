<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCustomer;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TailorCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

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
     *         description="The page number for pagination"
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Number of customers to retrieve per page"
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
                $tailorcustomers = TailorCustomer::where('tailor_id', $tailor_id)->get();
            } else {
                $tailorcustomers = TailorCustomer::where('tailor_id', $tailor_id)->forpage($page, $perpage)->get();
            }

            if (count($tailorcustomers) === 0) {
                return response()->json(['success' => false, 'message' => 'No Customer Found', 'data' => ['tailor_id' => $tailor_id]], 404);
            } else {
                return response()->json(['success' => true, 'message' => 'Customers Found', 'data' => ['tailor_id' => $tailor_id, 'customers' => $tailorcustomers]], 200);
            }
        }
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
    public function getCustomerById($customer_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $customer = TailorCustomer::where([['customer_id', $customer_id], ['tailor_id', $tailor_id]])->first();
        if (empty($customer)) {
            return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => ['customer' => $customer]], 200);
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
                    $customer = TailorCustomer::where('tailor_id', $tailor_id)->get();
                } else {
                    $customer = TailorCustomer::where('tailor_id', $tailor_id)->forpage($page, $perpage)->get();
                }
                if (count($customer) === 0) {
                    return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 404);
                } else {
                    return response()->json(['success' => true, 'message' => 'Customer Found', 'data' => ['customer' => $customer]], 200);
                }
            } else {
                if (empty($page) or empty($perpage)) {
                    $customer = TailorCustomer::where([['tailor_id', $tailor_id], ['number', 'LIKE', '%' . $text . '%']])->orwhere([['tailor_id', $tailor_id], ['name', 'LIKE', '%' . $text . '%']])->get();
                } else {
                    $customer = TailorCustomer::where([['tailor_id', $tailor_id], ['number', 'LIKE', '%' . $text . '%']])->orwhere([['tailor_id', $tailor_id], ['name', 'LIKE', '%' . $text . '%']])->forpage($page, $perpage)->get();
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
            'city_name' => '',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        }

        $tailor_id = auth('sanctum')->user()->id;
        $tailorcustomer = TailorCustomer::where([['number', $request->number], ['tailor_id', $tailor_id]])->first();
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
    public function update(Request $request)
    {
        $rules = [
            'customer_id' => 'required',
            'number' => 'required|max:12',
            'name' => '',
            'address' => 'max:70',
            'gender' => '',
            'picture' => '',
            'city_name' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailorcustomer = TailorCustomer::where([['customer_id', $request->customer_id], ['tailor_id', $tailor_id]])->first();
            if (empty($tailorcustomer)) {
                return response()->json(['success' => false, 'message' => 'Customer does not exist.', 'data' => []], 200);
            } else {
                $tailorcustomer->number = $request->number;
                $tailorcustomer->name = $request->name;
                $tailorcustomer->address = $request->address;
                $tailorcustomer->gender = $request->gender;
                $tailorcustomer->picture = $request->picture;
                $tailorcustomer->city_name = $request->city_name;

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
