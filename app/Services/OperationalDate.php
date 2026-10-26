<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OperationalDate
{
    public static function normalize(Request $request, array $fields): void
    {
        if (!$request->has('timezone')) return; // Existing integrations submit UTC.
        $request->validate(['timezone' => ['required', \Illuminate\Validation\Rule::in(['UTC', 'America/Lima'])]]);
        foreach ($fields as $field) {
            if (!$request->filled($field)) continue;
            try {
                $request->merge([$field => Carbon::parse($request->input($field), $request->input('timezone'))->utc()->format('Y-m-d H:i:s')]);
            } catch (\Throwable $error) {
                throw ValidationException::withMessages([$field => 'Introduce una fecha y hora válidas.']);
            }
        }
    }
}
