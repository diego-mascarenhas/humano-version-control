<?php

namespace Idoneo\HumanoVersionControl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class AuditTrailController extends Controller
{
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

    public function show(Request $request, string $model, int $id)
    {
        $modelClass = $this->getModelClass($model);

        if (!$modelClass || !$modelClass::find($id)) {
            abort(404, 'Model not found');
        }

        $subject = $modelClass::find($id);
        $activities = Activity::forSubject($subject)
            ->with('causer')
            ->latest()
            ->paginate(20);

        return view('humano-version-control::audit-trail.show', compact(
            'subject',
            'activities',
            'model'
        ));
    }

    public function versions(Request $request, string $model, int $id)
    {
        $modelClass = $this->getModelClass($model);

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
        $userModel = \App\Models\User::find($user);

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

    public function activities(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        // Apply filters
        if ($request->filled('model')) {
            $modelClass = $this->getModelClass($request->model);
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
                return class_basename($activity->subject_type);
            })
            ->addColumn('subject_name', function ($activity) {
                if ($activity->subject) {
                    return $activity->subject->name ?? $activity->subject->title ?? "#{$activity->subject_id}";
                }
                return 'Deleted';
            })
            ->addColumn('causer_name', function ($activity) {
                return $activity->causer ? $activity->causer->name : 'System';
            })
            ->addColumn('changes_summary', function ($activity) {
                $changes = $activity->properties->get('attributes', []);
                return count($changes) . ' field(s) changed';
            })
            ->addColumn('actions', function ($activity) {
                return view('humano-version-control::partials.activity-actions', compact('activity'));
            })
            ->editColumn('created_at', function ($activity) {
                return $activity->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    private function getAvailableModelTypes(): array
    {
        return Activity::distinct('subject_type')
            ->pluck('subject_type')
            ->mapWithKeys(function ($type) {
                return [$type => class_basename($type)];
            })
            ->toArray();
    }

    private function getActiveUsers(): array
    {
        return Activity::with('causer')
            ->whereNotNull('causer_id')
            ->distinct('causer_id')
            ->get()
            ->pluck('causer.name', 'causer.id')
            ->toArray();
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
