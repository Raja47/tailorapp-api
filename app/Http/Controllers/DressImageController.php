<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dress;
use App\Models\DressImage;
use Intervention\Image\Facades\Image;
use App\Models\Cloth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use OpenApi\Annotations as OA;

class DressImageController extends Controller
{

    /**
     * @OA\Get(
     *     path="/tailors/dresses/{id}/designs",
     *     summary="Get images for a dress",
     *     description="Returns a list of images for a dress based on the dress ID.",
     *     operationId="getDressImages",
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
     *         description="List of images for the dress",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="designs", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="path", type="string", example="/images/design1.jpg")
     *                 )
     *             ) 
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dress not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Dress not found")
     *         )
     *     )
     * )
     */
    public function designs($id)
    {
        $designs = DressImage::select(['id', 'path'])->where(['type' => 'design', 'dress_id' => $id])->get();
        foreach ($designs as $design) {
            $design->path = $design->path ? complete_url($design->path) : null;
        }
        return response()->json(['designs' => $designs]); // assumes relation images()
    }

    /**
     * @OA\Delete(
     *     path="/tailors/dresses/designs/{design_id}",
     *     summary="Delete a design image",
     *     description="Deletes a design image based on the design ID.",
     *     operationId="deleteDesign",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="design_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Design ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Design deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Design deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Design not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Design not found")
     *         )
     *     )
     * )
     */
    public function deleteDesign($id)
    {

        $image = DressImage::find($id);

        if (empty($image)) {
            return response()->json(['message' => 'Design not found'], 404);
        }

        $image->delete();

        return response()->json(['message' => 'Image deleted'], 200);
    }

    /**
     * @OA\Post(
     *     path="/tailors/dresses/{id}/designs",
     *     summary="Upload a Design image",
     *     description="Stores an image in 'public/dress'.",
     *     operationId="uploadDressDesign",
     *     tags={"Dresses.edit"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true, 
     *         @OA\Schema(type="integer"),
     *         description="Dress ID"
     *     ),
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(type="object", required={"designs[]"},
     *                 @OA\Property(property="designs[]", type="array", 
     *                      @OA\Items(type="string", format="binary"),
     *                      description="Array of design Image files")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Images uploaded",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Image uploaded"),
     *             @OA\Property(property="data", type="string", example="storage/dress/image.jpg")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data validation error"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function createDesign(Request $request, $id)
    {

        $validation = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096', // Validate each image file
        ]);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Image Type or Size Error', 'data' => $validation->errors()], 422);
        }

        $uploadedImages = [];

        $files = is_array($request->file('designs'))
            ? $request->file('designs')
            : [$request->file('designs')];

        foreach ($files as $file) {
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            $path = 'storage/dress/' . $filename;
            $file->storeAs('public/dress', $filename);

            $compressed_file = Image::make($file)->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode($file->getClientOriginalExtension(), 50);
            $thumb_path = 'storage/dress/thumbnails/' . $filename;
            Storage::put('public/dress/thumbnails' . $filename, $compressed_file);


            DressImage::create([
                'tailor_id' => auth('sanctum')->user()->id,
                'dress_id' => $id,
                'order_id' => Dress::findOrFail($id)->order_id,
                'type' => 'design',
                'path' => $path ,
                'thumb_path' => $thumb_path
            ]);

            $uploadedImages[] = [
                'id' => DressImage::latest()->first()->id,
                'path' => complete_url($path),
                'thumb_path' => complete_url($thumb_path)
            ];
        }
        return response()->json(['message' => 'Design Created Successfully', 'data' => ['designs' => $uploadedImages]], 200);
    }
}
