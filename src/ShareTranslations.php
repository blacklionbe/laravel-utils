<?php

namespace BlackLion\LaravelUtils;

use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\Finder\Finder;

class ShareTranslations
{
    /**
     * Handle the event.
     *
     * @param LocaleUpdated $event
     */
    public function handle(LocaleUpdated $event)
    {
        $this->update();
    }

    public function update()
    {
        $finder = new Finder();
        $path = resource_path('lang/'.App::getLocale());

        if (! is_dir($path) && function_exists('lang_path')) {
            $path = lang_path(App::getLocale());
        }

        if (! is_dir($path)) {
            return;
        }

        $translations = collect($finder->files()->in($path))->mapWithKeys(function ($file) {
            $nameWithoutExtension = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return [$nameWithoutExtension => require $file->getRealPath()];
        });

        View::share('translations', $translations);
    }
}
