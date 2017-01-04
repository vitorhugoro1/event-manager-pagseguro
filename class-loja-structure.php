<?php

/**
 *
 */
class LojaWPPagSeguro
{
  protected $pluginPath;
  protected $pluginUrl;
  protected $prefix;

  public function __construct()
  {
    $this->pluginPath = dirname(__FILE__);

    $this->pluginUrl = WP_PLUGIN_URL . '\loja-wp-pagseguro';

    $this->prefix = '_vhr_';

    add_action( 'init', array($this, 'vhr_loja_cpt') );
    add_action( 'cmb2_admin_init', array($this, 'vhr_metaboxes_cmb2'));
    add_action( 'add_meta_boxes', array($this, 'vhr_metaboxes'));
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
  		'supports'           => array( 'title', 'thumbnail')
  	);

    register_post_type( 'loja-cpt', $args );

    flush_rewrite_rules();
  }

  public function vhr_metaboxes_cmb2(){
    /**
     * Descrição do Evento
     */

    $descricao = new_cmb2_box(array(
      'id'  => 'descricao_metabox',
      'title' => 'Breve descrição sobre o evento',
      'object_types'  => array('loja-cpt'),
      'context'   => 'normal',
      'priority'  => 'high',
      'show_names'  => false
    ));

    $descricao->add_field(array(
      'id'  => $this->prefix . 'desc',
      'title' => 'Breve descrição sobre o evento',
      'type'  => 'wysiwyg'
    ));

    /**
     * Mapa de mesas
     */

     $mapa = new_cmb2_box(array(
       'id'  => 'mapa_metabox',
       'title' => 'Mapa de mesas do evento',
       'object_types'  => array('loja-cpt'),
       'context'   => 'side',
       'priority'  => 'core',
       'show_names'  => false
     ));

     $mapa->add_field(array(
       'id'     => $this->prefix . 'mesas',
       'title'  => 'Mapa de mesas do evento',
       'desc'   => '',
       'type'   => 'file',
       'options' => array(
            'url' => false,
        ),
       'text'   => array(
         'add_upload_file_text' => 'Adicionar imagem do mapa de mesas'
       )
     ));

     /**
      * Datas para o Evento
      */


     $data = new_cmb2_box(array(
       'id'  => 'data_metabox',
       'title' => 'Mapa de mesas do evento',
       'object_types'  => array('loja-cpt'),
       'context'   => 'side',
       'priority'  => 'core',
       'show_names'  => true
     ));

    $data_group_id = $data->add_field(array(
      'id'    => $this->prefix . 'data',
      'type'  => 'group',
      'options' => array(
        'group_title' => 'Dia {#}',
        'add_button'    => 'Adicionar data',
        'remove_button' => 'Remover data',
        'sortable'      => true, // beta
      )
    ));

    $data->add_group_field( $data_group_id, array(
        'name' => 'Dia',
        'id'   => 'data',
        'type' => 'text_date_timestamp',
        'date_format' => 'd/m/Y',
        'attributes'  => array(
          'placeholder' => 'dd/mm/yyyy'
        )
    ) );

    $data->add_group_field( $data_group_id, array(
        'name' => 'Tipo de dia',
        'id'   => 'tipo',
        'type' => 'select',
        'show_option_none'  => false,
        'default' => 'expo',
        'options' => array(
          'expo'  => 'Exposição',
          'org'   => 'Organização',
        )
    ) );


    $valores = new_cmb2_box(array(
       'id'     => 'valores_metabox',
       'title' => 'Valores dos ingressos',
       'object_types'  => array('loja-cpt'),
       'context'   => 'normal',
       'priority'  => 'core',
       'show_names'  => true,
       'show_on'    => array('alt_key' => 'valores', 'alt_value' => 'post-new.php')
    ));

    $valores_group_id = $valores->add_field(array(
	    'id'    => $this->prefix . 'valores',
	    'type'  => 'group',
	    'options' => array(
		    'group_title' => 'Ingresso {#}',
		    'add_button'    => 'Adicionar novo valor',
		    'remove_button' => 'Remover valor',
		    'sortable'      => true, // beta
	    )
    ));

	  $valores->add_group_field( $valores_group_id, array(
          'name' => 'Valor',
          'id'   => 'valor',
          'type' => 'text_money',
          'before_field'    => 'R$'
      ) );

	  $valores->add_group_field( $valores_group_id, array(
		  'name' => 'Vai ser para multiplos dias?',
		  'id'   => 'multiplo',
		  'type' => 'checkbox'
      ) );

	  $valores->add_group_field( $valores_group_id, array(
		  'name' => 'Dias',
		  'id'   => 'dia-multiplo',
		  'type' => 'multicheck',
		  'options' => array($this, 'vhr_load_data_values'),
      'attributes' => array(
  			'data-conditional-id'    => wp_json_encode( array( $valores_group_id, 'multiplo' ) ),
  			'data-conditional-value' => 'on',
  		),
	  ) );

    $valores->add_group_field( $valores_group_id, array(
		  'name' => 'Dia',
		  'id'   => 'dia-simples',
		  'type' => 'radio_inline',
      'options' => array($this, 'vhr_load_data_values'),
      'attributes'  => array(
  			'required'               => true, // Will be required only if visible.
  			'data-conditional-id'    => wp_json_encode( array( $valores_group_id, 'multiplo' ) ),
  			'data-conditional-value' => 'off',
  		),
	  ) );

  }

  public function vhr_load_data_values(){
    global $post_ID, $pagenow;

    $datas = array();

    if($pagenow == 'post-new.php')
        return array();

    $dates = get_post_meta($post_ID, '_vhr_data', true);

    foreach((array) $dates as $key => $date){
      $datas[$key] = date('d/m/Y', $date['data']);
    }

    return $datas;
  }

  public function vhr_metaboxes(){

    /**
     * Páginas relacionadas ao Evento
     */

  }

}
