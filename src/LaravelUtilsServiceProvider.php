<?php

namespace KlaasGeldof\LaravelUtils;

use Ibericode\Vat\Validator as VatValidator;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Symfony\Component\Finder\Finder;

class LaravelUtilsServiceProvider extends EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        MessageSending::class => [
            EmailLogger::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->shareTranslations();

        $this->addBladeDirectives();

        $this->addValidators();

        $this->addHelpers();
    }

    protected function shareTranslations()
    {
        $finder = new Finder();
        $path = resource_path('lang/'.$this->app->getLocale());

        $translations = collect($finder->files()->in($path))->mapWithKeys(function ($file) {
            $nameWithoutExtension = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return [$nameWithoutExtension => require $file->getRealPath()];
        });

        View::share('translations', $translations);
    }

    protected function addBladeDirectives()
    {
        Blade::directive('route', function ($expression) {
            return "<?php echo route($expression); ?>";
        });

        Blade::directive('config', function ($expression) {
            return "<?php echo config($expression); ?>";
        });

        Blade::directive('number', function ($expression) {
            if (strpos($expression, ',') === false) {
                $expression .= ', 2';
            }

            return "<?php echo number_format($expression, ',', '.'); ?>";
        });

        Blade::directive('currency', function ($expression) {
            return "<?php echo '€&nbsp;'.number_format($expression, 2, ',', '.'); ?>";
        });

        Blade::directive('errors', function ($expression) {
            return "<?php if (\$errors->has($expression)) echo '<span class=\"form-error\" role=\"alert\">'.ucfirst(\$errors->first($expression)).'</span>'; ?>";
        });
    }

    protected function addValidators()
    {
        Validator::extend('vat_number', function ($attribute, $value, $parameters, $validator) {
            return app(VatValidator::class)->validateVatNumberFormat($value);
        });
    }

    protected function addHelpers()
    {
        include __DIR__.'/helpers.php';
    }
}
