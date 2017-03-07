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
      add_action('admin_post_nopriv_validate_user_name', array($this, 'validate_user_name'));
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
        'first_name'  => $_POST['name'],
        'last_name'   => $_POST['lastname'],
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

      if(email_exists( $_POST['email'] )){
        return wp_send_json_error( array(
          'msg' => 'Email já está em uso.'
        ) );
      }

      if(username_exists( $_POST['doc'] )){
        return wp_send_json_error( array(
          'msg' => 'Documento já está em uso.'
        ) );
      }

      $arr = array(
        'first_name'  => $_POST['name'],
        'last_name'   => $_POST['lastname'],
        'user_email'  => $_POST['email'],
        'user_login'  => $_POST['doc'],
        'user_pass'   => $_POST['pass'],
      );

      $user_id = wp_insert_user($arr);

      if(!is_wp_error( $user_id )){
        update_user_meta( $user_id, 'ddd', $_POST['ddd'] );
        update_user_meta( $user_id, 'tel', $this->number_only($_POST['tel']) );
        update_user_meta( $user_id, 'tipo', $_POST['tipo'] );
        update_user_meta( $user_id, 'doc', $_POST['doc'] );
        update_user_option($user_id, 'show_admin_bar_front', false);
      }

      wp_send_json_success(array(
        'msg' => 'Usuario criado com sucesso.',
        'redirect'  => home_url("/login")
      ));
    }

    public function validate_user_name(){
      $nonce = $_POST['_wpnonce'];

      if( ! wp_verify_nonce( $nonce, 'validate_user_name' ) ){
        wp_send_json_error("Validação errada");
      }

      $doc = $_POST['doc'];

      if(! username_exists($doc) ){
        $redirect = home_url('/cadastrar');
        return wp_send_json_success(array(
          'redirect'  => $redirect
        ));
      } else {
        return wp_send_json_error("CPF já está em uso.");
      }
    }

    protected function number_only($number){
      preg_match_all('/\d+/', $number, $matches);
      return implode('',$matches[0]);
    }
  }
}

new VHR_Users;
