<?php

namespace Viviniko\Theme;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ThemeServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/theme.php' => config_path('theme.php'),
        ]);

        $theme = $this->app['theme'];

        if ($viewFinder = $this->app['view']->getFinder()) {
            foreach ($theme->getViewPaths() as $location) {
                $viewFinder->prependLocation($location);
            }
        }

        // register publishes
        if ($this->app->runningInConsole()) {
            $this->publishes($theme->getPublishableAssets(), 'theme:asset');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/theme.php', 'theme');

        $this->app->singleton('theme.path', function ($app) {
            return $app['config']->get('theme.path', base_path('themes'));
        });

        $this->app->singleton('theme', function ($app) {
            $theme = new ThemeManager($app['files'], $app['events']);
            $theme->setBasePath($app['theme.path']);
            $theme->setDefaultTheme([
                'default' => $app['config']->get('theme.default'),
                'mobile' => $app['config']->get('theme.mobile'),
            ]);
            $theme->setPublic($app['config']->get('theme.public', asset('themes')));

            return $theme;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'theme'
        ];
    }
}