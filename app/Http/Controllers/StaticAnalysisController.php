<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AnalysisJob;
use App\Events\TrafficDataUpdated; // <--- CRITICAL IMPORT ADDED

class StaticAnalysisController extends Controller
{
    public function index()
    {
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

            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);

            AnalysisJob::create([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'status' => 'pending'
            ]);

            return view('static-analysis', [
                'video_path' => 'uploads/' . $filename,
                'filename' => $filename
            ]);
        }

        return back()->with('error', 'Upload failed');
    }

    // --- NEW: THIS IS THE MISSING FUNCTION ---
    public function updateData(Request $request)
    {
        // 1. Receive Data from Python
        $validated = $request->validate([
            'car_count' => 'required|integer',
            'mode'      => 'nullable|string', // Python sends 'static'
            'source_id' => 'nullable|string', // Python sends the filename
        ]);

        // 2. Prepare the Payload
        $statistics = [
            'car_count' => $validated['car_count'],
            'timestamp' => now()->toDateTimeString(),
        ];

        $type = $request->input('mode', 'live');
        $id   = $request->input('source_id', 'default-cam');

        // 3. BROADCAST TO FRONTEND
        // This takes the data and sends it to Reverb -> Browser
        event(new TrafficDataUpdated($statistics, $type, $id));

        return response()->json(['status' => 'success']);
    }
}
