<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::get('/uploads/{folder}/{filename}', function ($folder, $filename) {
            $path = base_path("uploads/{$folder}/{$filename}");

            if (!file_exists($path)) {
                abort(404, 'File not found');
            }

            $mimeType = mime_content_type($path);
            $headers = [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ];

            return response()->file($path, $headers);
        })->where(['folder' => '[a-zA-Z0-9_-]+', 'filename' => '[a-zA-Z0-9._-]+']);

        Route::get('/Modules/{module}/Resources/assets/{path}', function ($module, $path) {
            $fullPath = base_path("Modules/{$module}/Resources/assets/{$path}");

            if (!file_exists($fullPath)) {
                abort(404, 'Asset not found');
            }
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            $mimeType = match ($extension) {
                'js' => 'application/javascript',
                'css' => 'text/css',
                'scss', 'sass' => 'text/css',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'svg' => 'image/svg+xml',
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'ico' => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                default => mime_content_type($fullPath) ?: 'application/octet-stream'
            };

            $headers = [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ];

            return response()->file($fullPath, $headers);
        })->where([
            'module' => '[a-zA-Z0-9_-]+',
            'path' => '.*'
        ]);
    }
}
