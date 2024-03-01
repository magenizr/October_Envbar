<?php namespace Magenizr\Envbar\Models;

/**
 * Magenizr Envbar
 *
 * @category    Magenizr
 * @package     Magenizr_Envbar
 * @copyright   Copyright (c) 2020 Magenizr (https://www.magenizr.com.au)
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
     * @var string The key to store rendered styles
     */
    public $cacheStyle = 'backend::magenizr.envbar.style';

    /**
     * @var string The key to store MD5 hash of the .env file in
     */
    public $cacheEnvHash = 'backend::magenizr.envbar.envhash';

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

    /**
     * @var
     */
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
        Cache::forget(self::instance()->cacheStyle);
    }

    /**
     * Render CSS or return cached version.
     *
     * @return string
     */
    public function renderCss()
    {
        if (!$this->checkPermissions()) {
            return false;
        }

        // Clear cache if .env file has been updated
        if ($this->isEnvUpdated()) {

            $this->afterSave();
        }

        $cacheStyle = self::instance()->cacheStyle;

        // Return cached CSS styles
        if (Cache::has($cacheStyle) && $this->isCssCompiled()) {

            return Cache::get($cacheStyle);
        }

        try {

            // Cache CSS styles
            $customCss = $this->compileCss();
            Cache::forever($cacheStyle, $customCss);

        } catch (Exception $ex) {
            $customCss = '/* ' . $ex->getMessage() . ' */';
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
        if (!$this->enabled) {
            return false;
        }

        if ($this->superuser) {

            $backendUser = BackendAuth::getUser();
            if ($backendUser && !$backendUser->isSuperUser()) {
                return false;
            }
        }

        if (!$this->getEnv()) {
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
     * Return MD5 hash of .env file.
     *
     * @return bool|false|string
     */
    private function getEnvHash()
    {
        $envFile = implode('/', [app()->environmentPath(), app()->environmentFile()]);

        if (is_readable($envFile)) {

            return md5_file($envFile);
        }

        return false;
    }

    /**
     * Check if .env file has been changed and cache MD5 hash.
     *
     * @return bool
     */
    private function isEnvUpdated()
    {
        $envHash = $this->getEnvHash();

        if ($envHash) {

            $cacheEnvHash = self::instance()->cacheEnvHash;

            if (Cache::get($cacheEnvHash) !== $envHash) {

                Cache::forever($cacheEnvHash, $envHash);

                return true;
            }
        }

        return false;
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

        $parser->parse(File::get($lessPath . '/style.less'));

        if (!File::exists($this->getPathTemp())) {
            File::makeDirectory($this->getPathTemp());
        }

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
