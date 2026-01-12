<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaticAnalysisController extends Controller
{
    public function index()
    {
        return view('static-analysis');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'video_file' => 'required|mimetypes:video/mp4,video/avi,video/mpeg|max:50000', // Max 50MB
        ]);

        // Simpan video ke folder 'public/videos'
        if ($request->file('video_file')) {
            $fileName = time() . '_' . $request->file('video_file')->getClientOriginalName();
            $path = $request->file('video_file')->storeAs('videos', $fileName, 'public');

            // Kembalikan view dengan path video
            return view('static-analysis', ['video_path' => '/storage/' . $path, 'filename' => $fileName]);
        }

        return back()->with('error', 'Gagal upload video.');
    }
}
