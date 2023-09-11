<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function uploadImage(Request $request)
    {
        try {
            $imageData = $request->input('image');
            $base64Image = substr($imageData, strpos($imageData, ',') + 1);
            $image = base64_decode($base64Image);
            
            // Simpan gambar ke direktori yang sesuai
            $imageName = 'captured_image_' . time() . '.jpg';
            file_put_contents(public_path('images/' . $imageName), $image);

            return response()->json(['message' => 'Image uploaded successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload image'], 500);
        }
    }
}
