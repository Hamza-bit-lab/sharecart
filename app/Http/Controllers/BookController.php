<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookController extends Controller
{
    public function getVersion()
    {
        $path = public_path('book/version.json');

        if (!file_exists($path)) {
            return response()->json(['error' => 'version.json not found'], 404);
        }

        $data = json_decode(file_get_contents($path), true);

        return response()->json($data);
    }

    public function updateVersion(Request $request)
    {
        $request->validate([
            'version' => 'required|integer',
        ]);

        $path = public_path('book/version.json');

        $data = [
            'version' => $request->version,
            'pdf_url' => url('book/book.pdf'),
        ];

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return response()->json(['success' => true, 'data' => $data]);
    }
}