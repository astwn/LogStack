<?php

namespace App\Http\Controllers;

use App\Services\BrandingService;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    /**
     * Show branding settings page (via AJAX for dashboard tab)
     */
    public function index()
    {
        $branding = BrandingService::get();
        return response()->json($branding);
    }

    /**
     * Save branding settings
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'app_name'      => 'required|string|max:50',
            'app_full_name' => 'required|string|max:100',
            'app_tagline'   => 'nullable|string|max:255',
            'app_logo_icon' => 'required|string|max:100',
            'app_version'   => 'nullable|string|max:20',
            'footer_text'   => 'nullable|string|max:150',
            'primary_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'accent_color'  => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'loader_text'   => 'nullable|string|max:50',
        ]);

        $success = BrandingService::save($validated);

        if ($success) {
            return redirect()->back()->with('success', 'Branding berhasil disimpan.');
        }

        return redirect()->back()->with('error', 'Gagal menyimpan branding. Coba lagi.');
    }

    /**
     * Reset branding to defaults
     */
    public function reset()
    {
        $success = BrandingService::reset();

        if ($success) {
            return redirect()->back()->with('success', 'Branding berhasil direset ke default.');
        }

        return redirect()->back()->with('error', 'Gagal mereset branding.');
    }
}
