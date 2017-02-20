<?php

function vhr_load_assets(){
  global $post_type, $post_ID, $post;

    wp_register_style( 'select2css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', false, '1.0', 'all' );
    wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_script( 'jquery-mask', plugin_dir_url( __FILE__ ) . 'js/jquery.mask.min.js', array('jquery'), '1.7.7' );
    wp_enqueue_script( 'vhr-script', plugin_dir_url( __FILE__ ) . 'js/vhr-scripts.js', array('jquery'), '1.0' );
    wp_enqueue_script( 'ingresso-admin', plugin_dir_url( __FILE__ ) . 'js/ingresso.js', array('jquery'), '1.0' );
    wp_enqueue_style( 'select2css' );
    wp_enqueue_script( 'select2' );
    wp_enqueue_style( 'ingresso-admin-css', plugin_dir_url( __FILE__ ) . 'css/ingresso.css' );
}

add_action( 'admin_enqueue_scripts', 'vhr_load_assets' );

add_action( 'wp_enqueue_scripts', 'load_assets_front' );

function load_assets_front(){
  if(is_page( 'selecionar-ingresso' )){
    wp_enqueue_script('front-validations', plugin_dir_url( __FILE__ ) . 'js/front-validations.js', array( 'jquery' ), '1.0');
  }
}
