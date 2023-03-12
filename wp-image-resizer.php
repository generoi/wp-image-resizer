<?php
/*
Plugin Name:        WP Image Resizer
Plugin URI:         http://genero.fi
Description:        A plugin which provides dynamic image sizes through a CDN
Version:            1.0.0
Author:             Genero
Author URI:         http://genero.fi/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/

use GeneroWP\ImageResizer\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

Plugin::getInstance();
