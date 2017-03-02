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
      add_action('admin_post_cadastrar_user', array($this, 'cadastrar_user'));
      add_action('admin_post_nopriv_cadastrar_user', array($this, 'cadastrar_user'));
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
      update_user_meta( $user_id, 'tel', $this->number_only($_POST['tel']) );
      update_user_meta( $user_id, 'tipo', $_POST['tipo'] );
      update_user_meta( $user_id, 'doc', $_POST['doc'] );

      wp_send_json_success(array(
        'msg' => 'Perfil atualizado.',
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'tel' => $_POST['ddd'] . ' ' . $_POST['tel'],
        'doc' => $_POST['doc']
      ));
    }

    function cadastrar_user(){
      $nonce = $_POST['_wpnonce'];

      if( ! wp_verify_nonce( $nonce, 'cadastrar_user' ) ){
        return new WP_Error('valid nonce', "Validação errada");
      }

      if(email_exists( $_POST['email'] ) && username_exists( $_POST['doc'] )){
        return wp_send_json_error( array(
          'msg' => 'Email já está em uso.'
        ) );
      }

      $arr = array(
        'display_name'  => $_POST['name'],
        'user_email'  => $_POST['email'],
        'user_login'  => $_POST['doc'],
        'user_pass'   => $_POST['pass'],
        'show_admin_bar_front' => false
      );

      $user_id = wp_insert_user($arr);

      if(!is_wp_error( $user_id )){
        update_user_meta( $user_id, 'ddd', $_POST['ddd'] );
        update_user_meta( $user_id, 'tel', $this->number_only($_POST['tel']) );
        update_user_meta( $user_id, 'tipo', $_POST['tipo'] );
        update_user_meta( $user_id, 'doc', $_POST['doc'] );
      }

      wp_send_json_success(array(
        'msg' => 'Usuario criado com sucesso.',
        'redirect'  => home_url("/login")
      ));
    }

    protected function number_only($number){
      preg_match_all('/\d+/', $number, $matches);
      return implode('',$matches[0]);
    }
  }
}

new VHR_Users;
