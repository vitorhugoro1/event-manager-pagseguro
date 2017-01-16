<?php

class VHR_Ingresso_Functions
{
  public function init(){
    add_action( 'admin_init', array(__CLASS__, 'vhr_infos_box') );
  }

  public function vhr_infos_box(){
    global $pagenow;

    if('post-new.php' !== $pagenow){
      add_meta_box( 'ingresso_info_box', 'Informações', array('VHR_Ingresso_Functions', 'vhr_infos_box_build'), 'ingresso', 'normal', 'default' );
      add_meta_box( 'vhr_ingresso_selector_box', 'Itens do Pedido', array('VHR_Ingresso_Functions', 'vhr_ingresso_selector_box'), 'ingresso', 'normal', 'default' );
    }
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
              <input class="evento-auto" type="text" name="evento" value=""> - <a>Editar</a>
            </td>
          </tr>
          <tr>
            <th>
              <label for="cliente">
                Cliente
              </label>
            </th>
            <td>
              <input class="cliente-auto" type="text" name="cliente" value=""> - <a>Editar</a>
            </td>
          </tr>
        </table>
      </div>
    <?php
  }

  public function vhr_ingresso_selector_box(){
    ?>
      <div>
        <table class="widefat" style="margin-bottom:1rem;">
          <thead>
            <th>
              ID
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
            <tr>
              <td>
                <p>
                  Nenhum ingresso
                </p>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3">
                <label class="alignright">Valor Total</label>
              </td>
              <td>
                R$ 00.00
              </td>
            </tr>
          </tfoot>
        </table>
        <div class="alignright">
          <a href="javascript:void(0);" class="button-primary">Adicionar +</a>
        </div>
        <div class="clear"></div>
      </div>
    <?php
  }
}

VHR_Ingresso_Functions::init();
