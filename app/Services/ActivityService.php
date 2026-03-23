<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    /**
     * Log an activity.
     */
    public static function log(string $type, string $description, Model $subject = null, array $properties = [])
    {
        $activity = [
            'user_id' => auth()->id(),
            'type' => $type,
            'description' => $description,
            'properties' => $properties,
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
}
