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

require LOJA_ROOT . 'pagseguro/vendor/autoload.php';
require LOJA_ROOT . 'cmb2/init.php';
require LOJA_ROOT . 'class-loja-structure.php';

$app = new LojaWPPagSeguro;

?>
