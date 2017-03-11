<?php

class VHR_Loja_Build
{
	protected $term_tipo = 'tipo-dia';
	protected $pages = array(
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
		),
		array(
			'name' => 'Conta',
			'template' => 'template/page-conta.php'
		),
		array(
			'name' => 'Resumo da Compra',
			'template' => 'template/page-resumo-compra.php'
		)
	);

	protected $html = '<div style="border: 1px solid #000; width: 500px; height: 400px; font-size: 12px; font-family: Helvetica; padding: 40px;">
<div><strong><!-- Mensagem de Estado -->Sua compra de ingressos foi concluída</strong>. O seu pedido número <strong><!-- Número Pedido -->#[order] </strong>foi confirmado.</div>
<div style="border-bottom: 3.99px solid #333333; margin-bottom: 7px;">
<h2 style="font-size: 28px;">RECIBO</h2>
</div>
<div style="color: #666666; height: 200px; margin-top: 10px;">
<div style="width: 200px; line-height: 1.2; float: left; padding: 20px; border-right: 2px solid #666666;">

<!-- Dados Pedido -->
<p style="font-weight: bold;">Dados do pedido:</p>

<div>Número do Pedido: [order]</div>
<div>Data do Pedido: [orderdate]</div>
<div>Valor do pagamento:</div>
<div>[purchasevalue]</div>
<div>Código da transação:</div>
<div>[transcationcode]</div>
</div>
<div style="width: 210px; line-height: 1.2; float: left; padding: 20px;">

<!-- Dados Cliente -->
<p style="font-weight: bold;">Dados do Cliente:</p>

<div>[username]</div>
<div>Telefone: [cel]</div>
</div>
</div>
<div style="color: #666666; margin-top: 25px; height: 100px;">[barcode]<!-- Código de Barras --></div>
</div>
<div style="text-align: center; text-decoration: inherit; width: 582px; font-size: 12px; font-family: Helvetica; line-height: 1.2; color: #666666;">

Em caso de dúvidas, acesse o seu <a href="#">Histórico de Pedidos</a> no site da BSB ou envie um email <a href="mailto:parasac@livepass.com.br">parasac@livepass.com.br</a>.

Curta <a href="#">nossa página</a> no Facebook para manter-se atualizado sobre nosso eventos e promoções.

</div>';

	public function __construct()
	{
		add_action('admin_init', array($this, 'vhr_standard_tipo_dia'));
		add_action('init', array($this, 'add_standard_pages'));
		add_action('init', function(){
			add_option('mail_template', $this->html);
		});
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
				VHR_Helpers::register_new_page($page['name'], '', $page['template']);
		}
	}
}

new VHR_Loja_Build;
