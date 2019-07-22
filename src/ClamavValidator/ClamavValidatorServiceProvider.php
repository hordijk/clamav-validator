<?php namespace Sunspikes\ClamavValidator;

use Illuminate\Support\ServiceProvider;

class ClamavValidatorServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The list of validator rules.
     *
     * @var array
     */
    protected $rules = [
        'clamav',
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'clamav-validator');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/clamav.php' => config_path('clamav.php'),
            ], 'config');
            $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/clamav-validator'),
            ], 'lang');
            $this->app['validator']
                ->resolver(function ($translator, $data, $rules, $messages, $customAttributes = []) {
                    return new ClamavValidator(
                        $translator,
                        $data,
                        $rules,
                        $messages,
                        $customAttributes
                    );
                });

            $this->addNewRules();
        }
    }

    /**
     * Get the list of new rules being added to the validator.
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }


    /**
     * Add new rules to the validator.
     */
    protected function addNewRules()
    {
        foreach ($this->getRules() as $rule) {
            $this->extendValidator($rule);
        }
    }


    /**
     * Extend the validator with new rules.
     *
     * @param  string $rule
     * @return void
     */
    protected function extendValidator($rule)
    {
        $method = studly_case($rule);
        $translation = $this->app['translator']->get('clamav-validator::validation');
        $this->app['validator']->extend(
            $rule,
            ClamavValidator::class .'@validate' . $method,
            isset($translation[$rule]) ? $translation[$rule] : []
        );
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/clamav.php', 'clamav');
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
