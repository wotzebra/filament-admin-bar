<?php

namespace Wotz\FilamentAdminBar;

use Illuminate\Translation\TranslationServiceProvider;

class TranslationLoader extends TranslationServiceProvider
{
    public function register()
    {
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];
            $locale = $app->getLocale();
            $trans = new Translator($loader, $locale); // < different translator
            $trans->setFallback($app->getFallbackLocale());

            return $trans;
        });
    }
}
