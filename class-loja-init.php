<?php

class VHR_Loja_Build
{
	protected static $term_tipo = 'tipo-dia';

	public function __construct()
	{
		add_action('admin_init', array($this, 'vhr_standard_tipo_dia'));
	}

	public function vhr_standard_tipo_dia(){
		$standard_types = array('Organização', 'Exposição');
		$taxonomy = $this->$term_tipo;

		foreach ( $standard_types as $standard_type ) {
			$type_slug = sanitize_title($standard_type);

			if (!term_exists($type_slug, $taxonomy)){
				wp_insert_term(
					$standard_type,
					$taxonomy,
					array(
						'slug'   => $type_slug
					)
				);
			}
		}
	}

	public function add_standard_pages(){
		$pages = array('Confirmação Pagamento', 'Eventos', 'Minha Conta', 'Notificação', 'Selecionar Ingresso');

		foreach($pages as $page_title){
			$check = get_page_by_title($page_title);

			if(!$check){
				$id = wp_insert_post(array(
					'post_title'	=> $page_title,
				));

				// if( !is_wp_error($id) ){
				// 	update_post_meta($id, '_wp_page_template', $new_page_template);
				// }

			}


		}
	}
}
