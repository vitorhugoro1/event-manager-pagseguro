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

    \PagSeguro\Configuration\Configure::setAccountCredentials($credentials['email'], $credentials['token']);

    if($sandbox == 1){
      \PagSeguro\Configuration\Configure::setEnvironment("sandbox");
    }
  }
}

new VHR_PagSeguro();
