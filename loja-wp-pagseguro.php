<?php
/*
Plugin Name: Loja WP PagSeguro
Version: 0.1
Plugin URI: ${TM_PLUGIN_BASE}
Description:
Author: ${TM_NAME}
Author URI: ${TM_HOMEPAGE}
*/

define('LOJA_ROOT', plugin_dir_path( __FILE__ ));
define('LOJA_ASSETS', LOJA_ROOT . 'assets/');

require LOJA_ROOT . 'pagseguro/vendor/autoload.php';
require LOJA_ASSETS . 'load-assets.php';
require LOJA_ROOT . 'cmb2/init.php';
require LOJA_ROOT . 'vhr-functions.php';
require LOJA_ROOT . 'cmb2-conditionals/cmb2-conditionals.php';
require LOJA_ROOT . 'class-loja-structure.php';

$app = new LojaWPPagSeguro;

?>
