<?php

class VHR_Screens
{
    protected $estados = array(
    1 => 'Aguardando pagamento',
    2 => 'Em análise',
    3 => 'Paga',
    4 => 'Disponível',
    5 => 'Em disputa',
    6 => 'Devolvida',
    7 => 'Cancelada',
  );

    public function __construct()
    {
        add_filter('the_content', array($this, 'archive_loop_filter'));
        add_filter('the_content', array($this, 'selecionar_ingresso_screen'));
        add_filter('the_content', array($this, 'confirmacao_pagamento_screen'));
        add_filter('the_content', array($this, 'minha_conta_screen'));
        add_filter('the_content', array($this, 'login_screen'));
        add_filter('the_content', array($this, 'cadastrar_screen'));
    }

    public function archive_loop_filter($content)
    {
        global $post;

        if ('eventos' == get_post_type($post) && !is_single($post) || 'eventos' == get_post_type($post) && is_single()) {
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

            } elseif ($hoje > $inicio && $hoje < $fim) {
                ?>
            <input type="button" onclick='window.location.href="<?=home_url('/login')?>"' value="Logar"/>
          <?php

            }

            $content .= ob_get_clean();
        }

        return $content;
    }

    public function selecionar_ingresso_screen($content)
    {
        global $post;

        if (is_page('selecionar-ingresso')) {
            ob_start();
            $refID = intval($_GET['refID']);
            $valores = get_post_meta($refID, '_vhr_valores', true); ?>
          <div class="om-columns">
            <div class="om-column om-full">
                Evento : <?=get_the_title($refID)?>
            </div>
            <div class="om-column om-two-third">
              <div class="select-ingresso">
                <label for="tipo-ingresso">Selecione um ingresso</label>
                <select id="tipo-ingresso">
                  <option value="">Selecione um ingresso</option>
                  <?php foreach ((array) $valores as $k => $valor) : ?>
                    <option value="<?=$k?>"><?=$valor['label']?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="qtd-ingresso">
                <label for="qtd-ingresso">Quantidade de ingressos</label>
                <input type="number" id="qtd-ingresso" min="1" value="1">
              </div>
            </div>
            <div class="om-column om-one-third">
              <input type="hidden" id="refID" value="<?=$refID?>">
              <input type="hidden" id="action-url" value="<?=admin_url('admin-post.php')?>">
              <input type="button" id="adc-ingresso" value="Adicionar"/>
            </div>
          </div>
          <div class="vc_om-table selecionar-table">
            <form action="<?=home_url('/confirmacao-pagamento')?>" id="initial-order" method="post">
              <input type="hidden" name="refID" value="<?=intval($refID)?>">
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
                <input type="button" onclick="javascript:history.back();" value="Cancelar">
                <input type="button" id="rmv-ingresso" value="Remover"/>
                <input type="submit" value="Continuar"/>
              </p>
            </form>
          </div>
        <?php
        $content .= ob_get_clean();
        }

        return $content;
    }

    public function confirmacao_pagamento_screen($content)
    {
        global $post;

        if (is_page('confirmacao-pagamento')) {
            $pagseguro = new VHR_PagSeguro();
            $pagseguro->add_pagseguro_init();
            ob_start();
            extract($_POST);
            $valores = get_post_meta($refID, '_vhr_valores', true); ?>
        <div class="vc_om-table selecionar-table">
          <form method="post" action="<?=admin_url('admin-post.php')?>" id="finalize">
            <input type="hidden" name="action" value="pag_code_gen">
            <input type="hidden" name="refID" value="<?=intval($refID)?>">
            <?php wp_nonce_field('finalize') ?>
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
                        <input type="hidden" data-elem="tipo" name="ingressos[<?=$k?>][tipo]" value="<?=$ingresso['tipo']?>">
                        <?=$valores[$ingresso['tipo']]['label']?>
                      </td>
                      <td>
                        <input type="hidden" data-elem="qtd" name="ingressos[<?=$k?>][qtd]" value="<?=$ingresso['qtd']?>">
                        <?=$ingresso['qtd']?>
                      </td>
                      <td>
                        <input type="hidden" data-elem="valor" name="ingressos[<?=$k?>][valor]" value="<?=floatval($ingresso['valor'])?>">
                        <?=number_format(floatval($ingresso['valor']), 2, ',', '.')?>
                      </td>
                    </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="2">
                    <span class="alignright" style="margin:0;">Total</span>
                  </th>
                  <th>
                    <input type="hidden" name="valor" id="total" value="<?=floatval($valor)?>">
                    <span id="total-span"><?=number_format(floatval($valor), 2, ',', '.')?></span>
                  </th>
                </tr>
              </tfoot>
            </table>
            <p>
              <input type="button" onclick="javascript:window.history.back();" value="Cancelar"/>
              <input type="submit" value="Finalizar"/>
            </p>
          </form>
          <input type="hidden" id="redirect_url" value="<?=home_url('/minha-conta')?>">
        </div>
        <?php
        $content .= ob_get_clean();
        }

        return $content;
    }

    public function minha_conta_screen($content)
    {
        if (is_page('minha-conta')) {
            $user_id = get_current_user_id();
            $admin_post = admin_url('admin-post.php');

            ob_start(); ?>
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
                           <span data-id="tel"><?php echo get_the_author_meta('ddd', $user_id).' '.get_the_author_meta('tel', $user_id); ?></span><br>
                           <label>Tipo de Conta</label>
                           <span data-id="tipo"><?php echo ucfirst(get_the_author_meta('tipo', $user_id)) ?></span><br>
                           <label>DOC</label>
                           <span data-id="doc"><?php echo get_the_author_meta('doc', $user_id) ?></span>
                         </div>
                         <div class="infos-compras">
                           <h4 class="tab-internal-title">Ultímos ingressos comprados</h4>
                           <?php
                              $args = array(
                                'post_type' => 'ingresso',
                                'posts_per_page' => 3,
                                'fields' => 'ids',
                                'meta_query' => array(
                                  'relation' => 'AND',
                                  array(
                                    'key' => 'user_id',
                                    'value' => get_current_user_id(),
                                    'compare' => '=',
                                  ),
                                ),
                              );

            $ingressos = get_posts($args); ?>
                              <div class="vc_om-table">
                                <table>
                                  <thead>
                                    <tr>
                                      <th> Nº </th>
                                      <th> Estado da transação </th>
                                      <th> Valor </th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                    if (count($ingressos) > 0) {
                                        foreach ($ingressos as $ingresso) {
                                            $title = get_the_title($ingresso);
                                            $transaction_state = get_post_meta($ingresso, 'transaction_state', true);
                                            $value = number_format(floatval(get_post_meta($ingresso, 'valor', true)), 2, ',', '.'); ?>
                                    <tr>
                                      <td> <?=$title?> </td>
                                      <td> <?=$this->$estados[$transaction_state]?> </td>
                                      <td> <?=sprintf('R$ %s', $value)?> </td>
                                    </tr>
                                  <?php
                                        }
                                    } else {
                                        ?>
                                    <tr>
                                      <td colspan="3"><strong class="aligncenter">Nenhuma compra realizada</strong></td>
                                    </tr>
                                    <?php
                                    } ?>
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
                        $name = get_the_author_meta('first_name', $user_id);
                        $sobrenome = get_the_author_meta('last_name', $user_id);
                        $email = get_the_author_meta('email', $user_id);
                        $ddd = get_the_author_meta('ddd', $user_id);
                        $tel = get_the_author_meta('tel', $user_id);
                        $tipo = get_the_author_meta('tipo', $user_id);
                        $doc = get_the_author_meta('doc', $user_id);
                        ?>
                       <form action="<?=$admin_post?>" id="update_perfil" method="post">
                         <input type="hidden" name="action" value="update_perfil">
                         <?php wp_nonce_field('update_perfil') ?>
                         <div class="om-columns om-columns-s-pad">
                           <div class="om-column om-full">
                             <p>
                               <label for="name">Nome*</label>
                               <input type="text" id="name" name="name" placeholder="Nome" required value="<?=$name?>">
                             </p>
                             <p>
                               <label for="lastname">Sobrenome*</label>
                               <input type="text" name="lastname" id="lastname" placeholder="Sobrenome" required value="<?=$sobrenome?>">
                             </p>
                             <p>
                               <label for="email">Email*</label>
                               <input type="email" id="email" name="email" placeholder="email@email.com" required value="<?=$email?>">
                             </p>
                             <p>
                               <label for="telefone">Telefone*</label>
                               <input type="text" id="ddd" name="ddd" placeholder="DDD" value="<?=$ddd?>" required>
                               <input type="text" id="telefone" name="tel" placeholder="00000-0000" value="<?=$tel?>" required>
                             </p>
                             <p>
                               <label>Tipo de Conta*</label><br>
                               <label for="expositor"><input type="radio" id="expositor" name="tipo" <?php checked($tipo, 'expositor') ?> value="expositor"> Expositor</label>
                               <label for="visitante"><input type="radio" id="visitante" name="tipo" <?php checked($tipo, 'visitante') ?> value="visitante"> Visitante</label>
                             </p>
                             <p>
                               <label for="doc">CPF/CNPJ*</label>
                               <input type="text" id="doc" name="doc" placeholder="DOC" value="<?=$doc?>" required>
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
                            'posts_per_page' => -1,
                            'fields' => 'ids',
                            'meta_query' => array(
                              'relation' => 'AND',
                              array(
                                'key' => 'user_id',
                                'value' => get_current_user_id(),
                                'compare' => '=',
                              ),
                            ),
                          );

                          $ingressos = get_posts($args);
                          ?>
                          <div class="vc_om-table">
                            <div class="msg"></div>
                            <table class="paginated">
                              <thead>
                                <tr>
                                  <th> Nº </th>
                                  <th> Evento </th>
                                  <th> Estado da transação </th>
                                  <th> Ações </th>
                                  <th> Valor </th>
                                </tr>
                              </thead>
                              <tbody>
                              <?php
                              if(count($ingressos) > 0){
                                foreach ($ingressos as $ingresso){
                                  $order = get_the_title($ingresso);
                                  $title = get_the_title(get_post_meta($ingresso, 'evento_id', true));
                                  $state = get_post_meta($ingresso, 'transaction_state', true);
                                  $valor = floatval(get_post_meta($ingresso, 'valor', true));
                                  $value = number_format($valor, 2, ',', '.');
                                  ?>
                                <tr>
                                  <td> <?=$order?> </td>
                                  <td> <?=$title?> </td>
                                  <td> <?=$this->$estados[$state]?> </td>
                                  <td>
                                      <?php
                                       if ($state != 7){
                                         $email_nonce = wp_create_nonce('resend_email');
                                         $ref = get_post_meta($ingresso, 'transaction_id', true); ?>

                                         <input type="button" data-href="<?=$admin_post?>" data-action="resend_email" data-nonce="<?=$email_nonce?>" data-ref="<?=$ref?>" class="enviar-email" data-id="<?=$ingresso?>" value="Enviar e-mail">

                                        <?php if ($state == 3): ?>
                                          <input type="button" class="imprimir" data-id="<?=$ingresso?>" value="Imprimir">
                                        <?php endif; ?>
                                        <input type="button" class="cancelar" data-id="<?=$ingresso?>" value="Cancelar">
                                      <?php } ?>
                                  </td>
                                  <td> <?=sprintf('R$ %s', $value)?> </td>
                                </tr>
                              <?php
                            }
                          } else {
                            ?>
                          <tr>
                            <td colspan="5"><strong class="aligncenter">Nenhuma compra realizada</strong></td>
                          </tr>
                          <?php
                          }
                               ?>
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

    function login_screen($content){
      if(is_page('login')){
          ob_start();
          $args = array(
            'redirect'  => home_url('/eventos'),
            'value_remember'  => false,
            'label_username'  => 'Nº documento'
          );

          ?>
            <p>
              <label>Selecione um tipo de login</label>
              <p>
                <label for="visitante"><input type="radio" id="visitante" name="tipo" value="visitante" checked> Visitante</label>
                <label for="expositor"><input type="radio" id="expositor" name="tipo" value="expositor"> Expositor</label>
              </p>
            </p>
          <?php
          wp_login_form( $args );

          echo sprintf('<div><a href="%s">%s</a></div>', home_url('/cadastrar') ,'Cadastrar');

          $content .= ob_get_clean();
      }

      return $content;
    }

    function cadastrar_screen($content){
      if(is_page('cadastrar')){
        ob_start();
          ?>
            <form action="<?=admin_url('admin-post.php')?>" id="cadastrar_user" method="post">
              <input type="hidden" name="action" value="cadastrar_user">
              <?php wp_nonce_field( 'cadastrar_user' ) ?>
              <p>
                <label for="name">Nome*</label>
                <input type="text" name="name" id="name" placeholder="Nome" required>
              </p>
              <p>
                <label for="lastname">Sobrenome*</label>
                <input type="text" name="lastname" id="lastname" placeholder="Sobrenome" required>
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
                  <input type="text" id="tel" name="tel" placeholder="00000-0000" required>
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
}

new VHR_Screens();
