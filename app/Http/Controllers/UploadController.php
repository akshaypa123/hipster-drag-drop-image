<?php

namespace App\Http\Controllers;
use Intervention\Image\Facades\Image as ImageTool;
use App\Models\Upload;
use App\Models\Image;


use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'upload_id' => 'required',
            'chunk' => 'required|file',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
        ]);

        $uploadDir = storage_path("app/chunks/{$request->upload_id}");
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $chunkPath = $uploadDir . "/chunk_{$request->chunk_index}";
        file_put_contents($chunkPath, file_get_contents($request->file('chunk')), FILE_APPEND);

        return response()->json(['message' => "Chunk {$request->chunk_index} uploaded"]);
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'upload_id' => 'required',
            'filename' => 'required',
            'checksum' => 'required',
        ]);

        $uploadDir = storage_path("app/chunks/{$request->upload_id}");
        $finalPath = storage_path("app/uploads/{$request->filename}");

        $out = fopen($finalPath, 'w');
        foreach (glob("$uploadDir/chunk_*") as $chunk) {
            fwrite($out, file_get_contents($chunk));
        }
        fclose($out);

        if (md5_file($finalPath) !== $request->checksum) {
            return response()->json(['error'=>'Checksum mismatch'], 400);
        }

        // Save upload
        $upload = Upload::create([
            'upload_id' => $request->upload_id,
            'filename' => $request->filename,
            'status' => 'completed',
            'checksum' => $request->checksum,
        ]);

        // Create image variants
        $sizes = [256, 512, 1024];
        foreach ($sizes as $size) {
            $img = ImageTool::make($finalPath)->resize($size, null, fn($c) => $c->aspectRatio());
            $variantPath = "uploads/{$size}_{$request->filename}";
            $img->save(storage_path("app/$variantPath"));

            Image::create([
                'upload_id' => $upload->id,
                'path' => $variantPath,
                'variant' => (string)$size,
            ]);
        }

        return response()->json(['message'=>'Upload complete']);
    }
}
