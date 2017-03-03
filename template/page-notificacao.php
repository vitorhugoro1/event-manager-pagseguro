<?php
header("access-control-allow-origin: https://sandbox.pagseguro.uol.com.br");

if(count($_POST) > 0){
  $pagseguro = new VHR_PagSeguro();
  $pagseguro->add_pagseguro_init();

  $response = \PagSeguro\Services\Transactions\Notification::check(
      \PagSeguro\Configuration\Configure::getAccountCredentials()
  );

  $code = $response->getCode();

  $args = array(
    'post_type'   => 'ingresso',
    'post_status' => 'publish',
    'posts_per_page'  => 1,
    'meta_key'  => 'notification_code',
    'meta_value'  => $code,
    'fields'  => 'ids'
  );

  $orderID = get_posts($args);

  if(!is_wp_error($orderID)){
    $status = $response->getStatus();

    if($status != 1){
      update_post_meta( $orderID[0], 'transaction_state', $status);

      if($status == 7){
        update_post_meta( $orderID[0], 'status', 'cancelado' );
      }
    }
    wp_send_json_success($orderID);
  } else {
    wp_send_json_error();
    return false;
  }

} else {
  header('Location:' . home_url());
}
