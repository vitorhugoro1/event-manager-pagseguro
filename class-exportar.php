<?php

/**
 *
 */
class VHR_Exportar
{
  protected $states = array(
    1 => 'Aguardando pagamento',
    2 => 'Em análise',
    3 => 'Paga',
    4 => 'Disponível',
    5 => 'Em disputa',
    6 => 'Devolvida',
    7 => 'Cancelada'
  );

  protected $comprador = array(
    'visitante' => 'Visitante',
    'expositor' => 'Expositor'
  );

  function __construct()
  {
    add_action('admin_menu', array($this, 'menu'));
    add_action( 'admin_post_exportar-ingresso', array($this, 'action') );
  }

  function menu(){
    add_submenu_page("edit.php?post_type=ingresso", "Exportar ingressos", "Exportar ingressos", "manage_options", "exportar-ingresso", array( $this, "form"));
  }

  function form(){
    ?>
      <div class="wrap">
        <h1><?=get_admin_page_title()?></h1>
        <div>
          <form action="admin-post.php" id="exportar" method="post">
            <input type="hidden" name="action" value="exportar-ingresso">
            <?php wp_nonce_field('exportar-ingresso') ?>
            <table class="form-table">
              <tr valign="top">
                <th scope="row">
                    <label for="evento">Selecione o evento que deseja exportar*</label>
                </th>
                <td>
                  <?php $eventos = get_posts(array('post_type' => 'eventos', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' )); ?>
                  <select id="evento" name="evento" required>
                    <?php
                    if(!empty($eventos)){
                      echo '<option value="">Selecione um evento</option>';
                      foreach($eventos as $post):
                        ?>
                          <option value="<?=$post?>"><?=get_the_title($post)?></option>
                        <?php
                      endforeach;
                    } else {
                      ?>
                      <option value="">Sem eventos cadastrados</option>
                      <?php
                    } ?>
                  </select>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row">
                  <label for="estado">Selecione o estado da transação</label>
                </th>
                <td>
                  <select id="estado" name="estado">
                    <option value="">Selecione um estado</option>
                    <?php foreach($this->states as $k => $v): ?>
                      <option value="<?=$k?>"><?=$v?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row">
                  <label for="tipo">Selecione o tipo de comprador</label>
                </th>
                <td>
                  <select id="tipo" name="tipo">
                    <option value="">Selecione um tipo</option>
                    <?php foreach($this->comprador as $k => $v): ?>
                      <option value="<?=$k?>"><?=$v?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
            </table>
            <?php submit_button("Exportar") ?>
          </form>
        </div>
      </div>
    <?php
  }

  function action(){
    check_admin_referer('exportar-ingresso');

    $args = array(
      'post_type'       => 'ingresso',
      'post_status'     => 'publish',
      'posts_per_page'  => -1,
      'meta_query'      => array(
        'relation'    => 'AND'
      ),
      'fields'          => 'ids'
    );

    $args['meta_query'][] = array(
      'key'     => 'evento_id',
      'compare' => '=',
      'value'   => $_POST['evento']
    );

    if(isset($_POST['estado']) && !empty($_POST['estado'])){
      $args['meta_query'][] = array(
        'key'     => 'transaction_state',
        'compare' => '=',
        'value'   => $_POST['estado']
      );
    }

    $ingressos = get_posts($args);

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator(get_the_author_meta( 'display_name' ))
							 ->setLastModifiedBy(get_the_author_meta( 'display_name' ));

    $objPHPExcel->getActiveSheet()->getStyle('1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle('G2:G256')
    ->getNumberFormat()
    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

    $objPHPExcel->setActiveSheetIndex(0)
           ->setCellValue('A1', 'Nome')
           ->setCellValue('B1', 'Celular')
           ->setCellValue('C1', 'E-mail')
           ->setCellValue('D1', 'Evento')
           ->setCellValue('E1', 'Tipo de ingresso')
           ->setCellValue('F1', 'ID Gerado')
           ->setCellValue('G1', 'Valor')
           ->setCellValue('H1', 'Data');

   $objPHPExcel->getActiveSheet()->setAutoFilter('A1:H1');

    if( ! empty($ingressos) ){
      foreach($ingressos as $key => $ingresso){
                $line = intval($key)+2; // Definindo a linha
                $user_id = get_post_meta( $ingresso, 'user_id', true );
                $evento = get_post_meta( $ingresso, 'evento_id', true );

                if(isset($_POST['tipo']) && ! empty($_POST['tipo']))
                {
                  if(get_the_author_meta( 'tipo', $user_id ) != $_POST['tipo']){
                    continue;
                  }
                }

                $in = new VHR_Ingresso_Functions;
                $ref = (get_post_meta( $ingresso, 'ref', true )) ? get_post_meta( $ingresso, 'ref', true ) : $in->pag_ref_gen($ingresso);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $line, get_the_author_meta( 'display_name', $user_id));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $line, '('.get_the_author_meta( 'ddd', $user_id).') '. get_the_author_meta( 'tel', $user_id));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $line, get_the_author_meta( 'user_email', $user_id));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $line, get_the_title( $evento ));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $line, get_the_author_meta( 'tipo', $user_id));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $line, $ref);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $line, get_post_meta( $ingresso, 'valor', true ));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $line, get_the_date( 'd/m/Y H:i', $ingresso ));
        }
    }



    $objPHPExcel->getActiveSheet()->setTitle('Relatório');
    $objPHPExcel->setActiveSheetIndex(0);

    $name = get_the_title($_POST['evento']) . ' - ' . date('d/m/Y') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $name . '"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');
    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: no-pragma'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    wp_die();
  }
}

new VHR_Exportar;
