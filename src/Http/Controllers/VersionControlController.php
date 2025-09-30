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

    public function getVersions(Request $request, string $model, int $id)
    {
        $modelClass = $this->getModelClass($model);

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $activities = Activity::forSubject($modelClass::find($id))
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

    private function getModelStats(): array
    {
        return Activity::select('subject_type', DB::raw('count(*) as total'))
            ->groupBy('subject_type')
            ->orderBy('total', 'desc')
            ->take(10)
            ->pluck('total', 'subject_type')
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
}
