<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
    }

    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'user_id', 'module', 'type', 'date_from', 'date_to']);
        
        $activities = ActivityService::getAllActivities($filters, 20);

        // Transform activities for display
        $activities->getCollection()->transform(function ($activity) {
            return [
                'id' => $activity->id,
                'user' => $activity->user ? [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                ] : null,
                'type' => $activity->type,
                'module' => $activity->module,
                'description' => $activity->description,
                'properties' => $activity->properties,
                'ip_address' => $activity->ip_address,
                'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $activity->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('Admin/ActivityLogs/Index', [
            'activities' => $activities,
            'filters' => $filters,
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
            'modules' => ActivityService::getAvailableModules(),
            'types' => ActivityService::getAvailableTypes(),
        ]);
    }

    /**
     * Remove the specified activity log.
     * Note: Only deletion is allowed, no editing.
     */
    public function destroy(Activity $activity)
    {
        $activityData = [
            'id' => $activity->id,
            'type' => $activity->type,
            'module' => $activity->module,
            'description' => $activity->description,
        ];

        $activity->delete();

        // Log that an admin deleted an activity log
        ActivityService::log(
            'delete',
            "Deleted activity log #{$activityData['id']}",
            null,
            ['deleted_activity' => $activityData],
            'activity_logs'
        );

        return back()->with('success', 'Activity log deleted successfully.');
    }

    /**
     * Bulk delete activity logs.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'activity_ids' => 'required|array',
            'activity_ids.*' => 'exists:activities,id',
        ]);

        $count = ActivityService::bulkDelete($validated['activity_ids']);

        // Log the bulk deletion
        ActivityService::log(
            'delete',
            "Bulk deleted {$count} activity logs",
            null,
            ['deleted_count' => $count, 'deleted_ids' => $validated['activity_ids']],
            'activity_logs'
        );

        return back()->with('success', "{$count} activity logs deleted successfully.");
    }

    /**
     * Show details of a specific activity log.
     */
    public function show(Activity $activity): Response
    {
        $activity->load('user');

        return Inertia::render('Admin/ActivityLogs/Show', [
            'activity' => [
                'id' => $activity->id,
                'user' => $activity->user ? [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                ] : null,
                'type' => $activity->type,
                'module' => $activity->module,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'description' => $activity->description,
                'properties' => $activity->properties,
                'ip_address' => $activity->ip_address,
                'user_agent' => $activity->user_agent,
                'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $activity->created_at->diffForHumans(),
            ],
        ]);
    }
}
