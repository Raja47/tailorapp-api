<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TailorCustomer;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class TailorCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $page = $request->input('page');
        $perpage = $request->input('perpage');

        if (empty($page) or empty($perpage)) {
            $tailorcustomers = TailorCustomer::where('tailor_id', $tailor_id)->get();
        } else {
            $tailorcustomers = TailorCustomer::where('tailor_id', $tailor_id)->forpage($page, $perpage)->get();
        }

        if (count($tailorcustomers) === 0) {
            return response()->json(['success' => false, 'message' => 'No Customer Found', 'data' => ['tailor_id' => $tailor_id]], 200);
        } else {
            return response()->json(['success' => true, 'message' => 'Customers Found', 'data' => ['tailor_id' => $tailor_id, 'customers' => $tailorcustomers]], 200);
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


    public function search(Request $request)
    {
        $page = $request->input('page');
        $perpage = $request->input('perpage');
        $rules = [
            'searchText' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $text = $request->searchText;
            if (empty($text)) {
                if (empty($page) or empty($perpage)) {
                    $customer = TailorCustomer::where('tailor_id', $tailor_id)->get();
                } else {
                    $customer = TailorCustomer::where('tailor_id', $tailor_id)->forpage($page, $perpage)->get();
                }
                if (count($customer) === 0) {
                    return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 200);
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
                    return response()->json(['success' => false, 'message' => 'Customer Not Found', 'data' => []], 200);
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
    public function store(Request $request)
    {
        $customer = Customer::where('number', $request->number)->first();

        if (empty($customer)) {
            $customer = Customer::create(['number' => $request->number]);
        }

        $rules = [
            'number' => 'required|max:12',
            'name' => '',
            'address' => 'max:70',
            'picture' => '',
            'gender' => '',
            'city_name' => '',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Customer data validation error', 'data' => $validation->errors()], 422);
        } else {
            $tailor_id = auth('sanctum')->user()->id;
            $tailorcustomer = TailorCustomer::where([['number', $request->number], ['tailor_id', $tailor_id]])->first();
            if (!empty($tailorcustomer)) {
                return response()->json(['success' => false, 'message' => 'Customer Already Exists', 'data' => $tailorcustomer->id], 200);
            } else {
                $tailorcustomer = TailorCustomer::create([
                    'number' => $request->number,
                    'name' => $request->name,
                    'address' => $request->address,
                    'gender' => $request->gender,
                    'picture' => $request->picture,
                    'city_name' => $request->city_name,
                    'tailor_id' => $tailor_id,
                    'customer_id' => $customer->id,
                ]);

                if ($tailorcustomer->save()) {
                    return response()->json(['success' => true, 'message' => 'Your Customer Created Successfully', 'data' => ['Tailor Customer id' => $tailorcustomer->id]], 200);
                } else {
                    return response()->json(['success' => false, 'message' => 'Customer Creation Failed', 'data' => []], 500);
                }
            }
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
