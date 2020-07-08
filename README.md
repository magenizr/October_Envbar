## Envbar
This October CMS plugin allows you to differentiate between environments by adding a custom colored bar above the top navigation.

![October Envbar - Intro](https://images2.imgbox.com/69/cf/EhSjgnjn_o.gif)

## System Requirements
- October CMS 1.0.4x
- PHP 7.x

## Installation
### Backend

Go to `Settings > System > Updates & Plugins` and search for the plugin. Click on `Install Plugins` and search for `envbar`.

### Artisan

```
php artisan plugin:install Magenizr.Envbar
Downloading plugin: Magenizr.Envbar
Unpacking plugin: Magenizr.Envbar
```

### Manually
1. Download the code.
2. Extract the downloaded tar.gz file. Example: `tar -xzf October_Envbar_1.0.2.tar.gz`.
3. Copy the code into `./plugins/magenizr/envbar/`.

## Configuration
Make sure the name of the environment ( e.g `development` ) matches the value of the variable `APP_ENV` or `default` in `./config/environment.php` or in your `.env` file.

## Features
* Enable or disable plugin temporarily.
* Enable plugin for Superusers only.
* Add multiple environments and choose the color of the bar.
* Automatically clear cached styles ( colors ) after saving settings or if `.env` file has been updated.

## Usage
Simply navigate to `Settings > System > Envbar` and enable the plugin. Update colors within the `Environments` section if required.

## Support
If you experience any issues, don't hesitate to open an issue on [Github](https://github.com/magenizr/October_Envbar/issues).

## Contact
Follow us on [GitHub](https://github.com/magenizr), [Twitter](https://twitter.com/magenizr) and [Facebook](https://www.facebook.com/magenizr).

## History
===== 1.0.3 =====
* Automatically clear cached styles ( colors ) if `.env` file has been updated. Thx @kavishdahekar

===== 1.0.2 =====
* Move compiled CSS file from `./storage/temp` to `./storage/temp/public`. Thx @jan-vince

===== 1.0.1 =====
* Fallback for `APP_ENV` variable.
* Compile CSS again if `./storage/temp` has been cleared.

===== 1.0.0 =====
* First release

## License
[MIT License](http://www.opensource.org/licenses/mit-license.html)
