<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/tailors/expenses/orders/{order_id}",
     *     summary="Get order expenses",
     *     description="Retrieves a list of expenses associated with a specific order.",
     *     tags={"Expenses"},
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
     *         description="Successful response with order expenses",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Expenses"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_id", type="integer", example=10),
     *                     @OA\Property(property="name", type="string", example="Fabric Cost"),
     *                     @OA\Property(property="amount", type="integer", example=500),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-15T22:07:35Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-15T22:07:35Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
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
        $expenses = Expense::where('order_id', $order_id)->get();
        return response()->json(['success' => true, 'message' => 'Order Expenses', 'data' => [$expenses]], 200);
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
     *     path="/tailors/expenses/orders",
     *     summary="Create a new expense",
     *     description="Stores a new expense record in the database.",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"amount", "order_id"},
     *             @OA\Property(property="title", type="string", example="Fabric Cost"),
     *             @OA\Property(property="amount", type="integer", example=500),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="dress_id", type="integer", nullable=true, example=null),
     *             @OA\Property(property="cloth_id", type="integer", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Expense Added Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Expense data validation error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"amount": {"The amount field is required."}, "order_id": {"The order_id field is required."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Expense cannot be added",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Expense cannot be added")
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
            'dress_id' => '',
            'cloth_id' => ''
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Expense data validation error', 'data' => $validation->errors()], 422);
        }

        $tailor_id = auth('sanctum')->user()->id;
        $order_id = $request->order_id;

        $expense = Expense::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'order_id' => $order_id,
            'tailor_id' => $tailor_id,
            'dress_id' => $request->dress_id,
            'cloth_id' => $request->cloth_id,
        ]);

        if ($expense->save()) {
            $expense_order = $expense->order;
            $expense_order->increment('total_expenses', $request->amount);

            return response()->json(['success' => true, 'message' => 'Expense Added Successfullyy'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Expense cannot be added'], 500);
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
     *     path="/tailors/expenses/{expense_id}/destroy",
     *     summary="Delete an expense",
     *     description="Deletes an expense by its ID.",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="expense_id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Expense deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Expense not found")
     *         )
     *     )
     * )
     */
    public function destroy($expense_id)
    {
        $expense = Expense::find($expense_id);
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense already deleted'], 200);
        }

        $expense->delete();
        return response()->json(['success' => true, 'message' => 'Expense deleted successfully'], 200);
    }
}
