<?php

namespace BlackLion\LaravelUtils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
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
        return function () {
            return redirect('/'.$this->getLanguage(), 301);
        };
    }

    public function detect()
    {
        $language = $this->getLanguage();

        if (in_array($language, $this->languages)) {
            App::setLocale($language);
            Cookie::queue('language', $language, 60 * 24 * 10);
        } else {
            abort(404);
        }

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

        if (count($this->languages) > 0) {
            return $this->languages[0];
        }
    }

    protected function getLanguageFromUrl()
    {
        if (! $this->isSystem($language = request()->segment(1))) {
            return $language;
        }
    }

    protected function getLanguageFromCookie()
    {
        $language = Cookie::get('language');

        if (strlen($language) > 10) {
            return Crypt::decrypt($language, false);
        }

        return $language;
    }

    protected function getLanguageFromBrowser()
    {
        return request()->getPreferredLanguage($this->languages);
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
            ['tinker', 'nova', 'vendor', 'admin', 'horizon', 'sitemap', 'robots', 'ignition', '_ignition'],
            $this->system
        ));
    }
}
