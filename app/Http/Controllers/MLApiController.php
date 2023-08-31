<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MLApiController extends Controller
{
    public function predict(Request $request)
    {
        // Ambil data gambar dari permintaan
        $imageData = $request->file('image');
        // dd($imageData);

        // $command = escapeshellcmd("py F:/PROJECT/portal-berita/app/Http/Controllers/sentimen/project-capstone/app.py " );
        $command = escapeshellcmd("py C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/app.py $imageData" );
        $output = shell_exec($command);



        // Proses gambar menggunakan model machine learning
        // $prediction = YourMLModel::predict($imageData);

        // Kembalikan hasil prediksi sebagai respons JSON
        return response()->json(['prediction' => $output]);
    }
}
