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
		 * Descrição do Evento
		 */

			$descricao = new_cmb2_box(array(
				'id' => 'descricao_metabox',
				'title' => 'Breve descrição sobre o evento',
				'object_types' => array('eventos'),
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => false,
			));

			$descricao->add_field(array(
				'id' => self::$prefix.'desc',
				'title' => 'Breve descrição sobre o evento',
				'type' => 'wysiwyg',
			));

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
				'rows_limit'   => 3
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
			));

			$data->add_group_field($data_group_id, array(
				'name' => 'Dia',
				'id' => 'data',
				'type' => 'text_date_timestamp',
				'date_format' => 'd/m/Y',
				'attributes' => array(
					'placeholder' => 'dd/mm/yyyy',
				),
			));

			$data->add_group_field($data_group_id, array(
				'name' => 'Tipo de dia',
				'id' => 'tipo',
				'type' => 'select',
				'show_option_none' => false,
				'default' => 'expo',
				'options' => array(__CLASS__, 'vhr_load_tipo_dia_values'),
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
	}
}

VHR_Loja_Meta_Boxes::init();