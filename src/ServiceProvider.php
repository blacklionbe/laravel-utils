<?php

namespace BlackLion\LaravelUtils;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Mail\Events\MessageSending;
use Ibericode\Vat\Validator as VatValidator;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class ServiceProvider extends EventServiceProvider
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
        LocaleUpdated::class => [
            ShareTranslations::class,
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

        $this->removeIndexPhpFromUrl();

        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->addBladeDirectives();

        $this->addValidators();

        $this->addHelpers();

        $this->addCarbonMacros();

        app(ShareTranslations::class)->update();
    }

    protected function removeIndexPhpFromUrl()
    {
        if (Str::startsWith($path = request()->getRequestUri(), '/index.php/')) {
            $url = str_replace('index.php/', '', $path);

            if (strlen($url) > 0) {
                header("Location: $url", true, 301);
                exit;
            }
        }
    }

    protected function addBladeDirectives()
    {
        Blade::directive('route', function ($expression) {
            return "<?php echo route($expression); ?>";
        });

        Blade::directive('config', function ($expression) {
            return "<?php echo config($expression); ?>";
        });

        Blade::directive('label', function ($expression) {
            return "<?php echo label($expression); ?>";
        });

        Blade::directive('number', function ($expression) {
            if (strpos($expression, ',') === false) {
                $expression .= ', 2';
            }

            return "<?php echo number_format($expression, ',', '.'); ?>";
        });

        Blade::directive('currency', function ($expression) {
            if (strpos($expression, ',') === false) {
                $expression .= ', 2';
            }

            return "<?php echo '€&nbsp;'.number_format($expression, ',', '.'); ?>";
        });

        Blade::directive('errors', function ($expression) {
            return "<?php if (\$errors->has($expression)) echo '<span class=\"form-error\" role=\"alert\">'.ucfirst(\$errors->first($expression)).'</span>'; ?>";
        });

        Blade::directive('nl2br', function ($expression) {
            return "<?php echo nl2br(e($expression)) ?>";
        });

        Blade::directive('capture', function ($expression) {
            return "<?php
                \$__capture_directive_variable = (string) str([{$expression}][0] ?? '')->camel();
                ob_start();
            ?>";
        });

        Blade::directive('endcapture', function () {
            return "<?php
                \$\$__capture_directive_variable = new Illuminate\Support\HtmlString(trim(ob_get_clean()));
            ?>";
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

    protected function addCarbonMacros()
    {
        Carbon::macro('formatDate', function () {
            return $this->format('d/m/Y');
        });

        Carbon::macro('formatDateTime', function () {
            return $this->format('d/m/Y H:i');
        });
    }
}
