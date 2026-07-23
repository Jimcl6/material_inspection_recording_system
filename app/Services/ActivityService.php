<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ActivityService
{
    private const HIDDEN_DETAIL_FIELDS = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'remember_token',
    ];

    private const SENSITIVE_FIELD_PARTS = [
        'password',
        'token',
        'secret',
        'api_key',
        'qr_data',
    ];

    private const FIELD_LABELS = [
        'previous_status' => 'Previous Status',
        'new_status' => 'New Status',
        'rejection_reason' => 'Rejection Reason',
        'source' => 'Source',
        'notes' => 'Notes',
        'ip' => 'IP Address',
        'ip_address' => 'IP Address',
        'user_agent' => 'Device / Browser',
        'item_code' => 'Item Code',
        'item_name' => 'Item Name',
        'item_block_code' => 'Item Block Code',
        'model_series' => 'Model Series',
        'model_code' => 'Model Code',
        'machine_no' => 'Machine Number',
        'machine_number' => 'Machine Number',
        'control_no' => 'Control Number',
        'equipment_type' => 'Equipment Type',
        'line_assigned' => 'Line Assigned',
        'process_assigned' => 'Process Assigned',
        'person_in_charge' => 'Person In Charge',
        'date' => 'Date',
        'production_date' => 'Production Date',
        'month' => 'Month',
        'year' => 'Year',
        'time_am' => 'AM Time',
        'temp_am' => 'AM Temperature',
        'time_pm' => 'PM Time',
        'temp_pm' => 'PM Temperature',
        'torque_am' => 'AM Torque',
        'torque_pm' => 'PM Torque',
        'checked_by' => 'Checked By',
        'checked_by_id' => 'Checked By',
        'verified_by_id' => 'Verified By',
        'pic_id' => 'PIC',
        'employee_id' => 'Employee ID',
        'employment_status' => 'Employment Status',
        'hire_date' => 'Hire Date',
        'contract_end_date' => 'Contract End Date',
        'department_id' => 'Department',
        'position_id' => 'Position',
        'role_id' => 'Role',
        'is_active' => 'Active',
        'imported' => 'Imported',
        'updated' => 'Updated',
        'skipped' => 'Skipped',
        'errors' => 'Errors',
        'count' => 'Total Records',
        'deleted_count' => 'Deleted Records',
        'material_lot_number' => 'Material Lot Number',
        'qr_code' => 'QR Code',
        'produce_qty' => 'Produced Quantity',
        'job_number' => 'Job Number',
        'remarks' => 'Remarks',
    ];

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
            $activity['subject_id'] = $subject->getKey();
        } else {
            // For activities without a subject (like login)
            $activity['subject_type'] = '';
            $activity['subject_id'] = null;
        }

        Activity::create($activity);
    }

    /**
     * Build a user-facing display payload for activity log details.
     */
    public static function formatForDisplay(Activity $activity): array
    {
        $properties = $activity->properties ?? [];
        $changes = self::extractDisplayChanges($activity, $properties);
        $details = self::extractDisplayDetails($activity, $properties);
        $recordedDetails = self::extractRecordedDetails($activity, $properties, ! empty($changes));

        return [
            'summary' => $activity->description,
            'details' => $details,
            'changes' => $changes,
            'recorded_details' => $recordedDetails,
            'has_before_after' => ! empty($changes),
            'recorded_details_title' => $activity->type === 'update' && empty($changes)
                ? 'Saved Details'
                : 'Recorded Details',
        ];
    }

    /**
     * Create before/after properties from two snapshots.
     */
    public static function updatePropertiesFromSnapshot(array $before, array $after, array $properties = []): array
    {
        return array_merge([
            'changes' => self::buildChangeRows($before, $after),
            'recorded_details' => $after,
        ], $properties);
    }

    /**
     * Log an update with before/after values captured by the caller.
     */
    public static function logSnapshotUpdate(
        Model $subject,
        array $before,
        array $after,
        string $description,
        string $module = null,
        array $properties = []
    ): void {
        self::log(
            'update',
            $description,
            $subject,
            self::updatePropertiesFromSnapshot($before, $after, $properties),
            $module
        );
    }

    public static function snapshot(Model $model): array
    {
        return $model->attributesToArray();
    }

    /**
     * Get module name from subject model
     */
    private static function getModuleFromSubject(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        $moduleMap = [
            'App\Models\AnnealingCheck' => 'annealing',
            'App\Models\TempRecord' => 'temperature',
            'App\Models\TorqueRecord' => 'torque',
            'App\Models\ProductionBatch' => 'production_batches',
            'App\Models\MagnetismChecksheet' => 'magnetism',
            'App\Models\MagnetismBatch' => 'magnetism',
            'App\Models\MagnetismCheckpoint' => 'magnetism',
            'App\Models\WeldingChecksheet' => 'welding',
            'App\Models\WeldingChecksheetSample' => 'welding',
            'App\Models\WeldingChecksheetType' => 'welding',
            'App\Models\WeldingItemConfig' => 'welding',
            'App\Models\MaterialPart' => 'material_monitoring',
            'App\Models\ModificationLog' => 'modification_logs',
            'App\Models\InspectionCheckpoint' => 'inspection_checkpoints',
            'App\Models\InspectionSample' => 'inspection_checkpoints',
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
            "Created {$modelName}: ".self::subjectIdentifier($subject),
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
        $changeText = ! empty($changes) ? ' - Updated: '.implode(', ', array_keys($changes)) : '';
        $changeRows = [];

        foreach ($changes as $field => $after) {
            if ($field === 'updated_at') {
                continue;
            }

            $changeRows[] = self::makeChangeRow($field, $subject->getOriginal($field), $after);
        }

        self::log(
            'update',
            "Updated {$modelName}: ".self::subjectIdentifier($subject).$changeText,
            $subject,
            array_merge(['changes' => $changeRows], $properties)
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
            "Deleted {$modelName}: ".self::subjectIdentifier($subject),
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
                return 'Created new record';
            case 'update':
                $changes = $activity->properties['changes'] ?? [];
                if (! empty($changes)) {
                    return 'Modified: '.implode(', ', array_keys($changes));
                }

                return 'Updated record';
            case 'delete':
                return 'Deleted record';
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
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by module
        if (! empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // Filter by type (action)
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search in description
        if (! empty($filters['search'])) {
            $query->where('description', 'like', '%'.$filters['search'].'%');
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
            'production_batches' => 'Production Batches',
            'material_monitoring' => 'Material Monitoring',
            'modification_logs' => 'Modification Logs',
            'inspection_checkpoints' => 'Inspection Checkpoints',
            'diaphragm_welding' => 'Diaphragm Welding',
            'welding' => 'Welding Checksheet',
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
        $identifier = self::subjectIdentifier($subject);
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
        $identifier = self::subjectIdentifier($subject);
        self::log(
            'reject',
            "Rejected {$modelName}: {$identifier}".($reason ? " - Reason: {$reason}" : ''),
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

    private static function buildChangeRows(array $before, array $after): array
    {
        $rows = [];
        $fields = array_unique(array_merge(array_keys($before), array_keys($after)));

        foreach ($fields as $field) {
            if (self::shouldHideField($field)) {
                continue;
            }

            $beforeValue = $before[$field] ?? null;
            $afterValue = $after[$field] ?? null;

            if (self::valuesAreEqual($beforeValue, $afterValue)) {
                continue;
            }

            $rows[] = self::makeChangeRow($field, $beforeValue, $afterValue);
        }

        return $rows;
    }

    private static function makeChangeRow(string $field, $before, $after): array
    {
        return [
            'field' => $field,
            'label' => self::fieldLabel($field),
            'before' => self::formatValue($field, $before),
            'after' => self::formatValue($field, $after),
        ];
    }

    private static function extractDisplayChanges(Activity $activity, array $properties): array
    {
        if (array_key_exists('previous_status', $properties) || array_key_exists('new_status', $properties)) {
            return [
                self::makeChangeRow(
                    'status',
                    $properties['previous_status'] ?? null,
                    $properties['new_status'] ?? null
                ),
            ];
        }

        if (isset($properties['old_data'], $properties['new_data'])
            && is_array($properties['old_data'])
            && is_array($properties['new_data'])) {
            $before = $properties['old_data'];
            $after = $properties['new_data'];

            if (isset($properties['old_permissions'], $properties['new_permissions'])) {
                $before['permissions'] = $properties['old_permissions'];
                $after['permissions'] = $properties['new_permissions'];
            }

            return self::buildChangeRows($before, $after);
        }

        if (isset($properties['old_permissions'], $properties['new_permissions'])) {
            return [
                self::makeChangeRow('permissions', $properties['old_permissions'], $properties['new_permissions']),
            ];
        }

        $changes = $properties['changes'] ?? [];

        if (! is_array($changes) || empty($changes)) {
            return [];
        }

        if (self::arrayIsList($changes)) {
            return collect($changes)
                ->filter(fn ($change) => is_array($change) && isset($change['label']))
                ->map(fn ($change) => [
                    'field' => $change['field'] ?? null,
                    'label' => $change['label'],
                    'before' => $change['before'] ?? self::formatValue($change['field'] ?? '', null),
                    'after' => $change['after'] ?? self::formatValue($change['field'] ?? '', null),
                ])
                ->values()
                ->all();
        }

        return [];
    }

    private static function extractDisplayDetails(Activity $activity, array $properties): array
    {
        $details = [];

        if ($activity->ip_address) {
            $details[] = self::detailRow('IP Address', $activity->ip_address);
        } elseif (isset($properties['ip'])) {
            $details[] = self::detailRow('IP Address', $properties['ip']);
        }

        $knownDetailFields = ['notes', 'rejection_reason', 'source', 'count', 'deleted_count'];

        foreach ($knownDetailFields as $field) {
            if (array_key_exists($field, $properties)) {
                $details[] = self::detailRow(self::fieldLabel($field), self::formatValue($field, $properties[$field]));
            }
        }

        return $details;
    }

    private static function extractRecordedDetails(Activity $activity, array $properties, bool $hasChanges): array
    {
        if (empty($properties)) {
            return [];
        }

        if (isset($properties['recorded_details']) && is_array($properties['recorded_details'])) {
            return self::detailRowsFromArray($properties['recorded_details']);
        }

        if (isset($properties['subject_data']) && is_array($properties['subject_data'])) {
            return self::detailRowsFromArray($properties['subject_data']);
        }

        if (isset($properties['deleted_activity']) && is_array($properties['deleted_activity'])) {
            return self::detailRowsFromArray($properties['deleted_activity']);
        }

        $skip = ['changes', 'previous_status', 'new_status', 'notes', 'rejection_reason', 'source', 'ip'];

        if ($hasChanges) {
            $skip = array_merge($skip, [
                'recorded_details',
                'old_data',
                'new_data',
                'old_permissions',
                'new_permissions',
            ]);
        }

        return self::detailRowsFromArray(
            collect($properties)
                ->reject(fn ($value, $key) => in_array($key, $skip, true))
                ->all()
        );
    }

    private static function detailRowsFromArray(array $values): array
    {
        $rows = [];

        foreach ($values as $field => $value) {
            if (self::shouldHideField((string) $field)) {
                continue;
            }

            $rows[] = self::detailRow(self::fieldLabel((string) $field), self::formatValue((string) $field, $value));
        }

        return $rows;
    }

    private static function detailRow(string $label, string $value): array
    {
        return [
            'label' => $label,
            'value' => $value,
        ];
    }

    private static function shouldHideField(string $field): bool
    {
        if (in_array($field, self::HIDDEN_DETAIL_FIELDS, true)) {
            return true;
        }

        return str_ends_with($field, '_id') && $field !== 'employee_id';
    }

    private static function fieldLabel(string $field): string
    {
        if ($field === 'status') {
            return 'Status';
        }

        return self::FIELD_LABELS[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    private static function formatValue(string $field, $value): string
    {
        if (self::isSensitiveField($field)) {
            return '[Hidden]';
        }

        if ($value === null || $value === '') {
            return 'Blank';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return 'None';
            }

            if (self::arrayIsList($value)) {
                $preview = array_slice($value, 0, 5);
                $formatted = array_map(fn ($item) => is_scalar($item) ? (string) $item : 'Recorded item', $preview);
                $suffix = count($value) > 5 ? ' +'.(count($value) - 5).' more' : '';

                return implode(', ', $formatted).$suffix;
            }

            return count($value).' details recorded';
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d H:i:s');
        }

        if (is_string($value) && self::looksLikeDate($field, $value)) {
            try {
                return Carbon::parse($value)->format(str_contains($value, ':') ? 'Y-m-d H:i:s' : 'Y-m-d');
            } catch (\Throwable $e) {
                return $value;
            }
        }

        return (string) $value;
    }

    private static function isSensitiveField(string $field): bool
    {
        foreach (self::SENSITIVE_FIELD_PARTS as $part) {
            if (str_contains($field, $part)) {
                return true;
            }
        }

        return false;
    }

    private static function looksLikeDate(string $field, string $value): bool
    {
        if (! preg_match('/date|_at$/', $field)) {
            return false;
        }

        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}/', $value);
    }

    private static function valuesAreEqual($before, $after): bool
    {
        return json_encode($before) === json_encode($after);
    }

    private static function subjectIdentifier(Model $subject): string
    {
        $identifier = $subject->getAttribute('identifier');
        if (is_scalar($identifier) && (string) $identifier !== '') {
            return (string) $identifier;
        }

        $key = $subject->getKey();

        return $key === null ? 'Record' : '# '.$key;
    }

    private static function arrayIsList(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }
}
