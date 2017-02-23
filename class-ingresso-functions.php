<?php

class VHR_Ingresso_Functions
{
  protected $estados = array(
    1 => 'Aguardando pagamento',
    2 => 'Em análise',
    3 => 'Paga',
    4 => 'Disponível',
    5 => 'Em disputa',
    6 => 'Devolvida',
    7 => 'Cancelada'
  );

  public function __construct(){
    add_action('admin_init', array($this, 'vhr_infos_box') );
    add_action('cmb2_admin_init', array($this, 'ingresso_meta_box'));
    add_action('admin_post_add_ingresso_item', array($this, 'add_ingresso_item'));
    add_action('admin_post_validation_ingresso', array($this, 'validation_ingresso'));
    add_action('admin_post_pag_code_gen', array($this, 'pag_code_gen'));
    add_action('admin_post_add_notification_code', array($this, 'add_notification_code'));
    add_action('admin_post_add_abort_status', array($this, 'add_abort_status'));
    add_action('admin_menu', array($this, 'disable_new_posts'));
    add_action('admin_footer', array($this, 'disable_new_posts_js'));
  }

  public function vhr_infos_box(){
    global $pagenow;

    if('post-new.php' !== $pagenow){
      add_meta_box( 'ingresso_info_box', 'Informações', array('VHR_Ingresso_Functions', 'vhr_infos_box_build'), 'ingresso', 'normal', 'default' );
      add_meta_box( 'vhr_ingresso_selector_box', 'Itens do Pedido', array('VHR_Ingresso_Functions', 'vhr_ingresso_selector_box'), 'ingresso', 'normal', 'default' );
      }
  }

  public function ingresso_meta_box(){
    $transaction = new_cmb2_box(array(
      'id' => 'transaction_metabox',
      'title' => 'Estado da transação',
      'object_types' => array('ingresso'),
      'context' => 'side',
      'priority' => 'core',
      'show_names' => false
    ));

    $transaction->add_field(array(
      'name'  => 'Estado da transação',
      'id'    => 'transaction_state',
      'show_option_none'  => false,
      'type'  => 'select',
      'options' => $this->estados,
      'column' => array(
        'position'	=> 2,
        'name'			=> 'Estado da transação'
      )
    ));

    $valor = new_cmb2_box(array(
      'id' => 'valor_metabox',
      'title' => 'Valor da transação',
      'object_types' => array('ingresso'),
      'context' => 'side',
      'priority' => 'core',
      'show_names' => false
    ));

    $valor->add_field(array(
      'id'    => 'valor',
      'title'  => 'Valor da transação',
      'type'    => 'text_money',
      'attributes'  => array(
        'readonly'  => 'readonly',
        'disabled'  => 'disabled',
        // 'type' => 'number',
        // 'pattern' => '\d*',
      ),
      'before_field' => 'R$',
      'column'  => array(
        'position'  => 3,
        'name'    => 'Valor da transação'
      ),
      'display_cb'  => array($this, 'show_column_valor')
    ));

    $status = new_cmb2_box(array(
      'id' => 'status_metabox',
      'title' => 'Status da transação',
      'object_types' => array('ingresso'),
      'context' => 'side',
      'priority' => 'core',
      'show_names' => false
    ));

    $status->add_field(array(
      'id'    => 'status',
      'title'  => 'Status da transação',
      'type'    => 'select',
      'options' => array(
        'ativo' => 'Ativo',
        'cancelado' => 'Cancelado'
      ),
      'column'  => array(
        'position'  => 4,
        'name'    => 'Status da transação'
      ),
    ));
  }

  public function show_column_valor($field_args, $field ) {
    $valor = $field->escaped_value();
    $post_id = $field->object_id;
    ?>
    <div class="custom-column-display <?php echo $field->row_classes(); ?>">
        <p>R$ <?php echo number_format($valor, 2, ',', ' '); ?></p>
        <p class="description"><?php echo $field->args( 'description' ); ?></p>
    </div>
    <?php
  }

  public function vhr_infos_box_build(){
    ?>
      <div>
        <table class="form-table">
          <tr>
            <th>
              <label for="title">
                Código do pedido
              </label>
            </th>
            <td>
              <?php echo get_the_title(); ?>
              <div>
                <!-- Preview do QR Code -->
              </div>
            </td>
          </tr>
          <tr>
            <th>
              <label for="data-pedido">
                Data do pedido
              </label>
            </th>
            <td>
              <?php echo get_the_date('d/m/Y'); ?>
            </td>
          </tr>
          <tr>
            <th>
              <label for="evento">
                Evento
              </label>
            </th>
            <td>
              <?php echo get_the_title(get_post_meta( get_the_id(), 'evento_id', true )); ?>
            </td>
          </tr>
          <tr>
            <th>
              <label for="cliente">
                Cliente
              </label>
            </th>
            <td>
              <?php echo get_the_author_meta('display_name', get_post_meta(get_the_id(), 'user_id', true)); ?>
            </td>
          </tr>
          <tr>
            <th>
              <label for="notification_code">Código de Transação</label>
            </th>
            <td>
              <?php echo get_post_meta( get_the_id(), 'notification_code', true ); ?>
            </td>
          </tr>
        </table>
      </div>
    <?php
  }

  public function vhr_ingresso_selector_box(){
    add_thickbox();
    ?>
      <div>
        <table class="widefat ingresso-table">
          <thead>
            <th>
              &nbsp;
            </th>
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
          $total = get_post_meta(get_the_id(), 'valor', true) ;
            $ingressos = get_post_meta(get_the_id(),'ingressos', true);
            $evento_id = get_post_meta( get_the_id(), 'evento_id', true );

            if(!empty($ingressos)){

              foreach((array) $ingressos as $k => $ingresso):
                  ?>
                  <tr data-id="<?php echo $k; ?>">
                    <td>
                      <input type="checkbox" data-id="remover" value="<?php echo $k; ?>">
                    </td>
                    <td>
                      <input type="hidden" name="ingressos[<?php echo $k; ?>][tipo]" value="<?php echo $ingresso['tipo']; ?>">
                      <?php echo self::get_valor_label($evento_id, $ingresso['tipo']); ?>
                    </td>
                    <td>
                      <input type="hidden" name="ingressos[<?php echo $k; ?>][qtd]" value="<?php echo $ingresso['qtd']; ?>">
                      <?php echo $ingresso['qtd']; ?>
                    </td>
                    <td>
                      <input type="hidden" name="ingressos[<?php echo $k; ?>][valor]" value="<?php echo $ingresso['valor']; ?>">
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
                <input type="hidden" id="ingressos-total" name="ingressos[total]" value="<?php echo $total; ?>">
                <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?>
              </td>
            </tr>
          </tfoot>
        </table>
        <div class="alignright">
          <a class="button-secondary remover-ingresso" disabled>Remover Selecionados</a>
          <a href="#TB_inline?width=600&height=550&inlineId=ingresso-selector-box" class="button-primary thickbox">Adicionar +</a>
        </div>
        <div id="ingresso-selector-box">
          <h3>Adicionar ingresso a lista</h3>
            <table class="form-table" id="ingresso-option-form">
              <tr>
                <th>
                  Tipo
                </th>
                <td>
                  <select id="tipo-ingresso">
                    <option value="">Selecione um tipo</option>
                    <?php
                      if($evento_id):
                        $tipos = get_post_meta($evento_id, '_vhr_valores', true);

                        foreach((array) $tipos as $tipo_id => $tipo):
                          ?>
                            <option value="<?php echo $tipo_id; ?>"><?php echo $tipo['label'] ; ?></option>
                          <?php
                        endforeach;

                      endif;
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th>
                  Quantidade
                </th>
                <td>
                  <input type="number" id="qtd-ingresso" min="1" value="">
                </td>
              </tr>
              <input type="hidden" id="valor-ingresso" value="">
              <input type="hidden" id="valores-json" value="<?php echo htmlspecialchars(json_encode($tipos)); ?>">
            </table>
          <div class="alignright">
            <a id="cancel-ingresso" class="button-secondary">Cancelar</a>
            <a id="add-ingresso" class="button-primary">Adicionar</a>
          </div>
        </div>
        <div class="clear"></div>
      </div>
      <style media="screen">
        span.select2-container.select2-container--default.select2-container--open {
          z-index: 10000000;
        }
      </style>
    <?php
  }

  function get_valor_label($post_id, $pos){
    $label = "";
    $valores = get_post_meta($post_id, '_vhr_valores', true);

    $label .= $valores[ $pos ]['label'];

    return $label;
  }

  public function add_ingresso_item(){
    extract($_POST);

    $postarr = array(
      'post_type'   => 'ingresso',
      'post_status' => 'publish',
      'meta_input'  => array(
        'evento_id' => $evento_id,
        'user_id'   => $user_id,
        'transaction_id'  => $transaction_id,
        'transaction_state' => $transaction_state,
        'notification_code' => $notification_code,
        'ingressos' => $ingressos,
        'valor' => $valor
      )
    );

    $id = wp_insert_post( $postarr );

    wp_update_post( array(
      'ID'  => $id,
      'post_title'  => '#'.$id,
      'post_name'   => $id
    ));

    wp_die(json_encode( $id ));
  }

  function disable_new_posts() {
    // Hide sidebar link
    global $submenu;
    unset($submenu['edit.php?post_type=ingresso'][10]);
  }

  function disable_new_posts_js(){
    // Hide link on listing page
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'ingresso' ||
    isset($_GET['post']) && get_post_type($_GET['post']) == 'ingresso' ) {
        echo '<style type="text/css">
        #favorite-actions, .add-new-h2, .page-title-action { display:none; }
        </style>';
        echo '<script>jQuery(".page-title-action").remove();</script>';
    }
  }

  public function validation_ingresso(){
    extract($_POST);

    $valid = array(
      'code'    => 0,
      'return'  => ''
    );

    $valores = get_post_meta( $refID, '_vhr_valores', true );

    $valor = $valores[$id]['valor'];
    $label = $valores[$id]['label'];
    $tValor = floatval($valor) * intval($qtd);

    if($valores) {
      $valid['code'] = 1;
      $valid['return'] = array(
        'tipo'  => intval($id),
        'label' => $label,
        'qtd'   => intval($qtd),
        'valor' => floatval($tValor)
      );
    } else {
      $valid['return'] = 'erro algum campo errado';
    }
    header('Content-Type: application/json');
    wp_send_json($valid);
  }

  public function pag_code_gen(){
    $data = array();

    $nonce = $_POST['_wpnonce'];

    if( ! wp_verify_nonce( $nonce, 'finalize' ) ){
      return new WP_Error('valid nonce', "Validação errada");
    }

    $ingressos = $_POST['ingressos'];
    $refID = $_POST['refID'];
    $total = $_POST['valor'];
    $user_id = get_current_user_id();

    $args = array(
      'evento_id' => $refID,
      'user_id'   => $user_id,
      'ingressos' => $ingressos,
      'valor'     => number_format($total, 2)
    );

    $orderID = $this->insert_order($args);

    $code = $this->pagseguro_init(array(
      'ref'       => $this->pag_ref_gen($orderID),
      'orderID'   => $orderID,
      'user_id'   => $user_id,
      'ingressos' => $ingressos
    ));

    if( ! is_array($code) ){
      $data['code'] = $code;
      $data['orderID'] = $orderID;
    } else {
      $data = $code;
    }

    header('Content-Type: application/json');
    wp_send_json($data);
  }

  public function add_notification_code(){
    header('Content-Type: application/json');
    $data = array();
    $pagseguro = new VHR_PagSeguro();
    $pagseguro->add_pagseguro_init();

    if(! isset($_POST['orderID']) ){
      $data = new WP_Error('parametro invalido', 'Paramêtros invalidos.');
      return wp_send_json_error($data);
    }

    $response = \PagSeguro\Services\Transactions\Search\Code::search(
      \PagSeguro\Configuration\Configure::getAccountCredentials(),
      $_POST['transactionCode']
    );

    $post_id = update_post_meta( $_POST['orderID'], 'notification_code', $_POST['transactionCode'] );

    $status = $response->getStatus();

    if($status != 1){
      update_post_meta( $_POST['orderID'], 'transaction_state', $status);

      if($status == 7){
        update_post_meta( $_POST['orderID'], 'status', 'cancelado' );
      }
    }

    if(is_wp_error( $post_id )){
      $errors = $post_id->get_error_messages();
      $data['error'] = implode($errors, ',');
      wp_send_json_error( $data );
    } else {
      $data['success'] = true;
      $data['id'] = $post_id;
      wp_send_json_success($data);
    }
  }

  public function add_abort_status(){
    header('Content-Type: application/json');
    $data = array();

    if(! isset($_POST['orderID']) ){
      $data = new WP_Error('parametro invalido', 'Paramêtros invalidos.');
      return wp_send_json_error($data);
    }

    update_post_meta( $_POST['orderID'], 'status', 'cancelado' );
    update_post_meta( $_POST['orderID'], 'transaction_state', 7 );

    wp_send_json_success($_POST['orderID']);
  }

  /**
   * Gera o CODIGO de referência para a transação do PagSeguro
   * @param  int $id ID do ingresso
   * @return str/object
   */

  protected function pag_ref_gen($id){
    if( empty($id) ) {
      return new WP_Error('format invalid', "Formato de informação invalído.");
    }

    $ref = 'PAGRF';
    $ref .= date('Y');
    $ref .= str_pad($id, 6, 0, STR_PAD_LEFT);

    return $ref;
  }

  /**
   * Insere um novo pedido (ingresso) na base de dados
   * @param  array/string $args Argumentos aceitos
   * @return mixed
   */

  protected function insert_order($args){
    $defaults = array(
      'evento_id' => 0,
      'user_id'   => 0,
      'ingressos' => array(),
      'valor'     => 00.00
    );

    $args = wp_parse_args( $args, $defaults );

    $postarr = array(
      'post_type'   => 'ingresso',
      'post_status' => 'publish',
      'meta_input'  => array(
        'evento_id' => $args['evento_id'],
        'user_id'   => $args['user_id'],
        'transaction_state' => 1,
        'ingressos' => $args['ingressos'],
        'valor'     => $args['valor'],
        'status'    => 'ativo'
      )
    );

    $id = wp_insert_post( $postarr );

    wp_update_post( array(
      'ID'  => $id,
      'post_title'  => '#'.$id,
      'post_name'   => $id,
      'meta_input'  => array(
        'transaction_id'  => $this->pag_ref_gen($id)
      )
    ));

    return $id;
  }

  protected function pagseguro_init($args){
    $pagseguro = new VHR_PagSeguro();
    $pagseguro->add_pagseguro_init();

    $defaults = array(
      'ref'       => '',
      'orderID'   => 0,
      'user_id'   => 0,
      'ingressos' => array()
    );

    $args = wp_parse_args( $args, $defaults );

    $payment = new \PagSeguro\Domains\Requests\Payment();

    $valores = get_post_meta( $orderID, '_vhr_valores', true );
    $home_url = home_url();
    $notificacao = home_url('/notificacao');

    foreach((array) $args['ingressos'] as $ingresso) {
      $tipo = intval($ingresso['tipo']);
      $description = ($valores[$tipo]['label']) ? $valores[$tipo]['label'] : 'Ingresso ' . $tipo;
      $valorSimples = (intval($ingresso['qtd']) == 1) ? number_format(floatval($ingresso['valor']), 2) : number_format(floatval($valores[$tipo]['valor']), 2);

      $payment->addItems()->withParameters(
        $ingresso['tipo'],
        $description,
        intval($ingresso['qtd']),
        $valorSimples
      );
    }

    $payment->setCurrency("BRL");
    $payment->setReference($args['ref']);

    // Set your customer information.
    $payment->setSender()->setName(get_the_author_meta('display_name'));
    $payment->setSender()->setEmail(get_the_author_meta('user_email'));
    $payment->setSender()->setPhone()->withParameters(
        11,
        56273440
    );

    // $payment->addParameter()->withParameter('shippingAddressRequired', 'false');

    $payment->setPaymentMethod(array(\PagSeguro\Enum\PaymentMethod\Group::CREDIT_CARD));
    $payment->setRedirectUrl($home_url);
    $payment->setNotificationUrl($notificacao);

    try {
        $onlyCheckoutCode = true;
        $result = $payment->register(
            \PagSeguro\Configuration\Configure::getAccountCredentials(),
            $onlyCheckoutCode
        );

        return $result->getCode();
    } catch (Exception $e) {
        return array('msg' => $e->getMessage(), 'exc' => true);
    }
  }
}

new VHR_Ingresso_Functions;
