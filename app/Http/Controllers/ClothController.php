<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dress;
use App\Models\Expense;
use App\Models\DressImage;
use App\Models\Cloth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use OpenApi\Annotations as OA;

class ClothController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dress/{id}/clothes",
     *     summary="Get clothes for a dress",
     *     description="Returns all clothes for a dress",
     *     operationId="getDressClothes",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true, 
     *         @OA\Schema(type="integer"),
     *         description="Dress ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *          description="List of clothes for the dress",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="clothes", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="dress_id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="length", type="integer"),
     *                     @OA\Property(property="provided_by", type="string"),
     *                     @OA\Property(property="price", type="integer"),
     *                     @OA\Property(property="path", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ) 
     *        )
     *     ),
     *)
     */
    // Clothes
    public function getClothes($id)
    {
        $clothes = Cloth::with('image')->where('dress_id', $id)->get();
        $clothes = $clothes->map(function ($cloth) {
            return [
                'id' => $cloth->id,
                'dress_id' => $cloth->dress_id,
                'order_id' => $cloth->order_id,
                'tailor_id' => $cloth->tailor_id,
                'path' => $cloth->image?->path ? complete_url($cloth->image->path) : null,
                'title' => $cloth->title,
                'length' => $cloth->length,
                'unit' => $cloth->unit,
                'provided_by' => $cloth->provided_by,
                'price' => $cloth->price,
                'created_at' => $cloth->created_at->toIso8601ZuluString(),
                'updated_at' => $cloth->updated_at->toIso8601ZuluString(),
            ];
        });
        return response()->json(['clothes' => $clothes], 200);
    }
    /**
     * @OA\Post(
     *     path="/dress/{id}/clothes",
     *     summary="Create a new cloth for a dress",
     *     description="Creates a new cloth for a specific dress.",
     *     operationId="createCloth",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Dress ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Summer Dress"),
     *             @OA\Property(property="length", type="number", format="float", example=50.5),
     *             @OA\Property(property="provided_by", type="string", example="Tailor Name"),
     *             @OA\Property(property="price", type="number", format="float", example=100.00),
     *             @OA\Property(property="path", type="string", example="/images/cloth.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cloth created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Cloth Created Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Cloth Object", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response( 
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data validation error"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="The title field is required.")),
     *                 @OA\Property(property="provided_by", type="array", @OA\Items(type="string", example="The provided_by field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Cloth could not be created")
     *         )
     *     )
     * )
     **/
    public function createCloth(Request $request, $id)
    {
        $dress = Dress::findOrFail($id);
        $tailor_id = auth('sanctum')->user()->id;

        $rules = [
            'title' => 'nullable|string|max:255',
            'length' => 'nullable|numeric',
            'unit' => 'nullable|string|max:255',
            'provided_by' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'path' => 'nullable|string'
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['message' => 'Data validation error', 'data' => $validation->errors()], 422);
        }

        $dress_image = null;
        if (isset($request->path) && !empty($request->path)) {
            $dress_image = DressImage::create([
                'tailor_id' => $tailor_id,
                'dress_id' => $id,
                'order_id' => $dress->order_id,
                'type' => 'cloth',
                'path' => relative_url($request->path),
                'thumb_path' => relative_thumb_url($request->path),]); 
        }

        $cloth = Cloth::create([
            'dress_id' => $dress->id,
            'order_id' => $dress->order_id,
            'tailor_id' => $tailor_id,
            'title' => $request->title,
            'dress_image_id' => $dress_image?->id,
            'length' => $request->length,
            'unit' => $request->unit,
            'provided_by' => $request->provided_by,
            'price' => (isset($request->price) && $request->provided_by == 'tailor') ? $request->price : null,
        ]);

        $expense = Expense::create([
            'title' => 'Cloth Expense',
            'amount' => $request->price,
            'order_id' => $dress->order_id,
            'tailor_id' => $tailor_id,
            'dress_id' => $dress->id,
            'cloth_id' => $cloth->id,
        ]);

        $expense_order = $expense->order;
        $expense_order->increment('total_expenses', $request->price);
        $expense_order->refreshFinancialStatus();


        $clothResponse = [
            'id' => $cloth->id,
            'dress_id' => $cloth->dress_id,
            'order_id' => $cloth->order_id,
            'tailor_id' => $cloth->tailor_id,
            'title' => $cloth->title,
            'length' => $cloth->length,
            'unit' => $cloth->unit, 
            'provided_by' => $cloth->provided_by,
            'price' => $cloth->price,
            'path' => complete_url($dress_image?->path),
            'thumb_path' =>  complete_url($dress_image?->thumb_path),
            'created_at' => $cloth->created_at->toIso8601ZuluString(),
            'updated_at' => $cloth->updated_at->toIso8601ZuluString(),
        ];

        return response()->json(['message' => 'Cloth Created Successfully', 'data' => $clothResponse], 200);
    }

    /**
     * @OA\Delete(
     *     path="/dress/clothes/{id}",
     *     summary="Delete a cloth",
     *     description="Deletes a cloth based on the dress ID and cloth ID.",
     *     operationId="deleteCloth",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Cloth ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cloth deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Cloth deleted")
     *         )     
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cloth not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Cloth not found")
     *         )
     *     )
     * )       
     */
    public function deleteCloth($id)
    {
        $cloth = Cloth::findOrFail($id);
        $cloth->delete(); // Assuming delete, though method name says "update"
        return response()->json(['message' => 'Cloth Deleted'], 200);
    }
}
