<?php

class VHR_Setup_Eventos  {
  function __construct(){
    add_action('admin_menu', array( $this, 'setup_page'));
    add_action('admin_post_pagseguro_options', array($this, 'save_pagseguro'));
    add_action('admin_post_eventos_options', array($this, 'save_eventos_setup'));
  }

  function setup_page(){
    add_submenu_page("edit.php?post_type=eventos", "Configurações PagSeguro", "Configurações PagSeguro", "manage_options", "pagseguro", array( $this, "pagseguro_setup_page"));
    add_submenu_page("edit.php?post_type=eventos", "Configurações Eventos", "Configurações Eventos", "manage_options", "setup_eventos", array( $this, "setup_eventos_page"));

  }

  function setup_eventos_page(){
    ?>
    <div class="wrap">
      <h1><?php echo get_admin_page_title(); ?></h1>
      <p></p>
      <form action="admin-post.php" method="post">
        <input type="hidden" name="action" value="eventos_options">
        <?php wp_nonce_field('eventos_options') ?>
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row">
                 <label for="visitantes">Somente ingressos para visitantes?</label>
              </th>
              <td>
                <input type="checkbox" name="visitantes" value="1" disabled <?php checked( get_option('visitantes_option', 1), 1 ); ?>>
              </td>
            </tr>
            <tr>
              <th scope="row">
                 <label for="limit_days">Limitar a quantidade de ingressos?</label>
              </th>
              <td>
                <input type="checkbox" name="limit_days" value="1" <?php checked( get_option('limit_days', 1), 1 ); ?>>
              </td>
            </tr>
            <tr>
              <th scope="row">
                 <label for="rows_limit">Quantidade de dias do evento</label>
              </th>
              <td>
                <input type="number" name="rows_limit" min="1" value="<?php echo get_option('rows_limit', 3); ?>">
              </td>
            </tr>
          </tbody>
        </table>
        <?php submit_button() ?>
      </form>
    </div>
    <?php
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
            <tbody>
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
                  <input type="checkbox" name="sandbox" value="1" <?php checked( get_option('sandbox', 1), 1 ); ?>>
                </td>
              </tr>
            </tbody>
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

  function save_eventos_setup(){
    check_admin_referer('eventos_options');

    $visitantes = ('' == $_POST['visitantes']) ? 1 : $_POST['visitantes'];
    $limit = $_POST['limit_days'];
    $rows_limit = $_POST['rows_limit'];

    update_option('visitantes_option', $visitantes);
    update_option('limit_days', $limit);
    update_option('rows_limit', $rows_limit);

    wp_redirect(wp_get_referer());
  }
}

new VHR_Setup_Eventos;
