<?php

namespace Viviniko\Theme;

use Viviniko\Agent\Facades\Agent;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

class ThemeManager
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $themeName;

    /**
     * @var array
     */
    protected $themes;

    /**
     * @var mixed
     */
    protected $defaultTheme;

    /**
     * @var string
     */
    protected $public;

    /**
     * ThemeManager constructor.
     * @param Filesystem $filesystem
     * @param Dispatcher $events
     */
    public function __construct(Filesystem $filesystem, Dispatcher $events)
    {
        $this->filesystem = $filesystem;
        $this->events = $events;
    }

    /**
     * Set themes base path.
     *
     * @param $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        $this->themes = array_map(function ($item) {
                return pathinfo($item, PATHINFO_BASENAME);
            }, $this->filesystem->directories($this->basePath));
        $this->themeName = $this->decideThemeName();

        return $this;
    }

    /**
     * Set default theme.
     *
     * @param $defaultTheme
     *
     * @return $this
     */
    public function setDefaultTheme($defaultTheme)
    {
        $this->defaultTheme = $defaultTheme;
        $this->themeName = $this->decideThemeName();

        return $this;
    }

    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get publishable assets.
     *
     * @return array
     */
    public function getPublishableAssets()
    {
        $publishes = [];
        foreach ($this->themes as $theme) {
            if (file_exists($assetPath = $this->getAssetPath($theme))) {
                $publishes[$assetPath] = public_path("themes/{$theme}");
            }
        }

        return $publishes;
    }

    public function getViewPaths()
    {
        return [
            $this->basePath . '/' . $this->themeName . '/views',
        ];
    }

    public function isMobileVisitor()
    {
        return Agent::isMobile() || Agent::isTablet();
    }

    public function isDisabledMobileTheme()
    {
        return !(is_array($this->defaultTheme) && isset($this->defaultTheme['mobile']));
    }

    public function getAssetUrl($asset, $secure = null)
    {
        return $this->public . '/' . $this->themeName . '/' . ltrim($asset, '/');
    }

    public function getAssetPath($themeName)
    {
        return $this->basePath . '/' . $themeName . '/assets';
    }

    public function getViewPath($themeName)
    {
        return $this->basePath . '/' . $themeName . '/views';
    }

    protected function decideThemeName()
    {
        if (!$this->isDisabledMobileTheme() && $this->isMobileVisitor()) {
            return $this->defaultTheme['mobile'];
        }

        return $this->defaultTheme['default'];
    }
}