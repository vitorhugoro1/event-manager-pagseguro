<?php

class VHR_Loja_Build
{
	protected static $term_tipo = 'tipo-dia';

	public static function init(){
		add_action('admin_init', array(__CLASS__, 'vhr_standard_tipo_dia'));
	}

	public function vhr_standard_tipo_dia(){
		$standard_types = array('Organização', 'Exposição');
		$taxonomy = self::$term_tipo;

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



}

VHR_Loja_Build::init();