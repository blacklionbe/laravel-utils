<?php

namespace BlackLion\LaravelUtils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class LanguageDetector
{
    protected $languages;
    protected $system;

    public function __construct($languages, $system = [])
    {
        $this->languages = $languages;
        $this->system = $system;

        if (array_keys($languages) !== range(0, count($languages) - 1)) {
            $this->languages = array_keys($languages);
        }
    }

    public function redirect()
    {
        $language = request()->getPreferredLanguage($this->languages);

        return function () use ($language) {
            return redirect('/'.$language, 301);
        };
    }

    public function detect()
    {
        $language = request()->segment(1);

        if (in_array($language, $this->languages)) {
            App::setLocale($language);
        } elseif (! $this->isSystemPath($language)) {
            abort(404);
        }

        return $language;
    }

    protected function isSystemPath($path)
    {
        return $this->getSystemPaths()
            ->some(function ($prefix) use ($path) {
                return Str::startsWith(Str::start($path, '/'), Str::start($prefix, '/'));
            });
    }

    protected function getSystemPaths()
    {
        return collect(array_merge(
            ['tinker', 'nova', 'vendor', 'admin', 'horizon', 'sitemap', 'robots'],
            $this->system
        ));
    }
}
