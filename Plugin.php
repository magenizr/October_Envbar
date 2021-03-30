<?php namespace Magenizr\Envbar;

/**
 * Magenizr Envbar
 *
 * @category    Magenizr
 * @package     Magenizr_Envbar
 * @copyright   Copyright (c) 2020 Magenizr (https://www.Magenizr.com)
 * @license        http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use App;
use Magenizr\Envbar\Models\Settings;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Event;

class Plugin extends PluginBase
{
    /**
     * @var Plugin settings
     */
    protected $settings;

    /**
     * @return array|void
     */
    public function boot()
    {
        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {

            $this->settings = Settings::instance();

            if ($this->settings->setPathTemp('public')->renderCss()) {

                $controller->addCss($this->settings->getPublicCssPath(), '1.0.4');
            }
        });
    }

    /**
     * Register Settings
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'magenizr.envbar::lang.settings.label',
                'description' => 'magenizr.envbar::lang.settings.description',
                'icon' => 'icon-bullhorn',
                'category' => SettingsManager::CATEGORY_SYSTEM,
                'class' => 'Magenizr\Envbar\Models\Settings',
                'permissions' => ['magenizr.envbar.access_settings'],
                'keywords' => 'backend color nav bar',
            ],
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'magenizr.envbar.access_settings' => [
                'tab' => 'magenizr.envbar::lang.permission.tab',
                'label' => 'magenizr.envbar::lang.permission.label',
            ],
        ];
    }
}
