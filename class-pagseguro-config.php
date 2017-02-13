<?php

/**
 *
 */
class VHR_PagSeguro
{

  function __construct()
  {

  }

  public function add_pagseguro_init(){
    $credentials = array(
      'email' => get_option('email_pagseguro','email@email.com.br'),
      'token' => get_option('token_pagseguro', '')
    );

    $sandbox = get_option('sandbox', 0);

    \PagSeguro\Library::initialize();
    \PagSeguro\Library::cmsVersion()->setName("VHR")->setRelease("1.0.0");
    \PagSeguro\Library::moduleVersion()->setName("VHR")->setRelease("1.0.0");

    if($sandbox == 1){
      \PagSeguro\Configuration\Configure::setEnvironment("sandbox");
      $this->pagseguro_script_callback($sandbox);
    } else {
      \PagSeguro\Configuration\Configure::setEnvironment('production');
      $this->pagseguro_script_callback($sandbox);
    }

    \PagSeguro\Configuration\Configure::setAccountCredentials($credentials['email'], $credentials['token']);

    \PagSeguro\Configuration\Configure::setCharset('UTF-8');// UTF-8 or ISO-8859-1

  }

  function pagseguro_script_callback($sandbox){
    if(1 == $sandbox){
      ?>
      <script type="text/javascript" src="https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js">
      </script>
      <?php
    } else {
      ?>
      <script type="text/javascript" src="https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js">
      </script>
      <?php
    }

  }

  public function generate_action(){
    extract($_POST);


  }
}

new VHR_PagSeguro();
