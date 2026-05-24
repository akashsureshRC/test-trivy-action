<?php

namespace App\Providers;

use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerHrmEmployeeWorkspaceScopes();

        // Use Bootstrap 5 for pagination
        Paginator::useBootstrapFive();

        // Force HTTPS URLs when behind a proxy (like ngrok)
        // This ensures asset() helper generates HTTPS URLs
        if (request()->header('X-Forwarded-Proto') === 'https' || 
            request()->header('X-Forwarded-Ssl') === 'on' ||
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }

        // For production environments, always use HTTPS
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }

    protected function registerHrmEmployeeWorkspaceScopes(): void
    {
        $modelsPath = app_path('Models/Hrm');

        if (!is_dir($modelsPath)) {
            return;
        }

        foreach (File::files($modelsPath) as $file) {
            $class = 'App\\Models\\Hrm\\' . pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (!class_exists($class) || $class === Employee::class || !is_subclass_of($class, Model::class)) {
                continue;
            }

            try {
                $model = new $class();
                $table = $model->getTable();

                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'employee_id')) {
                    continue;
                }

                $class::addGlobalScope('workspace_employee', function (Builder $builder) {
                    $table = $builder->getModel()->getTable();
                    $builder->whereIn($table . '.employee_id', Employee::query()->select('id'));
                });
            } catch (\Throwable $e) {
                continue;
            }
        }
    }
}
