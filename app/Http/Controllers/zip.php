use ZipArchive;

public function createZipFolder()
{
    // Nama folder yang ingin Anda zip
    $folderName = 'nama_folder_anda';
    
    // Nama untuk file zip yang akan Anda buat
    $zipFileName = public_path() . '/' . $folderName . '.zip';

    // Inisialisasi objek ZipArchive
    $zip = new ZipArchive();

    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        // Tambahkan semua file dan direktori dalam folder yang akan Anda zip
        $this->addFolderToZip($folderName, $zip);

        // Tutup file zip
        $zip->close();

        // File zip sekarang telah dibuat dengan folder dan isinya
        return response()->json(['message' => 'Folder successfully zipped', 'zip' => $zipFileName]);
    } else {
        return response()->json(['error' => 'Failed to create ZIP folder'], 500);
    }
}

private function addFolderToZip($folderPath, $zip)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folderPath) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
}
