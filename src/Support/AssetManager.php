<?php

namespace InterNACHI\Modular\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Http\Request;
use InterNACHI\Modular\Support\Facades\Modules;

class AssetManager
{
    static function boot()
    {
        $instance = new static;

        $instance->registerAssetDirective();
        $instance->registerAssetRoutes();
    }

    public function registerAssetDirective()
    {
        Blade::directive('moduleStyles', function ($expression) {
            return <<<PHP
            {!! app('modules')->styles($expression) !!}
            PHP;
        });

        Blade::directive('moduleScripts', function ($expression) {
            return <<<PHP
            <?php app('livewire')->forceAssetInjection(); ?>
            {!! app('modules')->scripts($expression) !!}
            PHP;
        });
    }

    public function registerAssetRoutes()
    {
        Route::get('/{module}/pyle.css', function (string $module) {
            return $this->pretendResponseIsFile(base_path("app-modules/$module/resources/css/pyle.css"), 'text/css');
        });

        Route::get('/{module}/pyle.js', function (string $module) {
            return $this->pretendResponseIsFile(base_path("app-modules/$module/dist/pyle.js"), 'text/javascript');
        });

        Route::get('/{module}/pyle.min.js', function (string $module) {
            return $this->pretendResponseIsFile(base_path("app-modules/$module/dist/pyle.min.js"), 'text/javascript');
        });
    }

    public static function scripts(string $module)
    {
        if (!Modules::module($module)) {
            return '';
        }

        $file = base_path("app-modules/$module/dist/manifest.json");

        if (!File::exists($file)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($file), true);

        $versionHash = $manifest['/pyle.js'];

        if (config('app.debug')) {
            return '<script src="/' . $module . '/pyle.js?id=' . $versionHash . '" data-navigate-once></script>';
        } else {
            return '<script src="/' . $module . '/pyle.min.js?id=' . $versionHash . '" data-navigate-once></script>';
        }
    }

    public static function styles(string $module)
    {
        if (!Modules::module($module)) {
            return '';
        }

        $file = base_path("app-modules/$module/dist/manifest.json");

        if (!File::exists($file)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($file), true);

        $versionHash = $manifest['/pyle.js'];

        return '<link rel="stylesheet" href="/' . $module . '/pyle.css">';
    }

    public function pretendResponseIsFile($file, $contentType = 'application/javascript; charset=utf-8')
    {
        $lastModified = filemtime($file);

        return $this->cachedFileResponse($file, $contentType, $lastModified,
            fn ($headers) => response()->file($file, $headers));
    }

    protected function cachedFileResponse($filename, $contentType, $lastModified, $downloadCallback)
    {
        $expires = strtotime('+1 year');
        $cacheControl = 'public, max-age=31536000';

        if ($this->matchesCache($lastModified)) {
            return response('', 304, [
                'Expires' => $this->httpDate($expires),
                'Cache-Control' => $cacheControl,
            ]);
        }

        $headers = [
            'Content-Type' => $contentType,
            'Expires' => $this->httpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => $this->httpDate($lastModified),
        ];

        if (str($filename)->endsWith('.br')) {
            $headers['Content-Encoding'] = 'br';
        }

        return $downloadCallback($headers);
    }

    protected function matchesCache($lastModified)
    {
        $ifModifiedSince = app(Request::class)->header('if-modified-since');

        return $ifModifiedSince !== null && @strtotime($ifModifiedSince) === $lastModified;
    }

    protected function httpDate($timestamp)
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }
}
