<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Predict;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MLApiController extends Controller
{
    public function predict(Request $request)
    {
        // Ambil data gambar dari permintaan
        // $predict = new Predict();
        $imageData = $request->file('image');
        // if ($request->hasFile('image')) {
        //     $file = $request->file('image')->store('post-images');
            

        // }
        
        // $file = $predict->image ;

        // $fileImage = "http://127.0.0.1:8000/storage/". $file;
        ini_set('max_execution_time', 300);
        
        $command = escapeshellcmd("py C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/app.py $imageData" );
        $output = shell_exec($command);


        
        return response()->json(['prediction' => $output]);
        
        
        // dd($imageData);
        // PROCESS SYMFONY
        // putenv("PYTHONHASHSEED=0");
        // $process = new Process(['C:/Users/HP/AppData/Local/Programs/Python/Python310/python.exe', 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/app.py', $imageData]);
        // $process->run();
        // $output = $process->getOutput();
        // if (!$process->isSuccessful()) {
        //     throw new ProcessFailedException($process);
        // }

    }
}
