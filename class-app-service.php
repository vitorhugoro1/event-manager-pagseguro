<?php

/**
 *
 */
class VHR_App_Api
{
  protected $methods = 'GET';
  protected $rest_route = 'loja_app/v1';
  protected $path = '/check/';
  protected $param = '(?P<ref>(.\w+))';

  function __construct()
  {
    add_action('rest_api_init', array($this, 'api_route'));
  }

  public function api_route()
  {
    register_rest_route($this->rest_route, $this->path . $this->param, array(
      'methods'   => $this->methods,
      'callback'  => array($this, 'api_callback'),
      'args'      => $this->api_args()
    ) );
  }

  public function api_callback($data)
  {
    $call = false;
    $args = array(
      'post_type'   => 'ingresso',
      'post_status' => 'publish',
      'meta_key'    => 'transaction_id',
      'meta_value'  => $data['ref'],
      'numberposts' => 1,
      'fields'      => 'ids'
    );

    $post = get_posts($args);

    if( ! empty( $post ) ){
       $used = get_post_meta( $post[0], 'used', true );

       if( ! $used ){
         $user_id = get_post_meta($post[0], 'user_id', true );
         $evento = get_post_meta($post[0], 'evento_id', true);
         $ingressos = get_post_meta($post[0], 'ingressos', true);

         $ingressos = $this->update_ingresso($ingressos, $evento);

         $data = array(
           'nome' => get_the_author_meta('display_name', $user_id ),
           'tipo' => ucfirst(get_the_author_meta('tipo', $user_id)),
           'ingressos'  => $ingressos
         );

        //  update_post_meta( $post[0], 'used', true, false );
         return wp_send_json_success($data);
       } else {
         return wp_send_json_error();
       }
    } else {
      return wp_send_json_error();
    }
  }

  public function api_args()
  {
    $args['ref'] = array(
      'validate_callback' => $this->is_string()
    );

    return $args;
  }

  protected function is_string($value = null, $request = null, $param = null){
    $value = html_entity_decode($value);

    if( ! is_string($value) ){
      return new WP_Error('rest_invalid_param', esc_html('Paramêtro inválido'), array( 'status' => 400 ));
    }
  }

  protected function update_ingresso($ingressos, $evento){
    $new = array();
    $valores = get_post_meta($evento, '_vhr_valores', true);
    $days = get_post_meta($evento, '_vhr_data', true );

    foreach((array) $ingressos as $k => $ingresso){
        $pos = intval($ingresso['tipo']);

        if($valores[$pos]['multiplo']){
          $ds = $valores[$pos]['dia-multiplo'];
          foreach($ds as $d){
            $dy[] = date('d/m/Y', $days[$d]['data']);
          }

          $new[$k]['multi'] = true;
          $new[$k]['dia'] = $dy;
        } else {
          $d = $valores[$pos]['dia-simples'];
          $new[$k]['multi'] = false;
          $new[$k]['dia'] = date('d/m/Y', $days[$d]['data']);
        }
        $new[$k]['qtd'] = intval($ingresso['qtd']);
        $new[$k]['tipo'] = $valores[$pos]['label'];
    }

    return $new;
  }
}

new VHR_App_Api();
