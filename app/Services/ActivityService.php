<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityService
{
    /**
     * Log an activity.
     */
    public static function log(string $type, string $description, Model $subject = null, array $properties = [], string $module = null)
    {
        $activity = [
            'user_id' => auth()->id(),
            'type' => $type,
            'module' => $module ?? self::getModuleFromSubject($subject),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($subject) {
            $activity['subject_type'] = get_class($subject);
            $activity['subject_id'] = $subject->id;
        } else {
            // For activities without a subject (like login)
            $activity['subject_type'] = '';
            $activity['subject_id'] = null;
        }

        Activity::create($activity);
    }

    /**
     * Get module name from subject model
     */
    private static function getModuleFromSubject(?Model $subject): ?string
    {
        if (!$subject) {
            return null;
        }

        $moduleMap = [
            'App\Models\AnnealingCheck' => 'annealing',
            'App\Models\TempRecord' => 'temperature',
            'App\Models\TorqueRecord' => 'torque',
            'App\Models\ProductionBatch' => 'magnetism',
            'App\Models\MagnetismChecksheet' => 'magnetism',
            'App\Models\MagnetismBatch' => 'magnetism',
            'App\Models\MagnetismCheckpoint' => 'magnetism',
            'App\Models\User' => 'users',
            'App\Models\Department' => 'departments',
            'App\Models\Position' => 'positions',
            'App\Models\Role' => 'roles',
        ];

        $className = get_class($subject);
        return $moduleMap[$className] ?? strtolower(class_basename($subject));
    }

    /**
     * Log a create activity.
     */
    public static function logCreate(Model $subject, array $properties = [])
    {
        $modelName = class_basename($subject);
        self::log(
            'create',
            "Created {$modelName}: {$subject->getIdentifierAttribute()}",
            $subject,
            $properties
        );
    }

    /**
     * Log an update activity.
     */
    public static function logUpdate(Model $subject, array $changes = [], array $properties = [])
    {
        $modelName = class_basename($subject);
        $changeText = !empty($changes) ? ' - Updated: ' . implode(', ', array_keys($changes)) : '';
        self::log(
            'update',
            "Updated {$modelName}: {$subject->getIdentifierAttribute()}{$changeText}",
            $subject,
            array_merge(['changes' => $changes], $properties)
        );
    }

    /**
     * Log a delete activity.
     */
    public static function logDelete(Model $subject, array $properties = [])
    {
        $modelName = class_basename($subject);
        self::log(
            'delete',
            "Deleted {$modelName}: {$subject->getIdentifierAttribute()}",
            null,
            array_merge(['subject_data' => $subject->toArray()], $properties)
        );
    }

    /**
     * Log a login activity.
     */
    public static function logLogin()
    {
        $user = auth()->user();
        self::log(
            'login',
            'Logged in to the system',
            null,
            ['ip' => request()->ip()]
        );
    }

    /**
     * Get today's activities for the current user.
     */
    public static function getTodayActivitiesForUser(int $limit = 10)
    {
        return Activity::with('user')
            ->forUser(auth()->id())
            ->whereDate('created_at', today())
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user->name,
                    'user_initials' => $activity->user_initials,
                    'action' => $activity->description,
                    'time_ago' => $activity->time_ago,
                    'type' => $activity->type,
                    'details' => self::getActivityDetails($activity),
                ];
            });
    }

    /**
     * Get recent activities for the current user.
     */
    public static function getRecentForUser(int $limit = 10)
    {
        return Activity::with('user')
            ->forUser(auth()->id())
            ->recent()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user->name,
                    'user_initials' => $activity->user_initials,
                    'action' => $activity->description,
                    'time_ago' => $activity->time_ago,
                    'type' => $activity->type,
                    'details' => self::getActivityDetails($activity),
                ];
            });
    }

    /**
     * Get formatted activity details.
     */
    private static function getActivityDetails(Activity $activity): string
    {
        switch ($activity->type) {
            case 'create':
                return "Created new record";
            case 'update':
                $changes = $activity->properties['changes'] ?? [];
                if (!empty($changes)) {
                    return "Modified: " . implode(', ', array_keys($changes));
                }
                return "Updated record";
            case 'delete':
                return "Deleted record";
            case 'login':
                $ip = $activity->properties['ip'] ?? 'Unknown';
                return "Login from IP: {$ip}";
            default:
                return $activity->description;
        }
    }

    /**
     * Get all activities for admin viewing with filters.
     */
    public static function getAllActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::with('user');

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by module
        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // Filter by type (action)
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search in description
        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Get available modules for filtering.
     */
    public static function getAvailableModules(): array
    {
        return [
            'annealing' => 'Annealing Checks',
            'magnetism' => 'Magnetism Checksheet',
            'temperature' => 'Temperature Records',
            'torque' => 'Torque Records',
            'users' => 'User Management',
            'departments' => 'Departments',
            'positions' => 'Positions',
            'roles' => 'Roles',
            'auth' => 'Authentication',
        ];
    }

    /**
     * Get available action types for filtering.
     */
    public static function getAvailableTypes(): array
    {
        return [
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'login' => 'Login',
            'logout' => 'Logout',
            'approve' => 'Approve',
            'reject' => 'Reject',
            'import' => 'Import',
            'export' => 'Export',
        ];
    }

    /**
     * Delete a single activity.
     */
    public static function deleteActivity(int $activityId): bool
    {
        $activity = Activity::find($activityId);
        if ($activity) {
            return $activity->delete();
        }
        return false;
    }

    /**
     * Bulk delete activities.
     */
    public static function bulkDelete(array $activityIds): int
    {
        return Activity::whereIn('id', $activityIds)->delete();
    }

    /**
     * Log an approval activity.
     */
    public static function logApprove(Model $subject, array $properties = [])
    {
        $modelName = class_basename($subject);
        $identifier = method_exists($subject, 'getIdentifierAttribute') 
            ? $subject->getIdentifierAttribute() 
            : $subject->id;
        self::log(
            'approve',
            "Approved {$modelName}: {$identifier}",
            $subject,
            $properties
        );
    }

    /**
     * Log a rejection activity.
     */
    public static function logReject(Model $subject, string $reason = '', array $properties = [])
    {
        $modelName = class_basename($subject);
        $identifier = method_exists($subject, 'getIdentifierAttribute') 
            ? $subject->getIdentifierAttribute() 
            : $subject->id;
        self::log(
            'reject',
            "Rejected {$modelName}: {$identifier}" . ($reason ? " - Reason: {$reason}" : ''),
            $subject,
            array_merge(['rejection_reason' => $reason], $properties)
        );
    }

    /**
     * Log an import activity.
     */
    public static function logImport(string $module, int $count, array $properties = [])
    {
        self::log(
            'import',
            "Imported {$count} records to {$module}",
            null,
            array_merge(['count' => $count], $properties),
            $module
        );
    }

    /**
     * Log an export activity.
     */
    public static function logExport(string $module, int $count, array $properties = [])
    {
        self::log(
            'export',
            "Exported {$count} records from {$module}",
            null,
            array_merge(['count' => $count], $properties),
            $module
        );
    }
}
