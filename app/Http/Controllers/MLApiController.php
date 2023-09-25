<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Predict;
use App\Models\User;
use Illuminate\Support\Str;

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
        $string = str_replace("\n", "", $output);
        $cleanedText = Str::after($string, 'step');
        unlink($tmpFilePath);
        // $predictedData = json_decode($output);

        // $predictedName = trim($output);

        if ($cleanedText) {
            // Ada hasil yang valid dari model Python
            // Cari pengguna dengan nama yang sesuai di database
            $user = User::where('name', $cleanedText)->first();

            if ($user) {
                // Login pengguna menggunakan autentikasi Laravel
                Auth::login($user);
                
                return response()->json(['message' => 'Login successful']);
            }
        }

        // Jika tidak ada hasil yang valid dari model Python atau tidak ada pengguna yang sesuai
        // return response()->json(['message' => 'Authentication failed']);
        // Return a response or store the file path in the database
        return response()->json(['message' => 'Image saved successfully', 'file_name' => $fileName, 'predictions' => $cleanedText]);

    }

    public function register(Request $request)
    {
        // Mengambil data JSON dari permintaan
        $data = $request->json()->all();

        // Pastikan data yang diterima adalah array gambar
        if (!isset($data['images']) || !is_array($data['images'])) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
        // $dataName = json_decode($data, true);
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

        $zip = new ZipArchive;

        $fileName = $uniqueDirectoryName.".zip";
        if($zip->open($fileName, ZipArchive::CREATE)){
            $files = File::files(public_path('storage/'.$uniqueDirectoryName));
            foreach($files as $file){
                $nameInZipFile = basename($file);

                $zip->addFile($file, $nameInZipFile);
            }
            $zip->close();

        }
        $zipFilePath = public_path("storage/".$uniqueDirectoryName);
        ini_set('max_execution_time', 300);
        
        $command = escapeshellcmd("python C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/app2.py $zipFilePath" );
        $output = shell_exec($command);
        
        return response()->json(['message' => 'Images received and processed successfully', 'image' => $directoryPath, 'python' => $output]);
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
    public function test(Request $request)
    {
        // Mengambil data JSON dari permintaan
        $data = $request->json()->all();

        $dataImage = $data['name'];

        return response()->json(['message' => 'Images received and processed successfully', 'image' => $dataImage]);

        // Pastikan data yang diterima adalah array gambar
        // if (!isset($data['images']) || !is_array($data['images'])) {
        //     return response()->json(['error' => 'Invalid data format'], 400);
        // }
        // // $noImageData = 0;
        // $images = $data['images'];
        
        // $uniqueDirectoryName = "images-". time(); ;// Membuat nama direktori unik untuk setiap gambar
        // $directoryPath = public_path() . '/' . $uniqueDirectoryName;

        // Storage::disk('public')->makeDirectory($directoryPath);
        // Storage::setVisibility($directoryPath, 'public');

        // foreach ($images as $img) {
        //     $parts = explode(',', $img);
        //     $base64Data = $parts[1];
        //     $mimeType = explode(';', $parts[0]);
        //     $extension = 'png'; // Default to PNG
        //     if (count($mimeType) > 1) {
        //         $typeParts = explode('/', $mimeType[0]);
        //         if (count($typeParts) === 2) {
        //             $extension = $typeParts[1];
        //         }
        //     }
        //     $fileName = 'image_' . uniqid() . '.' . $extension;
        //     $imageDataDecoded = base64_decode($base64Data);
        //     Storage::disk('public')->put($uniqueDirectoryName . '/' .$fileName, $imageDataDecoded);
        // }

        // return response()->json(['message' => 'Images received and processed successfully', 'image' => $directoryPath]);
    }
}
