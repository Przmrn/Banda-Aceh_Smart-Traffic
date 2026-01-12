<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AnalysisJob;

class StaticAnalysisController extends Controller
{
    public function index()
    {
        // We must pass null values so the View doesn't crash on initial load
        return view('static-analysis', [
            'video_path' => null,
            'filename' => null
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'video_file' => 'required|mimes:mp4,avi,mov|max:50000',
        ]);

        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');

            // Generate a unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);

            // --- NEW: Create a Job in Database ---
            AnalysisJob::create([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'status' => 'pending' // Python will look for this
            ]);

            // Return view with the filename so the frontend knows what to listen for
            return view('static-analysis', [
                'video_path' => 'uploads/' . $filename,
                'filename' => $filename
            ]);
        }

        return back()->with('error', 'Upload failed');
    }
}
