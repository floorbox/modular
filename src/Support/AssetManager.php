<?php

namespace InterNACHI\Modular\Support;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use InterNACHI\Modular\Support\Facades\Modules;

class AssetManager
{
    static function boot()
    {
        $instance = new static;
        $instance->registerBladeDirectives();
    }

    public function registerBladeDirectives()
    {
        Blade::directive('viteModule', function ($module) {
            return <<<PHP
            {!! app('modules')->assets($module) !!}
            PHP;
        });
    }

    public static function scripts(string $module)
    {
        if (!Modules::module($module)) {
            return '';
        }

        $file = base_path("app-modules/$module/resources/js/module.js");

        if (!File::exists($file)) {
            return '';
        }

        return $file;

    }

    public static function styles(string $module)
    {
        if (!Modules::module($module)) {
            return '';
        }

        $file = base_path("app-modules/$module/resources/css/module.css");

        if (!File::exists($file)) {
            return '';
        }

        return $file;
    }

    public static function assets(string $module)
    {
        $paths = [
            static::scripts($module),
            static::styles($module),
            'resources/js/app.js',
            'resources/css/app.css',
        ];

        $entryPoints = collect($paths)->filter()->map(fn ($path) => str($path)->after(base_path() . '/')->toString())->toArray();

        return \Illuminate\Support\Facades\Vite::withEntryPoints($entryPoints)->toHtml();
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
