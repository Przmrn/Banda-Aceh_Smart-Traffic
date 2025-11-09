<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; // Tambahkan ini untuk File facade

class TrafficController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan form upload.
     */
    public function showDashboard()
    {
        return view('dashboard');
    }

    /**
     * Mengirim video ke API Python dan menampilkan hasilnya.
     */
    public function analyzeVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg|max:51200', // max 50MB
        ]);

        $videoFile = $request->file('video');

        // Simpan file asli untuk dikirim ke Python, tapi tidak perlu public storage
        // Ini akan disimpan di storage/app/temp_input_laravel.mp4
        $temp_input_path = $videoFile->storeAs('temp', 'input_video_' . uniqid() . '.mp4');
        $full_temp_input_path = storage_path('app/' . $temp_input_path);

        try {
            // Kirim file video ke API Python dari lokasi sementara di Laravel
            $response = Http::timeout(600) // Tingkatkan timeout karena proses video lebih lama
            ->attach(
                'video_file',
                file_get_contents($full_temp_input_path),
                $videoFile->getClientOriginalName()
            )
                ->post('http://127.0.0.1:5000/analyze-video');

            // Hapus file input sementara setelah dikirim ke Python
            File::delete($full_temp_input_path);

            if ($response->successful()) {
                $response_data = $response->json();
                $statistics = $response_data['statistics'];
                $python_output_video_path = $response_data['output_video_path'];

                // Pindahkan video hasil dari server Python ke public storage Laravel
                // Asumsi app.py menyimpan video output di folder yang sama dengan app.py
                $python_app_dir = base_path('python'); // Ubah jika app.py ada di subfolder lain
                $full_python_output_path = $python_app_dir . '/' . $python_output_video_path;

                if (File::exists($full_python_output_path)) {
                    $unique_filename = 'analyzed_video_' . uniqid() . '.mp4';
                    $laravel_public_output_path = 'public/analyzed_videos/' . $unique_filename;

                    // Pindahkan file dari folder Python ke storage Laravel
                    Storage::put($laravel_public_output_path, File::get($full_python_output_path));

                    // Dapatkan URL publik untuk video yang sudah diproses
                    $processed_video_url = Storage::url($laravel_public_output_path);

                    // Hapus file video hasil dari folder Python setelah dipindahkan
                    File::delete($full_python_output_path);

                    return back()
                        ->with('statistics', $statistics)
                        ->with('processed_video_url', $processed_video_url); // Kirim URL video PROSES
                } else {
                    return back()->with('error', 'API Error: Video hasil tidak ditemukan di server Python.');
                }
            } else {
                return back()->with('error', 'API Error: ' . $response->body());
            }

        } catch (ConnectionException $e) {
            File::delete($full_temp_input_path); // Pastikan input temp dihapus meski error
            return back()->with('error', 'Tidak dapat terhubung ke server analisis. Pastikan server Python berjalan.');
        } catch (\Exception $e) {
            File::delete($full_temp_input_path); // Pastikan input temp dihapus meski error
            return back()->with('error', 'Terjadi kesalahan tidak terduga: ' . $e->getMessage());
        }
    }
}
