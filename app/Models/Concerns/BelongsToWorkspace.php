<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait BelongsToWorkspace
{
    protected static array $workspaceColumnCache = [];

    protected static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope('workspace', function (Builder $builder) {
            $model = $builder->getModel();
            $workspaceColumn = static::resolveWorkspaceColumn($model);

            if (!$workspaceColumn) {
                return;
            }

            $workspaceId = function_exists('getActiveWorkSpace') ? getActiveWorkspace() : null;
            if (empty($workspaceId)) {
                return;
            }

            $builder->where($model->getTable() . '.' . $workspaceColumn, $workspaceId);
        });

        static::creating(function (Model $model) {
            $workspaceColumn = static::resolveWorkspaceColumn($model);
            if (!$workspaceColumn) {
                return;
            }

            $workspaceId = function_exists('getActiveWorkSpace') ? getActiveWorkspace() : null;
            if (empty($workspaceId)) {
                return;
            }

            if (empty($model->{$workspaceColumn})) {
                $model->{$workspaceColumn} = $workspaceId;
            }
        });
    }

    protected static function resolveWorkspaceColumn(Model $model): ?string
    {
        $table = $model->getTable();

        if (array_key_exists($table, static::$workspaceColumnCache)) {
            return static::$workspaceColumnCache[$table];
        }

        if (Schema::hasColumn($table, 'workspace_id')) {
            return static::$workspaceColumnCache[$table] = 'workspace_id';
        }

        if (Schema::hasColumn($table, 'workspace')) {
            return static::$workspaceColumnCache[$table] = 'workspace';
        }

        return static::$workspaceColumnCache[$table] = null;
    }
}
