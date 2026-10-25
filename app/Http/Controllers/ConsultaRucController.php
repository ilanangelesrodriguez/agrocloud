<?php

namespace App\Http\Controllers;

use App\Services\SunatRucService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultaRucController extends Controller
{
    public function __invoke(Request $request, SunatRucService $service): JsonResponse
    {
        $data = $request->validate(['ruc' => ['required', 'regex:/^[0-9]{11}$/D'], 'razon_social' => ['nullable', 'string', 'max:200']]);
        $result = $service->verificar($data['ruc'], $data['razon_social'] ?? '');
        if ($request->routeIs('acceso.consulta-ruc')) {
            $result = array_intersect_key($result, array_flip(['ruc', 'razon_social', 'resultado', 'mensaje', 'estado', 'condicion', 'actualizado']));
            if ($result['resultado'] === 'pendiente') $result['mensaje'] = 'La consulta no está disponible. Puedes completar la solicitud manualmente; la administración verificará los datos.';
        }
        return response()->json($result)->header('Cache-Control', 'no-store');
    }
}
