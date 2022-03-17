<?php

namespace BlackLion\LaravelUtils;

use Carbon\CarbonInterval;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class LanguageDetector
{
    protected $languages = [];
    protected $system = [];

    public static function make()
    {
        return new static;
    }

    public function setLanguages(array $languages)
    {
        if (array_keys($languages) !== range(0, count($languages) - 1)) {
            $languages = array_keys($languages);
        }

        $this->languages = $languages;

        return $this;
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function setSystem(array $system)
    {
        $this->system = $system;

        return $this;
    }

    public function getSystem()
    {
        return $this->system;
    }

    public function redirect()
    {
        return function () {
            return redirect('/'.$this->getLanguage(), 301);
        };
    }

    public function detect()
    {
        $language = $this->getLanguage();

        if (! in_array($language, $this->languages)) {
            abort(404);
        }

        App::setLocale($language);
        Cookie::queue('language', $language, CarbonInterval::days(30)->totalMinutes);

        return $language;
    }

    protected function getLanguage()
    {
        if ($language = $this->getLanguageFromUrl()) {
            return $language;
        }

        if ($language = $this->getLanguageFromCookie()) {
            return $language;
        }

        if ($language = $this->getLanguageFromBrowser()) {
            return $language;
        }

        if ($language = $this->getFirstLanguage()) {
            return $language;
        }
    }

    protected function getLanguageFromUrl()
    {
        $language = request()->segment(1);

        if (! $this->isSystem($language)) {
            return $language;
        }
    }

    protected function getLanguageFromCookie()
    {
        $language = Cookie::get('language');

        if (strlen($language) > 10) {
            $language = Crypt::decrypt($language, false);
        }

        if (strpos($language, '|') !== false) {
            $language = explode('|', $language)[1];
        }

        return $language;
    }

    protected function getLanguageFromBrowser()
    {
        return request()->getPreferredLanguage($this->languages);
    }

    protected function getFirstLanguage()
    {
        if (count($this->languages) > 0) {
            return $this->languages[0];
        }
    }

    protected function isSystem($path)
    {
        return $this->getSystemPaths()
            ->some(function ($prefix) use ($path) {
                return Str::startsWith(Str::start($path, '/'), Str::start($prefix, '/'));
            });
    }

    protected function getSystemPaths()
    {
        return collect(array_merge(
            [
                'tinker',
                'nova',
                'vendor',
                'admin',
                'horizon',
                'sitemap',
                'robots',
                'ignition',
                '_ignition',
                'livewire',
                '_debugbar',
            ],
            $this->system
        ));
    }
}
