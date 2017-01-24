<?php

class VHR_Post_Categories(){
  function __construct(){
    add_action('init', array($this, 'register_post_category_eventos'));
    add_action('save_post_eventos', array($this, 'save_evento_action'));
  }

  function register_post_category_eventos(){
    
  }

  function save_evento_action(){
    // Adcionar novo termo a Category Eventos
    // a partir do post novo
  }
}

new VHR_Post_Categories;
