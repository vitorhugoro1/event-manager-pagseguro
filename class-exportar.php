<?php

/**
 *
 */
class VHR_Exportar
{
  protected $states = array(
    1 => 'Aguardando pagamento',
    2 => 'Em análise',
    3 => 'Paga',
    4 => 'Disponível',
    5 => 'Em disputa',
    6 => 'Devolvida',
    7 => 'Cancelada'
  );

  protected $comprador = array(
    'visitante' => 'Visitante',
    'expositor' => 'Expositor'
  );

  function __construct()
  {
    add_action('admin_menu', array($this, 'menu'));
  }

  function menu(){
    add_submenu_page("edit.php?post_type=ingresso", "Exportar ingressos", "Exportar ingressos", "manage_options", "exportar-ingresso", array( $this, "form"));
  }

  function form(){
    ?>
      <div class="wrap">
        <h1><?=get_admin_page_title()?></h1>
        <div>
          <form action="admin-post.php" method="post">
            <table class="form-table">
              <tr valign="top">
                <th scope="row">
                    <label for="evento">Selecione o evento que deseja exportar*</label>
                </th>
                <td>
                  <?php $eventos = get_posts(array('post_type' => 'eventos', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' )); ?>
                  <select id="evento" name="evento" required>
                    <?php
                    if(!empty($eventos)){
                      foreach($eventos as $post):
                        ?>
                          <option value="<?=$post?>"><?=get_the_title($post)?></option>
                        <?php
                      endforeach;
                    } else {
                      ?>
                      <option value="">Sem eventos cadastrados</option>
                      <?php
                    } ?>
                  </select>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row">
                  <label for="estado">Selecione o estado da transação</label>
                </th>
                <td>
                  <select id="estado" name="estado">
                    <option value="">Selecione um estado</option>
                    <?php foreach($this->states as $k => $v): ?>
                      <option value="<?=$k?>"><?=$v?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row">
                  <label for="tipo">Selecione o tipo de comprador</label>
                </th>
                <td>
                  <select id="tipo" name="tipo">
                    <option value="">Selecione um tipo</option>
                    <?php foreach($this->comprador as $k => $v): ?>
                      <option value="<?=$k?>"><?=$v?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
            </table>
            <?php submit_button("Exportar") ?>
          </form>
        </div>
      </div>
    <?php
  }

  function action(){

  }
}

new VHR_Exportar;
