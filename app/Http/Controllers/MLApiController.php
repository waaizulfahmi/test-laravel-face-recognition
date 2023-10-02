<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Predict;
use App\Models\User;
use Illuminate\Support\Str;
use App\Events\WebSocketEvent;

use Carbon\Carbon;
// use App\Http\Controllers\Image;
// use App\Http\Controllers\Input;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Zip;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class MLApiController extends Controller
{
    public function login(Request $request)
    {
        $imageData = $request->input('image');

        // mengslipt 
        $parts = explode(',', $imageData);
        $base64Data = $parts[1];

        // Determine the file extension based on the MIME type
        $mimeType = explode(';', $parts[0]);
        $extension = 'png'; // Default to PNG
        if (count($mimeType) > 1) {
            $typeParts = explode('/', $mimeType[0]);
            if (count($typeParts) === 2) {
                $extension = $typeParts[1];
            }
        }
        
        // Generate a unique file name
        $fileName = 'image_' . uniqid() . '.' . $extension;
        
        // Decode the Base64 data and save it as a file
        $imageDataDecoded = base64_decode($base64Data);
        Storage::disk('public')->put($fileName, $imageDataDecoded);
        
        // // load temporary file image
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'image_');
        file_put_contents($tmpFilePath, $imageDataDecoded);
        // setting time execution
        ini_set('max_execution_time', 300);
        
        $command = escapeshellcmd("python C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/app.py $tmpFilePath" );
        $output = shell_exec($command);
        $substring = Str::after($output, "step\n");
        $cleaned = Str::between($substring, "", "\n");
        $cleanedText = Str::replace("\n", "", $cleaned);
              
        if ($cleanedText) {    
            $user = User::where('name', $cleanedText)->first();
            if ($user) {
                Auth::login($user);
                
                return response()->json(['message' => 'Login successful']);
            }
        }

      
        return response()->json(['message' => 'Image saved successfully', 'file_name' => $fileName, 'predictions' => $cleanedText]);

    }

    public function register(Request $request)
    {
        // Mengambil data JSON dari permintaan
        $data = $request->json()->all();

        if (!isset($data['images']) || !is_array($data['images'])) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
        $name = $data['name'];

        
        // $name = $dataName['value'];

        $images = $data['images'];

        $uniqueDirectoryName = $name ."-". time(); // Membuat nama direktori unik untuk setiap gambar
        $directoryPath = public_path() . '/' . $uniqueDirectoryName;

        Storage::disk('public')->makeDirectory($directoryPath);
        Storage::setVisibility($directoryPath, 'public');
        
        foreach ($images as $img) {
            $parts = explode(',', $img);
            $base64Data = $parts[1];
            $mimeType = explode(';', $parts[0]);
            $extension = 'png'; // Default to PNG
            if (count($mimeType) > 1) {
                $typeParts = explode('/', $mimeType[0]);
                if (count($typeParts) === 2) {
                    $extension = $typeParts[1];
                }
            }
            $fileName = 'image_' . uniqid() . '.' . $extension;
            $imageDataDecoded = base64_decode($base64Data);
            Storage::disk('public')->put($uniqueDirectoryName . '/' . $fileName, $imageDataDecoded);
            // Zip::create(public_path('your_folder.zip'))->add(public_path('your_folder'));
        }

        $zipFilePath = public_path("storage/".$uniqueDirectoryName);
        ini_set('max_execution_time', 300);
        
        $command = escapeshellcmd("python C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/test-zip.py $zipFilePath" );
        $output = shell_exec($command);
        
        return response()->json(['message' => 'Images received and processed successfully', 'image' => $zipFilePath, 'python' => $output]);
    }

    

    public function images(Request $request)
    {
        // Mengambil data JSON dari permintaan
        $data = $request->json()->all();

        // Pastikan data yang diterima adalah array gambar
        if (!isset($data['images']) || !is_array($data['images'])) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
        // $noImageData = 0;
        $images = $data['images'];
        
        $uniqueDirectoryName = "images-". time(); ;// Membuat nama direktori unik untuk setiap gambar
        $directoryPath = public_path() . '/' . $uniqueDirectoryName;

        Storage::disk('public')->makeDirectory($directoryPath);
        Storage::setVisibility($directoryPath, 'public');

        foreach ($images as $img) {
            $parts = explode(',', $img);
            $base64Data = $parts[1];
            $mimeType = explode(';', $parts[0]);
            $extension = 'png'; // Default to PNG
            if (count($mimeType) > 1) {
                $typeParts = explode('/', $mimeType[0]);
                if (count($typeParts) === 2) {
                    $extension = $typeParts[1];
                }
            }
            $fileName = 'image_' . uniqid() . '.' . $extension;
            $imageDataDecoded = base64_decode($base64Data);
            Storage::disk('public')->put($uniqueDirectoryName . '/' .$fileName, $imageDataDecoded);
        }

        return response()->json(['message' => 'Images received and processed successfully', 'image' => $directoryPath]);
    }

    public function sendDataToWebSocket(Request $request)
    {
        $message = 'Hello from Laravel Controller!'; // Data yang ingin Anda kirim

        event(new WebSocketEvent($message));
        // $output = $event->message;
        $response = $request->input('result'); // Sesuaikan dengan nama field yang sesuai di request
        // Lakukan apa yang perlu Anda lakukan dengan respons dari Python
        // Contoh: Simpan data ke database atau tangani sesuai kebutuhan Anda

        // return response()->json(['message' => $response]);

        return response()->json(['message' => 'Data sent to WebSocket', 'hallo' => $response]);
    }

    public function test(Request $request)

    {
        $response = $request->input('result');


        return response()->json(['message' => $response]);

    }
        // $imageData = $request->input('image_data');

        // $parts = explode(',', $imageData);
        // $base64Data = $parts[1];

        // // Determine the file extension based on the MIME type
        // $mimeType = explode(';', $parts[0]);
        // $extension = 'png'; // Default to PNG
        // if (count($mimeType) > 1) {
        //     $typeParts = explode('/', $mimeType[0]);
        //     if (count($typeParts) === 2) {
        //         $extension = $typeParts[1];
        //     }
        // }
        
        // // Generate a unique file name
        // $fileName = 'image_' . uniqid() . '.' . $extension;
        
        // // Decode the Base64 data and save it as a file
        // $imageDataDecoded = base64_decode($base64Data);
        // Storage::disk('public')->put($fileName, $imageDataDecoded);
        // return response()->json(['message' => 'Image uploaded successfully', 'repsons'=> $imageData]);}

        // $path = storage_path('app/public/images');
        // $filename = uniqid() . '.jpg';
        // file_put_contents("$path/$filename", $imageData);

        // Respon sukses jika pemrosesan berhasil
}
