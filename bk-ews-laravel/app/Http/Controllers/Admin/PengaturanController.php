<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PengaturanController extends Controller
{
    public function index()
    {
        $flaskUrl     = PengaturanSistem::get('flask_api_url', env('FLASK_API_URL', 'http://127.0.0.1:5000'));
        $waToken      = PengaturanSistem::get('wa_gateway_token', env('FONNTE_TOKEN', ''));
        $namaSekolah  = PengaturanSistem::get('nama_sekolah', 'SMK Sunan Ampel Jombang');

        return view('admin.pengaturan.index', compact('flaskUrl', 'waToken', 'namaSekolah'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'flask_api_url'    => 'required|url',
            'wa_gateway_token' => 'nullable|string',
            'nama_sekolah'     => 'required|string|max:255',
        ]);

        PengaturanSistem::set('flask_api_url', rtrim($validated['flask_api_url'], '/'), 'URL Endpoint Microservice Machine Learning Flask');
        PengaturanSistem::set('wa_gateway_token', $validated['wa_gateway_token'] ?? '', 'Token WhatsApp Gateway Fonnte');
        PengaturanSistem::set('nama_sekolah', $validated['nama_sekolah'], 'Nama Sekolah / Instansi');

        return redirect()->route('admin.pengaturan.index')
            ->with('success', 'Konfigurasi sistem berhasil disimpan.');
    }

    public function testFlask(Request $request)
    {
        $url = $request->input('url', PengaturanSistem::get('flask_api_url', 'http://127.0.0.1:5000'));
        $url = rtrim($url, '/');

        try {
            $start = microtime(true);
            $response = Http::timeout(3)->get("{$url}/health");
            $duration = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return response()->json([
                    'status'   => 'success',
                    'message'  => "Koneksi Berhasil! Respons dalam {$duration}ms.",
                    'data'     => $response->json(),
                ]);
            }

            return response()->json([
                'status'   => 'error',
                'message'  => "Server merespons dengan status HTTP {$response->status()}.",
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'status'   => 'error',
                'message'  => 'Gagal terhubung ke Flask API: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testWhatsapp(Request $request)
    {
        $token = $request->input('token', PengaturanSistem::get('wa_gateway_token', ''));

        if (empty($token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token WhatsApp belum diisi.',
            ], 400);
        }

        try {
            // Cek status device ke Fonnte API
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => $token])
                ->post('https://api.fonnte.com/device');

            if ($response->successful()) {
                $body = $response->json();
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Token valid! Device: ' . ($body['device'] ?? 'Terhubung'),
                    'data'    => $body,
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Fonnte merespons dengan status: ' . $response->status(),
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghubungi WhatsApp Gateway: ' . $e->getMessage(),
            ], 500);
        }
    }
}
