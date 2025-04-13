<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/tailors/discounts/orders/{order_id}",
     *     summary="Get discounts for an order",
     *     description="Retrieves all discounts associated with a specific order for the authenticated tailor.",
     *     tags={"Discounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to fetch discounts for",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of discounts for the order",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Discounts"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Holiday Discount"),
     *                     @OA\Property(property="amount", type="number", example=500)
     *                 )
     *             )
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
        $discounts = Discount::where('order_id', $order_id)->get();
        return response()->json(['success' => true, 'message' => 'Order Discounts', 'data' => [$discounts]], 200);
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
     *     path="/tailors/discounts/orders",
     *     summary="Create a new discount",
     *     description="Adds a new discount to a specific order for the authenticated tailor.",
     *     tags={"Discounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"amount", "order_id", "tailor_id"},
     *             @OA\Property(property="title", type="string", example="Holiday Discount"),
     *             @OA\Property(property="amount", type="number", example=500),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="tailor_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Discount Added Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Discount data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add discount",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Discount cannot be added")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $rules = [
            'title' => 'string',
            'amount' => 'required',
            'order_id' => 'required',
            'tailor_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Discount data validation error', 'data' => $validation->errors()], 422);
        }

        $discount = Discount::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'order_id' => $request->order_id,
            'tailor_id' => $request->tailor_id,
        ]);

        if ($discount->save()) {
            return response()->json(['success' => true, 'message' => 'Discount Added Successfully'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Discount cannot be added'], 500);
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
     *     path="/tailors/discounts/{discount_id}/destroy",
     *     summary="Delete a discount",
     *     description="Deletes a specific discount by its ID for the authenticated tailor.",
     *     tags={"Discounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="discount_id",
     *         in="path",
     *         required=true,
     *         description="ID of the discount to delete",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount deleted successfully or already deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Discount deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discount not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Discount not found")
     *         )
     *     )
     * )
     */
    public function destroy($discount_id)
    {
        $discount = Discount::find($discount_id);
        if (!$discount) {
            return response()->json(['success' => false, 'message' => 'Discount already deleted'], 200);
        }

        $discount->delete();
        return response()->json(['success' => true, 'message' => 'Discount deleted successfully'], 200);
    }
}
