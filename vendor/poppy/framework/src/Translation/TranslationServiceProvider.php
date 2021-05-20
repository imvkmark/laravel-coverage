<?php

namespace Poppy\Framework\Translation;

use Illuminate\Translation\TranslationServiceProvider as IlluminateTranslationServiceProvider;

/**
 * Class TranslationServiceProvider.
 */
class TranslationServiceProvider extends IlluminateTranslationServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerLoader();

		$this->app->singleton('translator', function ($app) {
			$loader = $app['translation.loader'];
			$locale = $app['config']['app.locale'];
			$trans  = new Translator($loader, $locale, $app['files']);
			$trans->setFallback($app['config']['app.fallback_locale']);

			return $trans;
		});
	}


	/**
	 * Get the services provided by the provider.
	 * @return array
	 */
	public function provides()
	{
		return [
			'translator',
		];
	}
}

