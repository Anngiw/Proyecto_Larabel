<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TareaController extends Controller
{
    public function index(Request $req): JsonResponse
    {
        $items = Tarea::where('usuario_id', $req->user()->id)
            ->orderBy('fecha_inicio')
            ->get();

        return response()->json(['data' => $items], 200);
    }

    public function show(Request $req, int $id): JsonResponse
    {
        $tarea = $this->findOwn($req, $id);
        if (!$tarea) {
            return response()->json(['message' => 'Tarea no encontrada.'], 404);
        }

        return response()->json(['data' => $tarea], 200);
    }

    public function store(Request $req): JsonResponse
    {
        try {
            $data = $this->validar($req);
            $data['usuario_id'] = $req->user()->id;

            $tarea = Tarea::create($data);

            return response()->json([
                'message' => 'Tarea creada con éxito.',
                'data' => $tarea,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Revisa los datos ingresados.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function update(Request $req, int $id): JsonResponse
    {
        $tarea = $this->findOwn($req, $id);
        if (!$tarea) {
            return response()->json(['message' => 'Tarea no encontrada.'], 404);
        }

        try {
            $data = $this->validar($req);
            $tarea->update($data);

            return response()->json([
                'message' => 'Tarea actualizada con éxito.',
                'data' => $tarea->fresh(),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Revisa los datos ingresados.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Request $req, int $id): JsonResponse
    {
        $tarea = $this->findOwn($req, $id);
        if (!$tarea) {
            return response()->json(['message' => 'Tarea no encontrada.'], 404);
        }

        $tarea->delete();

        return response()->json(['message' => 'Tarea eliminada con éxito.'], 200);
    }

    private function validar(Request $req): array
    {
        return $req->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', 'string', Rule::in(Tarea::ESTADOS)],
        ], [
            'nombre.required' => 'El nombre de la tarea es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Pendiente, En progreso o Completada.',
        ]);
    }

    private function findOwn(Request $req, int $id): ?Tarea
    {
        return Tarea::where('id', $id)
            ->where('usuario_id', $req->user()->id)
            ->first();
    }
}