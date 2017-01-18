<?php

function vhr_load_assets(){
  global $post_type, $post_ID;

    wp_register_style( 'select2css', 'http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
    wp_register_script( 'select2', 'http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_script( 'jquery-mask', plugin_dir_url( __FILE__ ) . 'js/jquery.mask.min.js', array('jquery'), '1.7.7' );
    wp_enqueue_script( 'vhr-script', plugin_dir_url( __FILE__ ) . 'js/vhr-scripts.js', array('jquery'), '1.0' );
    wp_enqueue_script( 'ingresso-admin', plugin_dir_url( __FILE__ ) . 'js/ingresso.js', array('jquery'), '1.0' );
    wp_enqueue_style( 'select2css' );
    wp_enqueue_script( 'select2' );
    wp_enqueue_style( 'ingresso-admin-css', plugin_dir_url( __FILE__ ) . 'css/ingresso.css' );
}

add_action( 'admin_enqueue_scripts', 'vhr_load_assets' );
