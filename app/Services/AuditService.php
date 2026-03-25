<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Create an audit log entry.
     *
     * @param string      $action    The action performed (create, update, delete, approve, post, etc.)
     * @param string      $module    The module name (budget, ap_bill, ar_invoice, journal_entry, etc.)
     * @param Model       $record    The model instance being audited
     * @param array|null  $oldValues Previous values (for updates)
     * @param string|null $remarks   Additional remarks
     */
    /**
     * Map common action shorthand to the DB enum values.
     * DB enum: created, updated, deleted, approved, rejected, posted, reversed, voided, closed, reopened
     */
    private const ACTION_MAP = [
        'create'          => 'created',
        'update'          => 'updated',
        'delete'          => 'deleted',
        'approve'         => 'approved',
        'reject'          => 'rejected',
        'post'            => 'posted',
        'reverse'         => 'reversed',
        'void'            => 'voided',
        'close'           => 'closed',
        'reopen'          => 'reopened',
        // Actions that don't have a direct match — map to closest equivalent
        'submit'          => 'updated',
        'submit_approval' => 'updated',
        'return'          => 'rejected',
        'payment'         => 'posted',
    ];

    public function log(
        string $action,
        string $module,
        Model $record,
        ?array $oldValues = null,
        ?string $remarks = null
    ): AuditLog {
        $user = Auth::user();

        // Normalize action to match DB enum
        $dbAction = self::ACTION_MAP[$action] ?? $action;

        $newValues = null;
        if ($oldValues !== null) {
            // Calculate changed fields only
            $currentValues = $record->toArray();
            $changes = [];
            foreach ($currentValues as $key => $value) {
                if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                    $changes[$key] = $value;
                }
            }
            $newValues = !empty($changes) ? $changes : $currentValues;
        }

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $dbAction,
            'module' => $module,
            'record_type' => get_class($record),
            'record_id' => $record->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'remarks' => $remarks,
        ]);
    }
}
