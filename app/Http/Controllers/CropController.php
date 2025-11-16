<?php

namespace App\Http\Controllers;
use App\Models\Crop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CropController extends Controller
{
public function store(Request $request)
{
    $validatedData = $request->validate([
        'user_id' => 'required|exists:users,id',
        'productName' => 'required|string|max:255',
        'productCategory' => 'required|string|max:255',
        'pricePerKilo' => 'required|numeric',
        'quantity' => 'required|integer',
        'status' => 'required|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $user = auth('sanctum')->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // إنشاء السجل بدون الصورة
    $crop = Crop::create($validatedData);

    // تحميل الصورة إذا تم إرسالها
    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        $photoName = time() . '_' . $photo->getClientOriginalName(); // اسم فريد للصورة
        $photo->storeAs('photos', $photoName, 'public'); // حفظ في storage/app/public/photos

        // تحديث السجل باسم الصورة فقط
        $crop->photo = $photoName;
        $crop->save();
    }

    return response()->json([
        'message' => 'Crop added successfully',
        'data' => [
            'id' => $crop->id,
            'user_id' => $crop->user_id,
            'productName' => $crop->productName,
            'productCategory' => $crop->productCategory,
            'pricePerKilo' => $crop->pricePerKilo,
            'quantity' => $crop->quantity,
            'status' => $crop->status,
            'photo' => $crop->photo, // إرجاع اسم الصورة فقط
            'created_at' => $crop->created_at,
            'updated_at' => $crop->updated_at,
        ]
    ]);
}

public function update(Request $request, $id)
{
    $crop = Crop::find($id);

    if (!$crop) {
        return response()->json(['error' => 'Crop not found'], 404);
    }

    // التحقق من البيانات
    $request->validate([
        'user_id' => 'required|integer',
        'productName' => 'required|string|max:255',
        'productCategory' => 'required|string|max:255',
        'pricePerKilo' => 'required|numeric',
        'quantity' => 'required|integer',
        'status' => 'required|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // تحديث البيانات بما فيها user_id
    $crop->update($request->only([
        'user_id', 'productName', 'productCategory', 'pricePerKilo', 'quantity', 'status'
    ]));

    return response()->json([
        'message' => 'Product updated successfully',
        'crop' => $crop
    ]);
}

public function destroy($id)
{
    try {
        // Find the crop
        $crop = Crop::findOrFail($id);

        // Delete the crop
        $crop->delete();

        return response()->json(['message' => 'Crop deleted successfully'], 200);
    } catch (\Exception $e) {
        // Log the error and return a 500 response
        \Log::error($e->getMessage());

        return response()->json(['error' => 'An error occurred while deleting the crop'], 500);
    }
}

public function getCropsByUserId($user_id)
{
    try {
        $crops = Crop::where('user_id', $user_id)->get();

        if ($crops->isEmpty()) {
            return response()->json(['message' => 'No Crop found for this user.'], 404);
        }

        // Ensure the response contains only the image name
        $crops->transform(function ($crop) {
            return [
                'id' => $crop->id,
                'user_id' => $crop->user_id,
                'productName' => $crop->productName,
                'productCategory' => $crop->productCategory,
                'pricePerKilo' => $crop->pricePerKilo,
                'quantity' => $crop->quantity,
                'status' => $crop->status,
                'photo' => $crop->photo, // Return only the filename
                'created_at' => $crop->created_at,
                'updated_at' => $crop->updated_at,
            ];
        });

        return response()->json(['Crops' => $crops], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500);
    }
}



}
