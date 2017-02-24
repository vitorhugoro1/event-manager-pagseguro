<?php
/**
 * Functions for help with load new characters
 * in the admin pages.
 */

/**
 * Removes metabox from appearing on post new screens before the post
 * ID has been set.
 *
 *
 * @param bool  $display
 * @param array $meta_box The array of metabox options
 *
 * @return bool $display True on success, false on failure
 */
function vhr_exclude_from_new($display, $meta_box)
{
    if (!isset($meta_box['show_on']['alt_key'], $meta_box['show_on']['alt_value'])) {
        return $display;
    }

    global $pagenow;

  // Force to be an array
  $to_exclude = !is_array($meta_box['show_on']['alt_value'])
  ? array($meta_box['show_on']['alt_value'])
  : $meta_box['show_on']['alt_value'];

    $is_new_post = 'post-new.php' == $pagenow && in_array('post-new.php', $to_exclude);

    return !$is_new_post;
}

add_filter('cmb2_show_on', 'vhr_exclude_from_new', 10, 2);

function vhr_title_code($data, $postarr)
{
    if ($data['post_type'] == 'ingresso') {
        $title = '#'.$postarr['ID'];
        $data['post_title'] = $title;
    }

    return $data;
}

add_filter('wp_insert_post_data', 'vhr_title_code', '99', 2);

function register_new_page($new_page_title, $new_page_content, $new_page_template)
{
    $new_page_id = null;

    $page_check = get_page_by_title($new_page_title);
    $new_page = array(
            'post_type' => 'page',
            'post_title' => $new_page_title,
            'post_content' => $new_page_content,
            'post_status' => 'publish',
            'post_author' => 1,
    );
    if (!isset($page_check->ID)) {
        $new_page_id = wp_insert_post($new_page);
        if (!empty($new_page_template)) {
            update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
        }
    }

    return $new_page_id;
}

function filter_content($content){
  global $post;

  if('eventos' == get_post_type($post) && ! is_single($post) ||
  'eventos' == get_post_type($post) && is_single()){
    $valores = get_post_meta( $post->ID, '_vhr_valores', true );
    $datas = get_post_meta( $post->ID, '_vhr_periodo', true );
    $inicio = DateTime::createFromFormat('d/m/Y', $datas['start']);
    $fim = DateTime::createFromFormat('d/m/Y', $datas['end']);
    $hoje = new DateTime();
    $comprarID = get_page_by_title('Selecionar Ingresso');
    $link = get_the_permalink($comprarID->ID);

    ob_start();
      echo '<ul>';
        foreach ($valores as $val) {
          $day = new VHR_Loja_Meta_Boxes();
          if($val['multiplo']){
            $dia = $day->get_day_event($post->ID, $val['dia-multiplo'], true);
          } else {
            $dia = $day->get_day_event($post->ID, $val['dia-simples']);
          }
          echo sprintf('<li>%s ( %s ) - R$ %s</li>', $val['label'], $dia, $val['valor']);
        }
      echo '</ul>';

      if($hoje > $inicio && $hoje < $fim){
        echo sprintf('<a href="%s?refID=%d" class="tickera_button">Comprar</a>', $link, $post->ID);
      }

    $content .= ob_get_clean();
  }

  return $content;
}

function add_table_ingresso($content){
  global $post;

  if(is_page( 'selecionar-ingresso' )){
    ob_start();
    $refID = intval($_GET['refID']);
    $valores = get_post_meta( $refID, '_vhr_valores', true );
    ?>
      <div class="om-columns">
        <div class="om-column om-full">
            Evento : <?php echo get_the_title( $refID ); ?>
        </div>
        <div class="om-column om-two-third">
          <div class="select-ingresso">
            <label for="tipo-ingresso">Selecione um ingresso</label>
            <select id="tipo-ingresso">
              <option value="">Selecione um ingresso</option>
              <?php foreach((array) $valores as $k => $valor) : ?>
                <option value="<?php echo $k; ?>"><?php echo $valor['label']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="qtd-ingresso">
            <label for="qtd-ingresso">Quantidade de ingressos</label>
            <input type="number" id="qtd-ingresso" min="1" value="1">
          </div>
        </div>
        <div class="om-column om-one-third">
          <input type="hidden" id="refID" value="<?php echo $refID; ?>">
          <input type="hidden" id="action-url" value="<?php echo admin_url('admin-post.php'); ?>">
          <button id="adc-ingresso">Adicionar</button>
        </div>
      </div>
      <div class="vc_om-table selecionar-table">
        <form action="<?php echo home_url('/confirmacao-pagamento'); ?>" method="post">
          <input type="hidden" name="refID" value="<?php echo intval($refID); ?>">
          <table id="table-form">
            <thead>
              <tr>
                <th>
                  <input type="checkbox">
                </th>
                <th>
                  Tipo ingresso
                </th>
                <th>
                  Quantidade
                </th>
                <th>
                  Valor
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="3">
                  <span class="alignright" style="margin:0;">Total</span>
                </th>
                <th>
                  <input type="hidden" name="valor" id="total" value="00.00">
                  <span id="total-span">00,00</span>
                </th>
              </tr>
            </tfoot>
          </table>
          <a href="javascript:void(0);" id="rmv-ingresso">Remover</a>
          <button type="submit">Continuar</button>
        </form>
      </div>
    <?php
    $content .= ob_get_clean();
  }

  return $content;
}

function add_finalizar($content){
  global $post;

  if(is_page('confirmacao-pagamento')){
    $pagseguro = new VHR_PagSeguro();
    $pagseguro->add_pagseguro_init();
    ob_start();
    extract($_POST);
    $valores = get_post_meta( $refID, '_vhr_valores', true );
    ?>
    <script src="http://malsup.github.com/jquery.form.js"></script>
    <div class="vc_om-table selecionar-table">
      <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="finalize">
        <input type="hidden" name="action" value="pag_code_gen">
        <?php wp_nonce_field('finalize'); ?>
        <input type="hidden" name="refID" value="<?php echo intval($refID); ?>">
        <table id="table-form">
          <thead>
            <tr>
              <th>
                Tipo ingresso
              </th>
              <th>
                Quantidade
              </th>
              <th>
                Valor
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($ingressos as $k => $ingresso) : ?>
                <tr>
                  <td>
                    <input type="hidden" data-elem="tipo" name="ingressos[<?php echo $k ?>][tipo]" value="<?php echo $ingresso['tipo']; ?>">
                    <?php echo $valores[$ingresso['tipo']]['label']; ?>
                  </td>
                  <td>
                    <input type="hidden" data-elem="qtd" name="ingressos[<?php echo $k ?>][qtd]" value="<?php echo $ingresso['qtd']; ?>">
                    <?php echo $ingresso['qtd'] ?>
                  </td>
                  <td>
                    <input type="hidden" data-elem="valor" name="ingressos[<?php echo $k ?>][valor]" value="<?php echo floatval($ingresso['valor']); ?>">
                    <?php echo number_format(floatval($ingresso['valor']), 2, ',', '.');?>
                  </td>
                </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2">
                <span class="alignright" style="margin:0;">Total</span>
              </th>
              <th>
                <input type="hidden" name="valor" id="total" value="<?php echo floatval($valor); ?>">
                <span id="total-span"><?php echo number_format(floatval($valor), 2, ',', '.'); ?></span>
              </th>
            </tr>
          </tfoot>
        </table>
        <a href="javascript:window.history.back();">Cancelar</a>
        <button type="submit">Finalizar</button>
      </form>
      <input type="hidden" id="redirect_url" value="<?php echo home_url('/minha-conta'); ?>">
    </div>
    <?php
    $content .= ob_get_clean();
  }

  return $content;
}

add_filter( 'the_content', 'filter_content' );
add_filter( 'the_content', 'add_table_ingresso' );
add_filter( 'the_content', 'add_finalizar' );
