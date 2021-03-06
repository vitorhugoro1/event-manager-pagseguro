<?php
error_reporting(E_ALL);
/**
 *
 */
class VHR_Helpers
{
  protected $tags_list = array('[barcode]', '[order]', '[username]', '[firstname]', '[event]', '[purchasevalue]', '[purchaseitems]', '[transcationcode]', '[orderdate]', '[address]', '[numberAddress]', '[compAddress]', '[cep]', '[city]', '[state]', '[cel]');
  protected $estados = array(
      1 => 'Aguardando pagamento',
      2 => 'Em análise',
      3 => 'Paga',
      4 => 'Disponível',
      5 => 'Em disputa',
      6 => 'Devolvida',
      7 => 'Cancelada',
    );

  function __construct()
  {
    add_filter('wp_insert_post_data', array($this,'vhr_title_code'), '99', 2);
    add_filter('cmb2_show_on', array($this, 'vhr_exclude_from_new'), 10, 2);
    add_filter( 'login_redirect', array($this, 'my_login_redirect'), 10, 3 );
    add_action('admin_post_print_recibo', array($this, 'print_recibo'));
    add_action('admin_post_barcode_generator', array($this, 'barcode_generator'));
    add_action('admin_post_nopriv_barcode_generator', array($this, 'barcode_generator'));
  }

  function vhr_title_code($data, $postarr)
  {
      if ($data['post_type'] == 'ingresso') {
          $title = '#'.$postarr['ID'];
          $data['post_title'] = $title;
      }

      return $data;
  }

  function my_login_redirect( $redirect_to, $request, $user ) {
  	//is there a user to check?
  	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
  		//check for admins
  		if ( in_array( 'administrator', $user->roles ) ) {
  			// redirect them to the default place
  			return $redirect_to;
  		} else {
  			return home_url();
  		}
  	} else {
  		return $redirect_to;
  	}
  }

  public function register_new_page($new_page_title, $new_page_content, $new_page_template)
  {
      $new_page_id = null;

      $page_check = get_page_by_path(sanitize_title($new_page_title));
      $new_page = array(
              'post_type' => 'page',
              'post_title' => $new_page_title,
              'post_content' => $new_page_content,
              'post_status' => 'publish',
              'post_author' => 1,
      );
      if (!isset($page_check->ID)) {
          $new_page_id = wp_insert_post($new_page);
          update_post_meta($new_page_id, 'eventerra_sidebar_show', 'hide');
          if (!empty($new_page_template)) {
              update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
          }
      }

      return $new_page_id;
  }

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

  public function mail_template_builder($args){
    extract(array(
      'orderID' => 0,
      'user_id' => 0,
    ), $args);

    $to = get_the_author_meta( 'user_email', $user_id );
    $admin = get_option('admin_send_email') ? get_option('admin_send_email') : get_option('admin_email');
    $blogname = get_option('blogname');
    $headers = array("Content-Type: text/html; charset=UTF-8","From: $blogname <$admin>");
    $subject = "Informações do ingresso #$orderID";
    $message = $this->tags_fetch(get_option( 'mail_template' ),$orderID);

    $mail = wp_mail( $to, $subject, $message, $headers );

    return $mail;
    // if($mail){
    //   return true;
    // } else {
    //   return false;
    // }
  }

  public function tags_fetch($html, $order){
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $in = new VHR_Ingresso_Functions;
    $ref = (get_post_meta($order, 'transaction_id', true)) ? get_post_meta($order, 'transaction_id', true) : $in->pag_ref_gen($order);
    $user_id = get_post_meta($order, 'user_id', true);
    $barcode = '<img src="' . admin_url('admin-post.php') . '?action=barcode_generator&ref=' . $ref . '" alt="Código de Barras" style="display:block;margin:0 auto;">';
    $username = get_the_author_meta( 'display_name', $user_id );
    $firstname = get_the_author_meta( 'first_name', $user_id );
    $event = get_the_title( get_post_meta( $order, 'evento_id', true ) );
    $purchasevalue = 'R$ ' . get_post_meta( $order, 'valor', true );
    $purchaseitems = $this->get_order_html($order);
    $transcationcode = get_post_meta( $order, 'notification_code', true );
    $orderdate = get_the_date( 'd.m.Y H:i', $order );
    $cel = '(' . get_the_author_meta('ddd', $user_id). ') ' . get_the_author_meta('tel', $user_id);

    $values = array($barcode, $order, $username, $firstname, $event, $purchasevalue, $purchaseitems, $transcationcode, $orderdate, 'address', 'numberAddress', 'compAddress', 'cep', 'city', 'state', $cel);

    $html = str_replace($this->tags_list, $values, $html);

    return $html;
  }

  protected function get_order_html($orderID){
    ob_start();
    ?>
    <table class="widefat ingresso-table">
      <thead>
        <th>
          Tipo
        </th>
        <th>
          Quantidade
        </th>
        <th>
          Total
        </th>
      </thead>
      <tbody>
      <?php
      $total = get_post_meta($orderID, 'valor', true) ;
        $ingressos = get_post_meta($orderID,'ingressos', true);
        $evento_id = get_post_meta($orderID, 'evento_id', true );
        $in = new VHR_Ingresso_Functions;
        if(!empty($ingressos)){

          foreach((array) $ingressos as $k => $ingresso):
              ?>
              <tr data-id="<?php echo $k; ?>">
                <td>
                  <?php echo $in->get_valor_label($evento_id, $ingresso['tipo']); ?>
                </td>
                <td>
                  <?php echo $ingresso['qtd']; ?>
                </td>
                <td>
                  <?php echo 'R$ ' . number_format($ingresso['valor'], 2, ',', '.'); ?>
                </td>
              </tr>
              <?php
          endforeach;
        } else {
          ?>
            <tr class='none'>
              <td>
                <p>
                  Nenhum ingresso
                </p>
              </td>
            </tr>
          <?php
        }
      ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3">
            <label class="alignright">Valor Total</label>
          </td>
          <td>
            <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?>
          </td>
        </tr>
      </tfoot>
    </table>
    <?php
    $html = ob_get_clean();

    return $html;
  }

  public function print_recibo(){
    $nonce = $_GET['_wpnonce'];
    if(!wp_verify_nonce($nonce, 'print_recibo')){
      return 'erro';
    }

    $orderID = $_GET['id'];

    $html = $this->tags_fetch(get_option( 'mail_template' ),$orderID);

    ?>
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <title></title>
        </head>
        <body>
          <?php echo $html; ?>
        </body>
      </html>
    <?php
  }

  public function barcode_generator()
  {
    header("Content-type: image/png");
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    echo $generator->getBarcode($_GET['ref'], $generator::TYPE_CODE_128);
  }
}

new VHR_Helpers;
