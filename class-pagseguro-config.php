<?php

/**
 *
 */
class VHR_PagSeguro
{
  protected $paymentsMethods = array(
    array(
      'title' => 'Cartão de Crédito',
      'value' => 'CREDIT_CARD'
    ),
    array(
      'title' => 'Boleto',
      'value' => 'BOLETO'
    ),
    array(
      'title' => 'Débito online',
      'value' => 'EFT'
    ),
    array(
      'title' => 'Saldo',
      'value' => 'BALANCE'
    ),
    array(
      'title' => 'Depósito em conta',
      'value' => 'DEPOSIT'
    ),
  );

  function __construct()
  {
    add_action('admin_menu', array( $this, 'setup_page'));
    add_action('admin_post_pagseguro_options', array($this, 'save_pagseguro'));
  }

  function setup_page(){
    add_submenu_page("edit.php?post_type=eventos", "Configurações PagSeguro", "Configurações PagSeguro", "manage_options", "pagseguro", array( $this, "pagseguro_setup_page"));
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
      // $this->pagseguro_script_callback($sandbox);
    } else {
      \PagSeguro\Configuration\Configure::setEnvironment('production');
      // $this->pagseguro_script_callback($sandbox);
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

  function pagseguro_setup_page(){
    ?>
      <div class="wrap">
        <h1><?php echo get_admin_page_title(); ?></h1>
        <p>Configurações do PagSeguro para configurar o sistema de pagamentos.</p>
        <form action="admin-post.php" method="post">
          <input type="hidden" name="action" value="pagseguro_options">
          <?php wp_nonce_field('pagseguro_options'); ?>
          <table class="form-table">
              <tr>
                <th scope="row">
                  <label for="email">Email da Conta</label>
                </th>
                <td>
                  <input class="regular-text" type="email" name="email" id="email" value="<?php echo get_option('email_pagseguro'); ?>">
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <label for="token">Token da Conta</label>
                </th>
                <td>
                  <input class="regular-text" type="text" name="token" id="token" value="<?php echo get_option('token_pagseguro'); ?>">
                </td>
              </tr>
              <tr>
                <th scope="row">
                   <label for="sandbox">Usar Sandbox?</label>
                </th>
                <td>
                  <input type="checkbox" name="sandbox" id="sandbox" value="1" <?php checked( get_option('sandbox', 1), 1 ); ?>>
                </td>
              </tr>
          </table>
          <br class="clear">
          <h2>Configurações avançadas</h2>
          <table class="form-table">
            <tr valign="top">
              <th scope="row">
                <label for="acceptPayment">Tipos de pagamento aceito</label>
              </th>
              <td>
                <fieldset>
                  <legend class="screen-reader-text"><span>Tipos de pagamento aceito</span></legend>
                  <?php foreach($this->paymentsMethods as $method):
                    $options = get_option( 'acceptPayment', array('CREDIT_CARD') );
                    $checked = checked( in_array($method['value'], $options), true, false );
                    ?>
                    <label for="<?=$method['value']?>">
                      <input type="checkbox" name="acceptPayment[]" id="<?=$method['value']?>" value="<?=$method['value']?>" <?=$checked?> disabled>
                      <span><?=$method['title']?></span>
                    </label>
                  <?php endforeach; ?>
                </fieldset>
                <p class="description">
                  Selecione ao menos um tipo.
                </p>
              </td>
            </tr>
          </table>
          <?php submit_button(); ?>
        </form>
      </div>
    <?php
  }

  function save_pagseguro(){
    check_admin_referer('pagseguro_options');

    $email = sanitize_email($_POST['email']);
    $token = sanitize_text_field($_POST['token']);
    $sandbox = $_POST['sandbox'];

    update_option('email_pagseguro', $email);
    update_option('token_pagseguro', $token);
    update_option('sandbox', $sandbox);

    wp_redirect(wp_get_referer());
  }

}

new VHR_PagSeguro();
