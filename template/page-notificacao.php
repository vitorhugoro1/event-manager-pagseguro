<?php
header("access-control-allow-origin: https://sandbox.pagseguro.uol.com.br");
error_log("NOTIFICAO_ATIVADA");

if(count($_POST) > 0){
  $pagseguro = new VHR_PagSeguro();
  $pagseguro->add_pagseguro_init();

  try {
      if (\PagSeguro\Helpers\Xhr::hasPost()) {
          $response = \PagSeguro\Services\Transactions\Notification::check(
              \PagSeguro\Configuration\Configure::getAccountCredentials()
          );
      } else {
          throw new \InvalidArgumentException($_POST);
      }

      echo "<pre>";
      print_r($response);
  } catch (Exception $e) {
      die($e->getMessage());
  }
} else {
  header('Location:' . home_url());
}
