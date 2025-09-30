<?php

namespace Idoneo\HumanoVersionControl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RestorationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$this->canPerformRestoration()) {
                abort(403, 'Insufficient permissions for restoration operations');
            }
            return $next($request);
        });
    }

    public function preview(Request $request, string $model, int $id, int $version)
    {
        $modelClass = $this->getModelClass($model);

        if (!$modelClass) {
            abort(404, 'Invalid model');
        }

        $subject = $modelClass::find($id);
        if (!$subject) {
            abort(404, 'Record not found');
        }

        $activity = Activity::find($version);
        if (!$activity || $activity->subject_id !== $id || $activity->subject_type !== $modelClass) {
            abort(404, 'Version not found');
        }

        $currentData = $subject->toArray();
        $versionData = $activity->properties->get('attributes', []);
        $differences = $this->calculateDifferences($currentData, $versionData);

        return view('humano-version-control::restoration.preview', compact(
            'subject',
            'activity',
            'model',
            'currentData',
            'versionData',
            'differences'
        ));
    }

    public function restore(Request $request, string $model, int $id, int $version)
    {
        $modelClass = $this->getModelClass($model);

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $subject = $modelClass::find($id);
        if (!$subject) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $activity = Activity::find($version);
        if (!$activity || $activity->subject_id !== $id || $activity->subject_type !== $modelClass) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        try {
            DB::beginTransaction();

            $versionData = $activity->properties->get('attributes', []);
            $fieldsToRestore = $request->get('fields', array_keys($versionData));

            // Store current state before restoration
            $currentData = $subject->getOriginal();

            // Apply restoration
            foreach ($fieldsToRestore as $field) {
                if (array_key_exists($field, $versionData)) {
                    $subject->$field = $versionData[$field];
                }
            }

            $subject->save();

            // Log the restoration activity
            activity('restored')
                ->performedOn($subject)
                ->withProperties([
                    'restored_from_version' => $version,
                    'restored_fields' => $fieldsToRestore,
                    'previous_state' => $currentData,
                    'restored_state' => array_intersect_key($versionData, array_flip($fieldsToRestore)),
                ])
                ->log('Record restored from version ' . $activity->created_at->format('Y-m-d H:i:s'));

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Record restored successfully',
                    'restored_fields' => count($fieldsToRestore),
                ]);
            }

            return redirect()->back()->with('success', 'Record restored successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Restoration failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Restoration failed: ' . $e->getMessage());
        }
    }

    public function restoreField(Request $request, string $model, int $id, string $field, int $version)
    {
        $modelClass = $this->getModelClass($model);

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $subject = $modelClass::find($id);
        if (!$subject) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $activity = Activity::find($version);
        if (!$activity || $activity->subject_id !== $id || $activity->subject_type !== $modelClass) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        try {
            DB::beginTransaction();

            $versionData = $activity->properties->get('attributes', []);

            if (!array_key_exists($field, $versionData)) {
                return response()->json(['error' => 'Field not found in version'], 404);
            }

            $previousValue = $subject->$field;
            $subject->$field = $versionData[$field];
            $subject->save();

            // Log the field restoration
            activity('field_restored')
                ->performedOn($subject)
                ->withProperties([
                    'restored_field' => $field,
                    'restored_from_version' => $version,
                    'previous_value' => $previousValue,
                    'restored_value' => $versionData[$field],
                ])
                ->log("Field '{$field}' restored from version " . $activity->created_at->format('Y-m-d H:i:s'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' restored successfully",
                'new_value' => $versionData[$field],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Field restoration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function canPerformRestoration(): bool
    {
        $authorizedRoles = config('humano-version-control.restoration.authorized_roles', ['admin']);

        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        foreach ($authorizedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    private function getModelClass(string $model): ?string
    {
        $modelMap = [
            'contact' => \App\Models\Contact::class,
            'project' => \App\Models\Project::class,
            'message' => \App\Models\Message::class,
            'user' => \App\Models\User::class,
            'team' => \App\Models\Team::class,
        ];

        return $modelMap[$model] ?? null;
    }

    private function calculateDifferences(array $current, array $version): array
    {
        $differences = [];
        $allKeys = array_unique(array_merge(array_keys($current), array_keys($version)));

        foreach ($allKeys as $key) {
            $currentValue = $current[$key] ?? null;
            $versionValue = $version[$key] ?? null;

            if ($currentValue !== $versionValue) {
                $differences[$key] = [
                    'current' => $currentValue,
                    'version' => $versionValue,
                    'changed' => true,
                ];
            } else {
                $differences[$key] = [
                    'current' => $currentValue,
                    'version' => $versionValue,
                    'changed' => false,
                ];
            }
        }

        return $differences;
    }
}
