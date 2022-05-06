<?php

namespace Illuminate\Foundation;

use Exception;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Vite
{
    /**
     * Generate Vite tags for an entrypoint.
     *
     * @param  string|string[]  $entrypoints
     * @param  string  $buildDirectory
     * @return \Illuminate\Support\HtmlString
     *
     * @throws \Exception
     */
    public function __invoke($entrypoints, $buildDirectory = 'build')
    {
        static $manifests = [];

        $entrypoints = collect($entrypoints);
        $buildDirectory = Str::start($buildDirectory, '/');

        if (is_file(public_path('/hot'))) {
            $url = rtrim(file_get_contents(public_path('/hot')));

            return new HtmlString(
                $entrypoints
                    ->map(fn ($entrypoint) => $this->makeScriptTag("{$url}/{$entrypoint}"))
                    ->prepend($this->makeScriptTag("{$url}/@vite/client"))
                    ->join('')
            );
        }

        $manifestPath = public_path($buildDirectory.'/manifest.json');

        if (! isset($manifests[$manifestPath])) {
            if (! is_file($manifestPath)) {
                throw new Exception("Vite manifest not found at: {$manifestPath}");
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        $scripts = collect();
        $stylesheets = collect();

        foreach ($entrypoints as $entrypoint) {
            if (! isset($manifest[$entrypoint])) {
                throw new Exception("Unable to locate file in Vite manifest: {$entrypoint}.");
            }

            $scripts->push(
                $this->makeScriptTag("{$buildDirectory}/{$manifest[$entrypoint]['file']}")
            );

            if (isset($manifest[$entrypoint]['css'])) {
                foreach ($manifest[$entrypoint]['css'] as $css) {
                    $stylesheets->push($this->makeStylesheetTag("{$buildDirectory}/{$css}"));
                }
            }
        }

        return new HtmlString($stylesheets->join('').$scripts->join(''));
    }

    /**
     * Generate a script tag for the given URL.
     *
     * @param  string  $url
     * @return string
     */
    protected function makeScriptTag($url)
    {
        return sprintf('<script type="module" src="%s"></script>', $url);
    }

    /**
     * Generate a stylesheet tag for the given URL.
     *
     * @param  string  $url
     * @return string
     */
    protected function makeStylesheetTag($url)
    {
        return sprintf('<link rel="stylesheet" href="%s" />', $url);
    }
}
