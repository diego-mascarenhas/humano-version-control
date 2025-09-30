<?php

namespace Idoneo\HumanoVersionControl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class AuditTrailController extends Controller
{
    /**
     * ✅ MÉTODO PRINCIPAL - Mostrar actividad específica por ID
     */
    public function showActivity(Request $request, int $activityId)
    {
        $activity = Activity::with(['causer', 'subject'])->find($activityId);

        if (!$activity) {
            abort(404, 'Activity not found');
        }

        // Obtener todas las actividades relacionadas al mismo sujeto
        $relatedActivities = Activity::forSubject($activity->subject)
            ->with('causer')
            ->latest()
            ->paginate(20);

        $subject = $activity->subject;
        $activities = $relatedActivities;
        $model = $this->getModelDisplayName($activity->subject_type);
        $modelSlug = strtolower(class_basename($activity->subject_type));
        $selectedActivity = $activity;

        return view('humano-version-control::audit-trail.show', compact(
            'subject',
            'activities',
            'model',
            'modelSlug',
            'selectedActivity'
        ));
    }

    /**
     * ✅ API endpoint para obtener versiones de una actividad específica
     */
    public function getActivityVersions(Request $request, int $activityId)
    {
        $activity = Activity::find($activityId);

        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activities = Activity::forSubject($activity->subject)
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

    /**
     * ✅ Índice mejorado - Todos los modelos automáticamente
     */
    public function index(Request $request, string $model = null)
    {
        $modelTypes = $this->getAvailableModelTypes();
        $users = $this->getActiveUsers();

        return view('humano-version-control::audit-trail.index', compact(
            'model',
            'modelTypes',
            'users'
        ));
    }

    /**
     * Mantener para compatibilidad - pero mejorado
     */
    public function show(Request $request, string $model, int $id)
    {
        $modelClass = $this->resolveModelClass($model);

        if (!$modelClass) {
            abort(404, 'Model type not found');
        }

        $subject = $modelClass::find($id);
        if (!$subject) {
            abort(404, 'Record not found');
        }

        $activities = Activity::forSubject($subject)
            ->with('causer')
            ->latest()
            ->paginate(20);

        $model = $this->getModelDisplayName($modelClass);
        $modelSlug = strtolower(class_basename($modelClass));

        return view('humano-version-control::audit-trail.show', compact(
            'subject',
            'activities',
            'model',
            'modelSlug'
        ));
    }

    public function versions(Request $request, string $model, int $id)
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
            ->get();

        return view('humano-version-control::audit-trail.versions', compact(
            'subject',
            'activities',
            'model'
        ));
    }

    public function userActivity(Request $request, int $user)
    {
        $userModel = $this->getUserModel()::find($user);

        if (!$userModel) {
            abort(404, 'User not found');
        }

        $activities = Activity::causedBy($userModel)
            ->with('subject')
            ->latest()
            ->paginate(20);

        return view('humano-version-control::audit-trail.user-activity', compact(
            'userModel',
            'activities'
        ));
    }

    public function compare(Request $request)
    {
        $activityId1 = $request->get('activity1');
        $activityId2 = $request->get('activity2');

        if (!$activityId1 || !$activityId2) {
            return back()->withErrors(['error' => 'Please select two activities to compare']);
        }

        $activity1 = Activity::find($activityId1);
        $activity2 = Activity::find($activityId2);

        if (!$activity1 || !$activity2) {
            return back()->withErrors(['error' => 'Activities not found']);
        }

        $differences = $this->compareActivities($activity1, $activity2);

        return view('humano-version-control::audit-trail.compare', compact(
            'activity1',
            'activity2',
            'differences'
        ));
    }

    /**
     * ✅ DataTables API mejorada - Completamente dinámica
     */
    public function activities(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        // Aplicar filtros dinámicos
        if ($request->filled('model')) {
            // Buscar por clase completa o basename
            $modelClass = $this->resolveModelClass($request->model);
            if ($modelClass) {
                $query->where('subject_type', $modelClass);
            }
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return DataTables::eloquent($query)
            ->addColumn('model_name', function ($activity) {
                return $this->getModelDisplayName($activity->subject_type);
            })
            ->addColumn('subject_name', function ($activity) {
                if ($activity->subject) {
                    return $activity->subject->name ??
                           $activity->subject->title ??
                           $activity->subject->email ??
                           "#{$activity->subject_id}";
                }
                return 'Deleted';
            })
            ->addColumn('causer_name', function ($activity) {
                return $activity->causer ? $activity->causer->name : 'System';
            })
            ->addColumn('changes_summary', function ($activity) {
                $properties = $activity->properties;
                if (isset($properties['attributes']) && isset($properties['old'])) {
                    $changed = array_keys($properties['attributes']);
                    return implode(', ', array_slice($changed, 0, 3)) .
                           (count($changed) > 3 ? '...' : '');
                }
                return 'N/A';
            })
            ->addColumn('actions', function ($activity) {
                $actions = '';

                // ✅ ICONO SIN ESTILO DE BOTÓN - Acceso directo por Activity ID
                $actions .= '<a href="' . route('version-control.activity.show', $activity->id) . '"
                            class="text-body" title="Ver Actividad">
                            <i class="ti ti-eye ti-sm me-2"></i>
                            </a>';

                return $actions;
            })
            ->editColumn('created_at', function ($activity) {
                return $activity->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * ✅ Obtener tipos de modelos dinámicamente desde Activity log
     */
    private function getAvailableModelTypes(): array
    {
        return Activity::distinct('subject_type')
            ->pluck('subject_type')
            ->mapWithKeys(function ($type) {
                $displayName = $this->getModelDisplayName($type);
                $slug = strtolower(class_basename($type));
                return [$slug => $displayName];
            })
            ->toArray();
    }

    /**
     * ✅ Obtener usuarios activos dinámicamente
     */
    private function getActiveUsers()
    {
        return Activity::with('causer')
            ->whereNotNull('causer_id')
            ->distinct('causer_id')
            ->get()
            ->pluck('causer.name', 'causer.id')
            ->filter()
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

    /**
     * ✅ Obtener modelo de usuario dinámicamente
     */
    private function getUserModel(): string
    {
        return config('humano-version-control.user_model', '\\App\\Models\\User');
    }

    /**
     * Comparar actividades
     */
    private function compareActivities(Activity $activity1, Activity $activity2): array
    {
        $attributes1 = $activity1->properties->get('attributes', []);
        $attributes2 = $activity2->properties->get('attributes', []);

        $differences = [];
        $allKeys = array_unique(array_merge(array_keys($attributes1), array_keys($attributes2)));

        foreach ($allKeys as $key) {
            $value1 = $attributes1[$key] ?? null;
            $value2 = $attributes2[$key] ?? null;

            if ($value1 !== $value2) {
                $differences[$key] = [
                    'activity1' => $value1,
                    'activity2' => $value2,
                ];
            }
        }

        return $differences;
    }
}