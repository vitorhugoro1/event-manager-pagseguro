<?php
/*
Plugin Name: Loja WP PagSeguro
Version: 0.4
Plugin URI: https://github.com/vitorhugoro1/loja-wp-pagseguro
Description:
Author: Vitor Hugo R Merencio (Polyvenn)
Author URI: https://github.com/vitorhugoro1/
*/

define('LOJA_ROOT', plugin_dir_path( __FILE__ ));
define('LOJA_ASSETS', LOJA_ROOT . 'assets/');

require LOJA_ROOT . 'pagseguro/vendor/autoload.php';
require LOJA_ASSETS . 'load-assets.php';
require LOJA_ROOT . 'cmb2/init.php';
require LOJA_ROOT . 'vhr-functions.php';
require LOJA_ROOT . 'cmb2-conditionals/cmb2-conditionals.php';
require LOJA_ROOT . 'class-post-types.php';
require LOJA_ROOT . 'class-meta-boxes.php';
require LOJA_ROOT . 'class-loja-init.php';

