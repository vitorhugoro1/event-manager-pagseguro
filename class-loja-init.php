<?php

class VHR_Loja_Build
{
	protected $term_tipo = 'tipo-dia';
	protected $pages = array(
		array(
			'name' => 'Confirmação Pagamento',
			'template'	=> 'template/page-confirmacao-pagamento.php'
		),
		array(
			'name' => 'Eventos',
			'template'	=> 'template/page-eventos.php'
		),
		array(
			'name' => 'Minha Conta',
			'template'	=> 'template/page-minha-conta.php'
		),
		array(
			'name' => 'Notificação',
			'template'	=> 'template/page-notificacao.php'
		),
		array(
			'name' => 'Selecionar Ingresso',
			'template' => 'template/page-selecionar-ingresso.php'
		),
		array(
			'name' => 'Cadastrar',
			'template' => 'template/page-cadastrar.php'
		)
	);

	public function __construct()
	{
		add_action('admin_init', array($this, 'vhr_standard_tipo_dia'));
		add_action('init', array($this, 'add_standard_pages'));
	}

	public function vhr_standard_tipo_dia(){
		$standard_types = array('Organização', 'Exposição');
		$taxonomy = $this->term_tipo;

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
		$pages = $this->pages;

		foreach($pages as $page){
				register_new_page($page['name'], '', $page['template']);
		}
	}
}

new VHR_Loja_Build;
