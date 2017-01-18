<?php

class VHR_Ingresso_Functions
{
  public function init(){
    add_action( 'admin_init', array(__CLASS__, 'vhr_infos_box') );
    add_action( 'admin_enqueue_scripts', array(__CLASS__, 'vhr_script_style_list') );
  }

  public function vhr_infos_box(){
    global $pagenow;

    if('post-new.php' !== $pagenow){
      add_meta_box( 'ingresso_info_box', 'Informações', array('VHR_Ingresso_Functions', 'vhr_infos_box_build'), 'ingresso', 'normal', 'default' );
      add_meta_box( 'vhr_ingresso_selector_box', 'Itens do Pedido', array('VHR_Ingresso_Functions', 'vhr_ingresso_selector_box'), 'ingresso', 'normal', 'default' );
      }
  }

  public function vhr_script_style_list(){
    global $pagenow, $post_type;

    if('post-new.php' !== $pagenow ){
      wp_register_style( 'select2css', 'http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
      wp_register_script( 'select2', 'http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
      wp_enqueue_style( 'select2css' );
      wp_enqueue_script( 'select2' );
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
    add_thickbox();
    ?>
      <div>
        <table class="widefat ingresso-table">
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
          <?php
          $ingressos = array(
            array(
              "tipo"  => 1,
              "qtd" => 2,
              "valor" => 50
            ),
            array(
              "tipo"  => 2,
              "qtd" => 1,
              "valor" => 100
            ),
          );
            // $ingressos = get_post_meta($post_id,'_vhr_ingressos_list', true);

            if(!empty($ingressos)){
              
              foreach((array) $ingressos as $k => $ingresso):
                echo '<tr>';
                echo '<td>' . $k . '</td>';
                echo '<td>' . $ingresso['tipo'] . '</td>';
                echo '<td>' . $ingresso['qtd'] . '</td>';
                echo '<td>' . $ingresso['valor'] . '</td>';
                echo '</tr>';
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
                <?php echo '00.00'; ?>
              </td>
            </tr>
          </tfoot>
        </table>
        <div class="alignright">
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
                  <select class="widefat" id="tipo-ingresso">
                    <option value="">Selecione um tipo</option>
                    <?php 
                      $evento_id = get_post_meta($post_id, 'evento', true);

                      if($evento_id):
                        $tipos = get_post_meta($post_id, '_vhr_valores', true);

                        foreach((array) $tipos as $tipo_id => $tipo):
                          ?>
                            <option value="<?php echo $tipo_id; ?>"><?php echo 'Ingresso ' . $tipo_id; ?></option>
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
            </table>
          <div class="alignright">
            <a id="cancel-ingresso" class="button-secondary">Cancelar</a>
            <a id="add-ingresso" class="button-primary">Adicionar</a>
          </div>
        </div>
        <div class="clear"></div>
      </div>
    <?php
  }
}

VHR_Ingresso_Functions::init();
