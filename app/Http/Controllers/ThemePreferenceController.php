<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemePreferenceController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate(['theme' => ['required', 'string', 'in:light,dark,system']]);
        $request->user()->forceFill(['theme_preference' => $data['theme']])->saveQuietly();
        return response()->json(['theme' => $data['theme']]);
    }
}
