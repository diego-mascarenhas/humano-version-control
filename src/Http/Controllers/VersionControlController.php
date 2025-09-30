<?php

namespace Idoneo\HumanoVersionControl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class VersionControlController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStats();
        $recentActivity = $this->getRecentActivity();
        $modelStats = $this->getModelStats();

        return view('humano-version-control::version-control.index', compact(
            'stats',
            'recentActivity',
            'modelStats'
        ));
    }

    /**
     * ✅ Versiones mejoradas - Acceso dinámico
     */
    public function getVersions(Request $request, string $model, int $id)
    {
        $modelClass = $this->resolveModelClass($model);

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $subject = $modelClass::find($id);
        if (!$subject) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $activities = Activity::forSubject($subject)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                    'causer' => $activity->causer ? $activity->causer->name : 'System',
                    'properties' => $activity->properties,
                ];
            });

        return response()->json($activities);
    }

    private function getStats(): array
    {
        return [
            'total_activities' => Activity::count(),
            'today_activities' => Activity::whereDate('created_at', today())->count(),
            'tracked_models' => Activity::distinct('subject_type')->count('subject_type'),
            'active_users' => Activity::distinct('causer_id')->whereNotNull('causer_id')->count('causer_id'),
        ];
    }

    private function getRecentActivity()
    {
        return Activity::with('causer', 'subject')
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * ✅ Stats de modelos mejorados - Nombres amigables
     */
    private function getModelStats(): array
    {
        return Activity::select('subject_type', DB::raw('count(*) as total'))
            ->groupBy('subject_type')
            ->orderBy('total', 'desc')
            ->take(10)
            ->mapWithKeys(function ($item) {
                $displayName = $this->getModelDisplayName($item->subject_type);
                return [$displayName => $item->total];
            })
            ->toArray();
    }

    /**
     * ✅ Obtener nombre amigable del modelo dinámicamente
     */
    private function getModelDisplayName(string $modelClass): string
    {
        $basename = class_basename($modelClass);

        // Convertir CamelCase a words separadas
        $words = preg_split('/(?=[A-Z])/', $basename, -1, PREG_SPLIT_NO_EMPTY);

        return implode(' ', $words);
    }

    /**
     * ✅ Resolver clase de modelo dinámicamente - ¡ELIMINA EL MAPEO ESTÁTICO!
     */
    private function resolveModelClass(string $modelIdentifier): ?string
    {
        // Obtener todos los tipos de modelos disponibles
        $availableTypes = Activity::distinct('subject_type')->pluck('subject_type');

        foreach ($availableTypes as $type) {
            // Comparar por basename (case-insensitive)
            if (strtolower(class_basename($type)) === strtolower($modelIdentifier)) {
                return $type;
            }

            // Comparar por clase completa
            if ($type === $modelIdentifier) {
                return $type;
            }
        }

        return null;
    }

    // ❌ MÉTODO ELIMINADO - Ya no se necesita mapeo estático
    // private function getModelClass(string $model): ?string
}