<?php

namespace App\Library;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ZipHelper
{
    /**
     * @param $disk
     * @param array $files
     * @param null $zip_name
     * @param null $directory_name
     * @return bool|string
     */
    public static function createZip($disk, $files = array(), $zip_name = null, $directory_name = null)
    {
        if ($zip_name == null) {
            $timestamp = Carbon::now()->format('YmdHsiu');
            $zip_name = 'archive' . '_' . $timestamp . '.zip';
        }

        $info = pathinfo($zip_name);

        if ($info["extension"] != "zip") {
            \Log::info('extension is not zip');
            return false;
        }

        $validFiles = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($disk != null) {
                    if (Storage::disk($disk)->exists($file)) {
                        $validFiles[] = $file;
                    }
                } else {
                    if (file_exists($file)) {
                        $validFiles[] = $file;
                    }
                }
            }
        }

        $zip = new ZipArchive();
        if ($zip->open('storage/zips/'.$zip_name) !== true) {
            \Log::info('Storage zip can`t open');
            return false;
        }

        if (count($validFiles)) {
            foreach ($validFiles as $file) {
                if ($disk != null) {
                    if ($directory_name) {
                        $naming = $directory_name.'/'.$file;
                    } else {
                        $naming = $file;
                    }
                    $zip->addFile(get_full_storage_path($file, $disk), $naming);
                } else {
                    $zip->addFile($file, $file);
                }
            }

            $zip->close();
        } else {
            \Log::info('Not valid files available');
        }

        return $zip_name;
    }
}