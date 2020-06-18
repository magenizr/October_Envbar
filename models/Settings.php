<?php namespace Magenizr\Envbar\Models;

/**
 * Magenizr Envbar
 *
 * @category    Magenizr
 * @package     Magenizr_Envbar
 * @copyright   Copyright (c) 2020 Magenizr (https://www.Magenizr.com)
 * @license        http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use App;
use BackendAuth;
use Model;
use Lang;
use File;
use Cache;
use Less_Parser;

/**
 * Settings Model
 */
class Settings extends Model
{
    const DEVELOPMENT_COLOR = '#27ae60';

    const STAGING_COLOR = '#f39c12';

    const PRODUCTION_COLOR = '#e74c3c';

    const CSS_STYLE_FILE = 'magenizr.envbar.style.css';

    /**
     * @var string The key to store rendered CSS in the cache under
     */
    public $cacheKey = 'backend::magenizr.envbar.style';

    /**
     * @var array
     */
    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string
     */
    public $settingsCode = 'magenizr_envbar';

    /**
     * @var string
     */
    public $settingsFields = 'fields.yaml';

    private $pathTemp;

    // Default settings.

    public function initSettingsData()
    {
        $this->enabled = false;

        $this->superuser = false;

        $this->envs = [
            0 => [
                'env' => 'development',
                'color' => self::DEVELOPMENT_COLOR,
            ],
            1 => [
                'env' => 'staging',
                'color' => self::STAGING_COLOR,
            ],
            2 => [
                'env' => 'production',
                'color' => self::PRODUCTION_COLOR,
            ],
        ];
    }

    /**
     * Clear cache after saving settings.
     */
    public function afterSave()
    {
        Cache::forget(self::instance()->cacheKey);
    }

    /**
     * Render CSS or return cached version.
     *
     * @return string
     */
    public function renderCss()
    {
        if (! $this->checkPermissions()) {
            return false;
        }

        $cacheKey = self::instance()->cacheKey;

        if (Cache::has($cacheKey) && $this->isCssCompiled()) {

            return Cache::get($cacheKey);
        }

        try {
            $customCss = $this->compileCss();
            Cache::forever($cacheKey, $customCss);
        } catch (Exception $ex) {
            $customCss = '/* '.$ex->getMessage().' */';
        }

        return $customCss;
    }

    /**
     * Check permissions.
     *
     * @return bool
     */
    private function checkPermissions()
    {
        if (! $this->enabled) {
            return false;
        }

        if ($this->superuser) {

            $isSuperUser = BackendAuth::getUser()->isSuperUser();

            if (! $isSuperUser) {
                return false;
            }
        }

        if (! $this->getEnv()) {
            return false;
        }

        return true;
    }

    /**
     * Get environment settings.
     *
     * @return bool|string
     */
    private function getEnv($key = '')
    {
        $appEnv = env('APP_ENV');

        if (empty($appEnv)) {
            $appEnv = App::environment();
        }

        foreach ($this->envs as $envs) {

            $env = trim($envs['env']);

            if ($appEnv === $env) {

                if (isset($envs[$key])) {
                    return $envs[$key];
                }

                return $env;
            }
        }

        return false;
    }

    /**
     * Check if css file has been compiled already.
     *
     * @return mixed
     */
    private function isCssCompiled()
    {
        return File::exists($this->getCssPath());
    }

    /**
     * Get folder path for compile CSS file.
     *
     * @return string
     */
    public function getCssPath()
    {
        return implode('/', [$this->getPathTemp(), self::CSS_STYLE_FILE]);
    }

    /**
     * Get folder path for compile CSS file.
     *
     * @return mixed
     */
    public function getPathTemp()
    {
        return $this->pathTemp;
    }

    /**
     * Set folder path for compiled CSS file.
     *
     * @return $this
     */
    public function setPathTemp($folder = '')
    {
        $this->pathTemp = temp_path($folder);

        return $this;
    }

    /**
     * Compile CSS and save as a file.
     *
     * @return mixed
     * @throws \Exception
     */
    private function compileCss()
    {
        $parser = new Less_Parser(['compress' => true]);
        $lessPath = File::symbolizePath('~/plugins/magenizr/envbar/assets/less/');

        $parser->ModifyVars([
            'color' => self::get('magenizr_envbar.color', $this->getEnv('color')),
        ]);

        $parser->parse(File::get($lessPath.'/style.less'));

        return File::put($this->getCssPath(), $parser->getCss());
    }

    /**
     * Get file path for compile CSS file.
     *
     * @return string
     */
    public function getPublicCssPath()
    {
        return str_replace(public_path(), '', $this->getCssPath());
    }
}
