<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateBuilderController extends Controller
{
    public function save(Request $request)
    {
        $html = $request->input('html');
        $css = $request->input('css');

        // Contoh simpan ke file (bisa disesuaikan ke DB)
        Storage::put('builder/template.html', $html);
        Storage::put('builder/style.css', $css);

        return response()->json(['message' => 'Saved']);
    }
}
