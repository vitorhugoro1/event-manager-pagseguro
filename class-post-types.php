<?php

if(!class_exists('VHR_Loja_Post_Types')){
	class VHR_Loja_Post_Types
	{
		public static function init(){
			add_action('init', array(__CLASS__, 'vhr_post_types'));
			add_action('init', array(__CLASS__, 'vhr_taxonomies'));
			add_action('init', array(__CLASS__,'vhr_remove_post_type_support'), 10 );
		}

		public function vhr_post_types()
		{
			$labels = array(
				'name' => _x('Eventos', 'post type general name', 'your-plugin-textdomain'),
				'singular_name' => _x('Evento', 'post type singular name', 'your-plugin-textdomain'),
				'menu_name' => _x('Eventos', 'admin menu', 'your-plugin-textdomain'),
				'name_admin_bar' => _x('Evento', 'add new on admin bar', 'your-plugin-textdomain'),
				'add_new' => _x('Adicionar Evento', 'Evento', 'your-plugin-textdomain'),
				'add_new_item' => __('Adicionar Evento', 'your-plugin-textdomain'),
				'new_item' => __('Novo Evento', 'your-plugin-textdomain'),
				'edit_item' => __('Editar Evento', 'your-plugin-textdomain'),
				'view_item' => __('Visualizar Evento', 'your-plugin-textdomain'),
				'all_items' => __('Todos Eventos', 'your-plugin-textdomain'),
				'search_items' => __('Procurar Eventos', 'your-plugin-textdomain'),
				'parent_item_colon' => __('Parent Eventos:', 'your-plugin-textdomain'),
				'not_found' => __('Nenhum Evento encontrado.', 'your-plugin-textdomain'),
				'not_found_in_trash' => __('Nenhum Evento encontrado no Lixo.', 'your-plugin-textdomain'),
			);

			$args = array(
				'labels' => $labels,
				'description' => __('Description.', 'your-plugin-textdomain'),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'eventos'),
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array('title', 'thumbnail'),
			);

			register_post_type('eventos', $args);

			$labels = array(
				'name' => _x('Ingresso', 'post type general name', 'your-plugin-textdomain'),
				'singular_name' => _x('Ingresso', 'post type singular name', 'your-plugin-textdomain'),
				'menu_name' => _x('Ingressos', 'admin menu', 'your-plugin-textdomain'),
				'name_admin_bar' => _x('Ingresso', 'add new on admin bar', 'your-plugin-textdomain'),
				'add_new' => _x('Adicionar Ingresso', 'Ingresso', 'your-plugin-textdomain'),
				'add_new_item' => __('Adicionar Ingresso', 'your-plugin-textdomain'),
				'new_item' => __('Novo Ingresso', 'your-plugin-textdomain'),
				'edit_item' => __('Editar Ingresso', 'your-plugin-textdomain'),
				'view_item' => __('Visualizar Ingresso', 'your-plugin-textdomain'),
				'all_items' => __('Todos Ingressos', 'your-plugin-textdomain'),
				'search_items' => __('Procurar Ingressos', 'your-plugin-textdomain'),
				'parent_item_colon' => __('Parent Ingressos:', 'your-plugin-textdomain'),
				'not_found' => __('Nenhum Ingresso encontrado.', 'your-plugin-textdomain'),
				'not_found_in_trash' => __('Nenhum Ingresso encontrado no Lixo.', 'your-plugin-textdomain'),
			);

			$args = array(
				'labels' => $labels,
				'description' => __('Description.', 'your-plugin-textdomain'),
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'ingressos'),
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => null,
			);

			register_post_type('ingresso', $args);

			flush_rewrite_rules();
		}

		public function vhr_taxonomies(){
			$labels = array(
				'name' => _x('Tipo de dia', 'taxonomy general name', 'textdomain'),
				'singular_name' => _x('Tipo de dia', 'taxonomy singular name', 'textdomain'),
				'search_items' => __('Search Tipo de dia', 'textdomain'),
				'all_items' => __('Todos Tipo de dia', 'textdomain'),
				'parent_item' => __('Parent Tipo de dia', 'textdomain'),
				'parent_item_colon' => __('Parent Tipo de dia:', 'textdomain'),
				'edit_item' => __('Editar Tipo de dia', 'textdomain'),
				'update_item' => __('Atualizar Tipo de dia', 'textdomain'),
				'add_new_item' => __('Adicionar novo Tipo de dia', 'textdomain'),
				'new_item_name' => __('Novo Tipo de dia', 'textdomain'),
				'menu_name' => __('Tipo de dia', 'textdomain'),
			);

			$args = array(
				'hierarchical' => true,
				'labels' => $labels,
				'meta_box_cb' => false,
				'show_admin_column' => false,
				'query_var' => true,
			);

			register_taxonomy('tipo-dia', array('eventos'), $args);
		}

		public function vhr_remove_post_type_support() {
			$remove_support_list = array('title', 'editor');

			foreach ( $remove_support_list as $support ) {
				remove_post_type_support( 'ingresso', $support );
			}
		}
	}
}

VHR_Loja_Post_Types::init();