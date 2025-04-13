<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

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
     *             @OA\Property(property="message", type="string", example="Order Payments"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Order ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid Order ID")
     *         )
     *     )
     * )
     */

    public function index($order_id)
    {
        $tailor_id = auth('sanctum')->user()->id;
        $order = Order::where([['id', $order_id], ['tailor_id', $tailor_id]])->first();
        if (!$order) {
            return response()->json(['success' => true, 'message' => 'Invalid Order ID'], 200);
        }
        $payments = Payment::where('order_id', $order_id)->get();
        return response()->json(['success' => true, 'message' => 'Order Payments', 'data' => [$payments]], 200);
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
     *             required={"amount", "date", "order_id", "tailor_id", "customer_id"},
     *             @OA\Property(property="title", type="string", example="Advance Payment"),
     *             @OA\Property(property="method", type="string", example="Bank Transfer"),
     *             @OA\Property(property="amount", type="number", format="float", example=5000),
     *             @OA\Property(property="date", type="string", format="date", example="2025-04-01"),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="tailor_id", type="integer", example=1),
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
            'tailor_id' => 'required',
            'customer_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Payment data validation error', 'data' => $validation->errors()], 422);
        }

        $payment = Payment::create([
            'title' => $request->title,
            'method' => $request->method,
            'amount' => $request->amount,
            'date' => $request->date,
            'order_id' => $request->order_id,
            'tailor_id' => $request->tailor_id,
            'customer_id' => $request->customer_id,
        ]);

        if ($payment->save()) {
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
