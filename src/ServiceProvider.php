<?php

namespace BlackLion\LaravelUtils;

use Ibericode\Vat\Validator as VatValidator;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        Blade::directive('nl2br', function ($expression) {
            return "<?php echo nl2br(e($expression)) ?>";
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
