<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HelpController extends Controller
{
    public function show(string $page): JsonResponse
    {
        $guides = config('help_guide');
        $key    = str_replace('__', '.', $page); // convert URL-safe key back to dot notation

        if (isset($guides[$key])) {
            return response()->json($guides[$key]);
        }

        return response()->json([
            'title' => 'Panduan',
            'steps' => ['Belum ada panduan untuk halaman ini.'],
        ]);
    }
}
