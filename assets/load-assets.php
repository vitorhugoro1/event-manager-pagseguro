<?php

function vhr_load_assets(){
  global $post_type, $post_ID;

  if($post_type == 'eventos' || $post_type == 'ingresso'){
    wp_enqueue_script( 'jquery-mask', plugin_dir_url( __FILE__ ) . 'js/jquery.mask.min.js', array('jquery'), '1.7.7' );
    wp_enqueue_script( 'vhr-script', plugin_dir_url( __FILE__ ) . 'js/vhr-scripts.js', array('jquery'), '1.0' );
    wp_enqueue_script( 'ingresso-admin', plugin_dir_url( __FILE__ ) . 'js/ingresso.js', array('jquery'), '1.0' );
    wp_enqueue_style( 'ingresso-admin-css', plugin_dir_url( __FILE__ ) . 'css/ingresso.css' );
  }
}

add_action( 'admin_enqueue_scripts', 'vhr_load_assets' );
