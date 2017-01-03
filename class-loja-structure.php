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
        'add_button'    => __( 'Add Another Entry', 'cmb2' ),
        'remove_button' => __( 'Remove Entry', 'cmb2' ),
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

    $data->add_group_field( $data_group_id, array(
        'name' => 'Grupo',
        'id'   => 'grupo',
        'type' => 'select',
        'show_option_none'  => false,
        'default' => 'expo',
        'options' => array(
          'expo'  => 'Exposição',
          'org'   => 'Organização',
        )
    ) );


  }

  public function vhr_metaboxes(){
    /**
     *  Dados do Evento (data, valores)
     */

     add_meta_box( 'atributos', 'Atributos do Evento', array($this, 'vhr_screen_attributes') , 'loja-cpt', 'normal', 'default' );

    /**
     * Páginas relacionadas ao Evento
     */



    /**
     *
     */
  }

  public function vhr_screen_attributes($post){
    ?>
    <style media="screen">
    .vhr-tabs {
      color: #333;
      display: block;
      font-family: arial, sans-serif;
      margin: auto;
      position: relative;
      width: 100%;
    }

      .vhr-tabs input[name="sections"] {
        left: -9999px;
        position: absolute;
        top: -9999px;
      }

      .vhr-tabs section {
        display: block;
      }

      .vhr-tabs section label {
        background: #ccc;
        border:1px solid #fff;
        cursor: pointer;
        display: block;
        font-size: 1.2em;
        font-weight: bold;
        padding: 15px 20px;
        position: relative;
        width: 180px;
        z-index:100;
      }

      .vhr-tabs section article {
        display: none;
        left: 230px;
        min-width: 300px;
        padding: 0 0 0 21px;
        position: absolute;
        top: 0;
      }

      .vhr-tabs section article:after {
        background-color: #ccc;
        bottom: 0;
        content: "";
        display: block;
        left:-229px;
        position: absolute;
        top: 0;
        width: 220px;
        z-index:1;
      }

      .vhr-tabs input[name="sections"]:checked + label {
        background: #eee;
        color: #bbb;
      }

      .vhr-tabs input[name="sections"]:checked ~ article {
        display: block;
      }


      @media (max-width: 533px) {

      .vhr-tabs {
        width: 100%;
      }

      .vhr-tabs section label {
        font-size: 1em;
        width: 160px;
      }

      .vhr-tabs section article {
        left: 200px;
        min-width: 270px;
      }

      .vhr-tabs section article:after {
        background-color: #ccc;
        bottom: 0;
        content: "";
        display: block;
        left:-199px;
        position: absolute;
        top: 0;
        width: 200px;

      }

      }

    </style>
      <div class="vhr-tabs">
        <section id="section1">
          <input type="radio" name="sections" id="data" checked>
          <label for="data">Data</label>
          <article>
            Teste
          </article>
        </section>
        <section id="section2">
          <input type="radio" name="sections" id="valores">
          <label for="valores">Valores</label>
          <article>
            Valores
          </article>
        </section>
      </div>
    <?php
  }

}
