<?php

if(!class_exists('VHR_Loja_Meta_Boxes')){
	class VHR_Loja_Meta_Boxes {

		protected static $prefix = '_vhr_';

		public static function init(){
			add_action('cmb2_admin_init', array(__CLASS__, 'vhr_metaboxes_cmb2'));
			add_action('cmb2_after_post_form_data_metabox', array(__CLASS__, 'vhr_limit_group_repeat'), 10, 2 );
			add_action('admin_footer-edit-tags.php', array(__CLASS__, 'vhr_remove_cat_tags'));
			add_action('admin_footer-term.php', array(__CLASS__, 'vhr_remove_cat_tags'));
			add_filter('manage_edit-tipo-dia_columns',array(__CLASS__, 'vhr_remove_taxonomy_columns'));
		}

		public function vhr_metaboxes_cmb2()
		{

			/*
			 * Mapa de mesas
			 */

			$mapa = new_cmb2_box(array(
				'id' => 'mapa_metabox',
				'title' => 'Mapa de mesas do evento',
				'object_types' => array('eventos'),
				'context' => 'side',
				'priority' => 'core',
				'show_names' => false,
			));

			$mapa->add_field(array(
				'id' => self::$prefix.'mesas',
				'title' => 'Mapa de mesas do evento',
				'desc' => '',
				'type' => 'file',
				'options' => array(
					'url' => false,
				),
				'text' => array(
					'add_upload_file_text' => 'Adicionar imagem do mapa de mesas',
				),
			));

			/*
			 * Datas para o Evento
			 */

			$data = new_cmb2_box(array(
				'id' => 'data_metabox',
				'title' => 'Dias de ocorrência do evento',
				'object_types' => array('eventos'),
				'context' => 'side',
				'priority' => 'core',
				'show_names' => true,
				'rows_limit'   => get_option('rows_limit', 3)
			));

			$data_group_id = $data->add_field(array(
				'id' => self::$prefix.'data',
				'type' => 'group',
				'options' => array(
					'group_title' => 'Dia {#}',
					'add_button' => 'Adicionar data',
					'remove_button' => 'Remover data',
					'sortable' => true, // beta
				),
				'column' => array(
					'position'	=> 2,
					'name'			=> 'Datas do evento'
				),
				'display_cb'	=> array(__CLASS__, 'display_data_column')
			));

			$data->add_group_field($data_group_id, array(
				'name' => 'Dia',
				'id' => 'data',
				'type' => 'text_date_timestamp',
				'date_format' => 'd/m/Y',
				'attributes' => array(
					'placeholder' => 'dd/mm/yyyy',
					'required' => true, // Will be required only if visible.
				),
			));

			$data->add_group_field($data_group_id, array(
				'name' => 'Tipo de dia',
				'id' => 'tipo',
				'type' => 'select',
				'show_option_none' => false,
				'default' => 'exposicao',
				'options' => array(__CLASS__, 'vhr_load_tipo_dia_values'),
			));

			$data_evento = new_cmb2_box(array(
				'id' => 'data_evento',
				'title' => 'Período de Vendas de Ingressos',
				'object_types' => array('eventos'),
				'context' => 'side',
				'priority' => 'high',
				'show_names' => false,
			));

			$data_evento->add_field(array(
				'name'	=> 'Período de Vendas de Ingressos',
				'id'		=> self::$prefix.'periodo',
				'type'	=> 'date_range',
				'attributes' => array(
					'required' => true, // Will be required only if visible.
				),
				'date_format' => 'd/m/Y',
				'column'	=> array(
					'position'	=> 2,
					'name'			=> 'Período de Vendas'
				),
				'data-daterange' => json_encode( array(
					'buttontext' => 'Selecione o range',
				) ),
				'display_cb'	=> array(__CLASS__, 'display_periodo_column')
			));

			$valores = new_cmb2_box(array(
				'id' => 'valores_metabox',
				'title' => 'Valores dos ingressos para visitantes',
				'object_types' => array('eventos'),
				'context' => 'normal',
				'priority' => 'core',
				'show_names' => true,
				'show_on' => array('alt_key' => 'valores', 'alt_value' => 'post-new.php'),
			));

			$valores_group_id = $valores->add_field(array(
				'id' => self::$prefix.'valores',
				'type' => 'group',
				'options' => array(
					'group_title' => 'Ingresso {#}',
					'add_button' => 'Adicionar novo valor',
					'remove_button' => 'Remover valor',
					'sortable' => true, // beta
				),
			));

			$valores->add_group_field($valores_group_id, array(
				'name' => 'Label',
				'id' => 'label',
				'desc'	=> 'Identificador do tipo de ingresso',
				'type' => 'text_medium',
			));

			$valores->add_group_field($valores_group_id, array(
				'name' => 'Valor',
				'id' => 'valor',
				'type' => 'text_money',
				'before_field' => 'R$',
			));

			$valores->add_group_field($valores_group_id, array(
				'name' => 'Vai ser para multiplos dias?',
				'id' => 'multiplo',
				'type' => 'checkbox',
			));

			$valores->add_group_field($valores_group_id, array(
				'name' => 'Dias',
				'id' => 'dia-multiplo',
				'type' => 'multicheck',
				'options' => array(__CLASS__, 'vhr_load_data_values'),
				'attributes' => array(
					'data-conditional-id' => wp_json_encode(array($valores_group_id, 'multiplo')),
					'data-conditional-value' => 'on',
				),
			));

			$valores->add_group_field($valores_group_id, array(
				'name' => 'Dia',
				'id' => 'dia-simples',
				'type' => 'radio_inline',
				'options' => array(__CLASS__, 'vhr_load_data_values'),
				'attributes' => array(
					'required' => true, // Will be required only if visible.
					'data-conditional-id' => wp_json_encode(array($valores_group_id, 'multiplo')),
					'data-conditional-value' => 'off',
				),
			));
		}

		public function vhr_load_data_values()
		{
			global $post_ID, $pagenow;

			$datas = array();

			if ($pagenow == 'post-new.php') {
				return array();
			}

			$dates = get_post_meta($post_ID, '_vhr_data', true);

			foreach ((array) $dates as $key => $date) {
				$datas[$key] = date('d/m/Y', $date['data']);
			}

			return $datas;
		}

		public function vhr_load_tipo_dia_values(){
			global $post_ID, $pagenow;
			$list = array();

			$tipos = get_terms( 'tipo-dia', array('hide_empty' => false) );

			foreach($tipos as $tipo){
				$list[$tipo->slug] = $tipo->name;
			}

			return $list;
		}

		public function vhr_remove_cat_tags()
		{
			global $current_screen, $pagenow;

			if ('edit-tags.php' == $pagenow) {
				switch ($current_screen->id) {
					case 'edit-tipo-dia':
						?>
						<script type="text/javascript">
                            jQuery(document).ready( function($) {
                                $('#parent').closest('.form-field').remove();
                                $('#tag-slug').closest('.form-field').remove();
                            });
						</script>
						<?php
						break;
				}
			} elseif ('term.php' == $pagenow) {
				switch ($current_screen->id) {
					case 'edit-tipo-dia':
						?>
						<script type="text/javascript">
                            jQuery(document).ready( function($) {
                                $('#parent').closest('.form-field').remove();
                                $('#slug').closest('.form-field').remove();
                            });
						</script>
						<?php
						break;
				}
			}
		}

		public function vhr_remove_taxonomy_columns($columns)
		{
			if ( !isset($_GET['taxonomy']) )
				return $columns;

			if ( $posts = $columns['description'] ){ unset($columns['description']); unset($columns['posts']); }
			return $columns;
		}

		public function display_periodo_column($field_args, $field ) {
			$data = $field->escaped_value();
			if(!empty($data)):
			?>
	    <div class="custom-column-display <?php echo $field->row_classes(); ?>">
	        <p><?php echo $data['start'] . ' - ' . $data['end']; ?></p>
	    </div>
	    <?php
			else:
			?>
			<div class="custom-column-display <?php echo $field->row_classes(); ?>">
					<p><i>Período não definido ou com erro</i></p>
			</div>
			<?php
		 endif;
		}

		public function display_data_column($field_args, $field ) {
			$datas = $field->value;
			$post_id = $field->object_id;
			?>
	    <div class="custom-column-display <?php echo $field->row_classes(); ?>">
	        <p><?php
							foreach((array) $datas as $data){
								$d = date('d/m/Y', $data['data']);
								$tipo = get_term_by('slug', $data['tipo'], 'tipo-dia');
								echo sprintf('%s - %s </br>', $d, $tipo->name );
							}
					  ?></p>
	        <p class="description"><?php echo $field->args( 'description' ); ?></p>
	    </div>
	    <?php
		}

		public function vhr_limit_group_repeat( $post_id, $cmb ) {
			// Grab the custom attribute to determine the limit
			$limit = absint( $cmb->prop( 'rows_limit' ) );
			$limit = $limit ? $limit : 0;
			?>
			<script type="text/javascript">
                jQuery(document).ready(function($){
                    // Only allow 3 groups
                    var limit            = <?php echo $limit; ?>;
                    var fieldGroupId     = '_vhr_data'; // This should match the ID of your group field.
                    var $fieldGroupTable = $( document.getElementById( fieldGroupId + '_repeat' ) );
                    var countRows = function() {
                        return $fieldGroupTable.find( '> .cmb-row.cmb-repeatable-grouping' ).length;
                    };
                    var disableAdder = function() {
                        $fieldGroupTable.find('.cmb-add-group-row.button').prop( 'disabled', true );
                    };
                    var enableAdder = function() {
                        $fieldGroupTable.find('.cmb-add-group-row.button').prop( 'disabled', false );
                    };
                    $fieldGroupTable
                        .on( 'cmb2_add_row', function() {
                            if ( countRows() >= limit ) {
                                disableAdder();
                            }
                        })
                        .on( 'cmb2_remove_row', function() {
                            if ( countRows() < limit ) {
                                enableAdder();
                            }
                        });
                });
			</script>
			<?php
		}

	public function get_day_event($post_id, $key, $multi = false){
		$days = get_post_meta( $post_id, '_vhr_data', true );
		$dates = '';

		if(!$multi){
			$d = date('d/m/Y', $days[$key]['data']);
			$dates .= $d;
		} else {
			foreach($key as $k => $v){
				$d = date('d/m/Y', $days[$v]['data']);
				$dates .= $d . (( isset ($key[$k+1]) ) ? ', ' : '');
			}
		}

		return $dates;
	}
	}
}

VHR_Loja_Meta_Boxes::init();
