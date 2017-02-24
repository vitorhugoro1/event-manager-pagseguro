<?php

/**
 *
 */
if(!class_exists('VHR_Users')){
  class VHR_Users
  {

    function __construct()
    {
      add_action('admin_post_update_perfil', array($this, 'update_perfil'));
    }

    function update_perfil(){
      $data = array();

      $nonce = $_POST['_wpnonce'];

      if( ! wp_verify_nonce( $nonce, 'update_perfil' ) ){
        return new WP_Error('valid nonce', "Validação errada");
      }

      $user_id = get_current_user_id();

      foreach($_POST as $key => $val){
        if($val == ''){
          return wp_send_json_error(array('id' => $key, 'error' => 'Campo vazio.'));
        }
      }

      if(get_the_author_meta( 'email', $user_id ) != $_POST['email']){
        if(email_exists( $_POST['email'] )){
          return wp_send_json_error(array('id' => 'email', 'error' => 'Email já existente.'));
        }
      }

      $user_id = wp_update_user( array(
        'ID'  => $user_id,
        'user_email'  => $_POST['email'],
        'display_name'  => $_POST['name'],
        'show_admin_bar_front'  => false
      ) );

      if(is_wp_error( $user_id )){
        return wp_send_json_error(array('id' => 'error', 'error' => 'Erro ao atualizar informação.'));
      }

      update_user_meta( $user_id, 'ddd', $_POST['ddd'] );
      update_user_meta( $user_id, 'tel', $this->number_tel($_POST['tel']) );
      update_user_meta( $user_id, 'tipo', $_POST['tipo'] );

      wp_send_json_success(array(
        'msg' => 'Perfil atualizado.',
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'tel' => $_POST['ddd'] . ' ' . $_POST['tel']
      ));
    }

    protected function number_tel($number){
      preg_match_all('/\d+/', $number, $matches);
      return $matches[0][0];
    }
  }
}

new VHR_Users;
