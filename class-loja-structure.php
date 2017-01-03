<?php

/**
 *
 */
class LojaWPPagSeguro
{
  protected $pluginPath;
  protected $pluginUrl;

  public function __construct()
  {
    $this->pluginPath = dirname(__FILE__);

    $this->pluginUrl = WP_PLUGIN_URL . '/loja-wp-pagseguro';

    add_action( 'init', array($this, 'vhr_loja_cpt') );
    add_action( 'cmb2_admin_init', array($this, 'vhr_metaboxes'));
  }

  public function vhr_loja_cpt(){
      $labels = array(
  		'name'               => _x( 'Eventos', 'post type general name', 'your-plugin-textdomain' ),
  		'singular_name'      => _x( 'Evento', 'post type singular name', 'your-plugin-textdomain' ),
  		'menu_name'          => _x( 'Eventos', 'admin menu', 'your-plugin-textdomain' ),
  		'name_admin_bar'     => _x( 'Evento', 'add new on admin bar', 'your-plugin-textdomain' ),
  		'add_new'            => _x( 'Adicionar Evento', 'Evento', 'your-plugin-textdomain' ),
  		'add_new_item'       => __( 'Adicionar Evento', 'your-plugin-textdomain' ),
  		'new_item'           => __( 'Novo Evento', 'your-plugin-textdomain' ),
  		'edit_item'          => __( 'Editar Evento', 'your-plugin-textdomain' ),
  		'view_item'          => __( 'Visualizar Evento', 'your-plugin-textdomain' ),
  		'all_items'          => __( 'Todos Eventos', 'your-plugin-textdomain' ),
  		'search_items'       => __( 'Procurar Eventos', 'your-plugin-textdomain' ),
  		'parent_item_colon'  => __( 'Parent Eventos:', 'your-plugin-textdomain' ),
  		'not_found'          => __( 'Nenhum Evento encontrado.', 'your-plugin-textdomain' ),
  		'not_found_in_trash' => __( 'Nenhum Evento encontrado no Lixo.', 'your-plugin-textdomain' )
  	);

  	$args = array(
  		'labels'             => $labels,
      'description'        => __( 'Description.', 'your-plugin-textdomain' ),
  		'public'             => true,
  		'publicly_queryable' => true,
  		'show_ui'            => true,
  		'show_in_menu'       => true,
  		'query_var'          => true,
  		'rewrite'            => array( 'slug' => 'eventos' ),
  		'capability_type'    => 'post',
  		'has_archive'        => true,
  		'hierarchical'       => false,
  		'menu_position'      => null,
  		'supports'           => array( 'title', 'thumbnail', 'excerpt')
  	);

    register_post_type( 'loja-cpt', $args );

    flush_rewrite_rules();
  }

  public function vhr_metaboxes(){
    
  }

}
