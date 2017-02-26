<?php
/**
 * Functions for help with load new characters
 * in the admin pages.
 */

/**
 * Removes metabox from appearing on post new screens before the post
 * ID has been set.
 *
 *
 * @param bool  $display
 * @param array $meta_box The array of metabox options
 *
 * @return bool $display True on success, false on failure
 */
function vhr_exclude_from_new($display, $meta_box)
{
    if (!isset($meta_box['show_on']['alt_key'], $meta_box['show_on']['alt_value'])) {
        return $display;
    }

    global $pagenow;

  // Force to be an array
  $to_exclude = !is_array($meta_box['show_on']['alt_value'])
  ? array($meta_box['show_on']['alt_value'])
  : $meta_box['show_on']['alt_value'];

    $is_new_post = 'post-new.php' == $pagenow && in_array('post-new.php', $to_exclude);

    return !$is_new_post;
}

add_filter('cmb2_show_on', 'vhr_exclude_from_new', 10, 2);

function vhr_title_code($data, $postarr)
{
    if ($data['post_type'] == 'ingresso') {
        $title = '#'.$postarr['ID'];
        $data['post_title'] = $title;
    }

    return $data;
}

add_filter('wp_insert_post_data', 'vhr_title_code', '99', 2);

function register_new_page($new_page_title, $new_page_content, $new_page_template)
{
    $new_page_id = null;

    $page_check = get_page_by_title($new_page_title);
    $new_page = array(
            'post_type' => 'page',
            'post_title' => $new_page_title,
            'post_content' => $new_page_content,
            'post_status' => 'publish',
            'post_author' => 1,
    );
    if (!isset($page_check->ID)) {
        $new_page_id = wp_insert_post($new_page);
        if (!empty($new_page_template)) {
            update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
        }
    }

    return $new_page_id;
}

function filter_content($content)
{
    global $post;

    if ('eventos' == get_post_type($post) && !is_single($post) ||
  'eventos' == get_post_type($post) && is_single()) {
        $valores = get_post_meta($post->ID, '_vhr_valores', true);
        $datas = get_post_meta($post->ID, '_vhr_periodo', true);
        $inicio = DateTime::createFromFormat('d/m/Y', $datas['start']);
        $fim = DateTime::createFromFormat('d/m/Y', $datas['end']);
        $hoje = new DateTime();
        $comprarID = get_page_by_title('Selecionar Ingresso');
        $link = get_the_permalink($comprarID->ID);

        ob_start();
        echo '<ul>';
        foreach ($valores as $val) {
            $day = new VHR_Loja_Meta_Boxes();
            if ($val['multiplo']) {
                $dia = $day->get_day_event($post->ID, $val['dia-multiplo'], true);
            } else {
                $dia = $day->get_day_event($post->ID, $val['dia-simples']);
            }
            echo sprintf('<li>%s ( %s ) - R$ %s</li>', $val['label'], $dia, $val['valor']);
        }
        echo '</ul>';

        if ($hoje > $inicio && $hoje < $fim && is_user_logged_in()) {
            ?>
              <input type="button" onclick='window.location.href="<?=$link?>?refID=<?=$post->ID?>"' value="Comprar"/>
            <?php
        } else if($hoje > $inicio && $hoje < $fim) {
          ?>
            <input type="button" onclick='window.location.href="<?=home_url('/login')?>"' value="Logar"/>
          <?php
        }

        $content .= ob_get_clean();
    }

    return $content;
}

function add_table_ingresso($content)
{
    global $post;

    if (is_page('selecionar-ingresso')) {
        ob_start();
        $refID = intval($_GET['refID']);
        $valores = get_post_meta($refID, '_vhr_valores', true);
        ?>
      <div class="om-columns">
        <div class="om-column om-full">
            Evento : <?php echo get_the_title($refID);
        ?>
        </div>
        <div class="om-column om-two-third">
          <div class="select-ingresso">
            <label for="tipo-ingresso">Selecione um ingresso</label>
            <select id="tipo-ingresso">
              <option value="">Selecione um ingresso</option>
              <?php foreach ((array) $valores as $k => $valor) : ?>
                <option value="<?php echo $k;
        ?>"><?php echo $valor['label'];
        ?></option>
              <?php endforeach;
        ?>
            </select>
          </div>
          <div class="qtd-ingresso">
            <label for="qtd-ingresso">Quantidade de ingressos</label>
            <input type="number" id="qtd-ingresso" min="1" value="1">
          </div>
        </div>
        <div class="om-column om-one-third">
          <input type="hidden" id="refID" value="<?php echo $refID;
        ?>">
          <input type="hidden" id="action-url" value="<?php echo admin_url('admin-post.php');
        ?>">
          <input type="button" id="adc-ingresso" value="Adicionar"/>
        </div>
      </div>
      <div class="vc_om-table selecionar-table">
        <form action="<?php echo home_url('/confirmacao-pagamento');
        ?>" method="post">
          <input type="hidden" name="refID" value="<?php echo intval($refID);
        ?>">
          <table id="table-form">
            <thead>
              <tr>
                <th>
                  <input type="checkbox">
                </th>
                <th>
                  Tipo ingresso
                </th>
                <th>
                  Quantidade
                </th>
                <th>
                  Valor
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="3">
                  <span class="alignright" style="margin:0;">Total</span>
                </th>
                <th>
                  <input type="hidden" name="valor" id="total" value="00.00">
                  <span id="total-span">00,00</span>
                </th>
              </tr>
            </tfoot>
          </table>
          <p>
            <input type="button" id="rmv-ingresso" value="Remover" />
            <input type="submit" value="Continuar"/>
          </p>
        </form>
      </div>
    <?php
    $content .= ob_get_clean();
    }

    return $content;
}

function add_finalizar($content)
{
    global $post;

    if (is_page('confirmacao-pagamento')) {
        $pagseguro = new VHR_PagSeguro();
        $pagseguro->add_pagseguro_init();
        ob_start();
        extract($_POST);
        $valores = get_post_meta($refID, '_vhr_valores', true);
        ?>
    <script src="http://malsup.github.com/jquery.form.js"></script>
    <div class="vc_om-table selecionar-table">
      <form method="post" action="<?php echo admin_url('admin-post.php');
        ?>" id="finalize">
        <input type="hidden" name="action" value="pag_code_gen">
        <?php wp_nonce_field('finalize');
        ?>
        <input type="hidden" name="refID" value="<?php echo intval($refID);
        ?>">
        <table id="table-form">
          <thead>
            <tr>
              <th>
                Tipo ingresso
              </th>
              <th>
                Quantidade
              </th>
              <th>
                Valor
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ingressos as $k => $ingresso) : ?>
                <tr>
                  <td>
                    <input type="hidden" data-elem="tipo" name="ingressos[<?php echo $k ?>][tipo]" value="<?php echo $ingresso['tipo'];
        ?>">
                    <?php echo $valores[$ingresso['tipo']]['label'];
        ?>
                  </td>
                  <td>
                    <input type="hidden" data-elem="qtd" name="ingressos[<?php echo $k ?>][qtd]" value="<?php echo $ingresso['qtd'];
        ?>">
                    <?php echo $ingresso['qtd'] ?>
                  </td>
                  <td>
                    <input type="hidden" data-elem="valor" name="ingressos[<?php echo $k ?>][valor]" value="<?php echo floatval($ingresso['valor']);
        ?>">
                    <?php echo number_format(floatval($ingresso['valor']), 2, ',', '.');
        ?>
                  </td>
                </tr>
            <?php endforeach;
        ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2">
                <span class="alignright" style="margin:0;">Total</span>
              </th>
              <th>
                <input type="hidden" name="valor" id="total" value="<?php echo floatval($valor);
        ?>">
                <span id="total-span"><?php echo number_format(floatval($valor), 2, ',', '.');
        ?></span>
              </th>
            </tr>
          </tfoot>
        </table>
        <p>
          <input type="button" onclick="javascript:window.history.back();" value="Cancelar"/>
          <input type="submit" value="Finalizar"/>
        </p>
      </form>
      <input type="hidden" id="redirect_url" value="<?php echo home_url('/minha-conta');
        ?>">
    </div>
    <?php
    $content .= ob_get_clean();
    }

    return $content;
}

function minha_conta_content($content)
{
    if (is_page('minha-conta')) {
      $user_id = get_current_user_id();
      $estados = array(
        1 => 'Aguardando pagamento',
        2 => 'Em análise',
        3 => 'Paga',
        4 => 'Disponível',
        5 => 'Em disputa',
        6 => 'Devolvida',
        7 => 'Cancelada'
      );
        ob_start();
        ?>
        <div class="tabs">

             <div class="tab">
                 <input type="radio" id="tab-1" class="tab-group" name="tab-group-1" checked>
                 <label for="tab-1" class="tab-title">Informações</label>

                 <div class="tab-content">
                     <div class="infos-cadastro">
                       <h4 class="tab-internal-title">Informações básicas</h4>
                       <label>Nome</label>
                       <span data-id="name"><?php echo get_the_author_meta('display_name', $user_id); ?></span><br>
                       <label>Email</label>
                       <span data-id="email"><?php echo get_the_author_meta('email', $user_id); ?></span><br>
                       <label>Telefone</label>
                       <span data-id="tel"><?php echo get_the_author_meta('ddd', $user_id) . ' ' . get_the_author_meta( 'tel', $user_id ); ?></span>
                     </div>
                     <div class="infos-compras">
                       <h4 class="tab-internal-title">Ultímos ingressos comprados</h4>
                       <?php
                          $args = array(
                            'post_type' => 'ingresso',
                            'posts_per_page'  => 3,
                            'fields'  => 'ids',
                            'meta_query'  => array(
                              'relation'  => 'AND',
                              array(
                                'key' => 'user_id',
                                'value' => get_current_user_id(),
                                'compare' => '='
                              )
                            )
                          );

                          $ingressos = get_posts($args);
                          ?>
                          <div class="vc_om-table">
                            <table>
                              <thead>
                                <tr>
                                  <th>
                                    Nº
                                  </th>
                                  <th>
                                    Estado da transação
                                  </th>
                                  <th>
                                    Valor
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                              <?php  foreach($ingressos as $ingresso):  ?>
                                <tr>
                                  <td>
                                    <?php echo get_the_title( $ingresso ); ?>
                                  </td>
                                  <td>
                                    <?php echo $estados[get_post_meta( $ingresso, 'transaction_state', true )]; ?>
                                  </td>
                                  <td>
                                    <?php echo 'R$ ' . number_format(floatval(get_post_meta( $ingresso, 'valor', true )), 2, ',', '.') ?>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                     </div>
                 </div>
             </div>

             <div class="tab">
                 <input type="radio" id="tab-2" class="tab-group" name="tab-group-1">
                 <label for="tab-2" class="tab-title">Editar Perfil</label>

                 <div class="tab-content">
                   <h4 class="tab-internal-title">Editar informações</h4>
                   <?php
                    $name = get_the_author_meta('display_name', $user_id );
                    $email = get_the_author_meta('email', $user_id);
                    $ddd = get_the_author_meta('ddd', $user_id );
                    $tel = get_the_author_meta('tel', $user_id );
                    $tipo = get_the_author_meta('tipo', $user_id );
                    $doc =  get_the_author_meta('doc', $user_id );
                    ?>
                   <form action="<?php echo admin_url('admin-post.php') ?>" id="update_perfil" method="post">
                     <input type="hidden" name="action" value="update_perfil">
                     <?php wp_nonce_field('update_perfil'); ?>
                     <div class="om-columns om-columns-s-pad">
                       <div class="om-column om-full">
                         <p>
                           <label for="name">Nome*</label>
                           <input type="text" id="name" name="name" placeholder="Nome completo" value="<?php echo $name; ?>">
                         </p>
                         <p>
                           <label for="email">Email*</label>
                           <input type="email" id="email" name="email" placeholder="email@email.com" value="<?php echo $email; ?>">
                         </p>
                         <p>
                           <label for="telefone">Telefone*</label>
                           <input type="text" id="ddd" name="ddd" placeholder="DDD" value="<?=$ddd?>">
                           <input type="text" id="telefone" name="tel" placeholder="Telefone" value="<?=$tel?>">
                         </p>
                         <p>
                           <label>Tipo de Perfil*</label><br>
                           <label for="expositor"><input type="radio" id="expositor" name="tipo" <?php checked( $tipo, 'expositor') ?> value="expositor"> Expositor</label>
                           <label for="visitante"><input type="radio" id="visitante" name="tipo" <?php checked( $tipo, 'visitante') ?> value="visitante"> Visitante</label>
                         </p>
                         <p>
                           <label for="doc">CPF/CNPJ</label>
                           <input type="text" id="doc" name="doc" placeholder="Número do documento" value="<?=$doc?>">
                         </p>
                         <p>
                           <input type="submit" value="Atualizar">
                         </p>
                       </div>
                     </div>
                   </form>
                 </div>
             </div>

              <div class="tab">
                 <input type="radio" id="tab-3" class="tab-group" name="tab-group-1">
                 <label for="tab-3" class="tab-title">Ingressos</label>

                 <div class="tab-content">
                   <?php
                      $args = array(
                        'post_type' => 'ingresso',
                        'posts_per_page'  => -1,
                        'fields'  => 'ids',
                        'meta_query'  => array(
                          'relation'  => 'AND',
                          array(
                            'key' => 'user_id',
                            'value' => get_current_user_id(),
                            'compare' => '='
                          )
                        )
                      );

                      $ingressos = get_posts($args);
                      ?>
                      <div class="vc_om-table">
                        <table class="paginated">
                          <thead>
                            <tr>
                              <th>
                                Nº
                              </th>
                              <th>
                                Evento
                              </th>
                              <th>
                                Estado da transação
                              </th>
                              <th>
                                Valor
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                          <?php  foreach($ingressos as $ingresso):
                              $titulo = get_the_title( $ingresso );
                              $evento = get_the_title( get_post_meta( $ingresso, 'evento_id', true ) );
                              $state = get_post_meta( $ingresso, 'transaction_state', true );
                              $valor = floatval(get_post_meta( $ingresso, 'valor', true ));
                            ?>
                            <tr>
                              <td>
                                <?php echo $titulo; ?>
                              </td>
                              <td>
                                <?php echo $evento; ?>
                              </td>
                              <td>
                                <?php echo $estados[$state]; ?>
                                <span>
                                  <?php if($state != 7): ?>

                                  <a href="#" class="enviar-email" data-id="<?php echo $ingresso; ?>">Enviar e-mail</a> |
                                    <?php if($state == 3): ?>
                                      <a href="#" class="imprimir" data-id="<?php echo $ingresso; ?>">Imprimir</a> |
                                    <?php endif; ?>
                                  <a href="#" class="cancelar" data-id="<?php echo $ingresso; ?>">Cancelar</a>
                                <?php endif; ?>
                                </span>
                              </td>
                              <td>
                                <?php echo 'R$ ' . number_format($valor, 2, ',', '.') ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                 </div>
             </div>

          </div>
    <?php
    $content .= ob_get_clean();
    }

    return $content;
}

function login_template($content){
  if(is_page('login')){
      ob_start();
      $args = array(
        'redirect'  => home_url('/eventos'),
        'value_remember'  => false,
        'label_username'  => 'Endereço de e-mail'
      );

      wp_login_form( $args );

      echo sprintf('<div><a href="%s">%s</a></div>', home_url('/cadastrar') ,'Cadastrar');

      $content .= ob_get_clean();
  }

  return $content;
}

function cadastrar_template($content){
  if(is_page('cadastrar')){
    ob_start();
      ?>
        <form action="<?php echo admin_url('admin-post.php'); ?>" id="cadastrar_user" method="post">
          <input type="hidden" name="action" value="cadastrar_user">
          <?php wp_nonce_field( 'cadastrar_user' ); ?>
          <p>
            <label for="name">Nome*</label>
            <input type="text" name="name" id="name" placeholder="Nome completo" required>
          </p>
          <p>
            <label for="email">E-mail*</label>
            <input type="email" id="email" name="email" placeholder="email@email.com" autocomplete="off" required>
          </p>
          <p>
            <label for="pass">Senha*</label>
            <input type="password" id="pass" name="pass" placeholder="Digite uma senha" autocomplete="off" required>
          </p>
          <p>
            <label>Tipo de Conta*</label><br>
            <label for="expositor"><input type="radio" name="tipo" id="expositor" value="expositor"> Expositor</label>
            <label for="visitante"><input type="radio" name="tipo" id="visitante" value="visitante"> Visitante</label>
          </p>
          <p>
            <label for="doc">CPF/CNPJ*</label>
            <input type="text" name="doc" id="doc" placeholder="DOC" required>
          </p>
          <p>
            <label for="tel-field">Telefone*</label>
            <div id="tel-field">
              <input type="text" id="ddd" name="ddd" placeholder="DDD" required>
              <input type="text" id="tel" name="tel" placeholder="0000-0000" required>
            </div>
          </p>
          <p>
            <input type="submit" value="Cadastrar">
          </p>
        </form>
      <?php
    $content .= ob_get_clean();
  }

  return $content;
}

add_filter('the_content', 'filter_content');
add_filter('the_content', 'add_table_ingresso');
add_filter('the_content', 'add_finalizar');
add_filter('the_content', 'minha_conta_content');
add_filter('the_content', 'login_template');
add_filter('the_content', 'cadastrar_template');
