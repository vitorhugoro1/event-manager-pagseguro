<?php

class VHR_Post_Categories
{
    public function __construct()
    {
        add_action('init', array($this, 'register_post_category_eventos'));
        add_action('save_post_eventos', array($this, 'save_evento_action'));
        add_action('delete_post_eventos', array($this, 'action_remove_evento'));
        add_action('save_post', array($this, 'save_meta_box_evento'));
        add_action('add_meta_boxes', array($this, 'meta_boxes'));
    }

    public function register_post_category_eventos()
    {
        $labels = array(
        'name' => _x('Eventos', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Evento', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Eventos', 'textdomain'),
        'all_items' => __('All Eventos', 'textdomain'),
        'parent_item' => __('Parent Evento', 'textdomain'),
        'parent_item_colon' => __('Parent Evento:', 'textdomain'),
        'edit_item' => __('Edit Evento', 'textdomain'),
        'update_item' => __('Update Evento', 'textdomain'),
        'add_new_item' => __('Add Evento', 'textdomain'),
        'new_item_name' => __('New Evento', 'textdomain'),
        'menu_name' => __('Evento', 'textdomain'),
    );

        $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'meta_box_cb' => false,
        'query_var' => true,
        'rewrite' => array('slug' => 'evento'),
    );

        register_taxonomy('evento', array('post', 'page'), $args);
    }

    public function save_evento_action()
    {
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
          return $post_id;
      }

      $slug = get_the_title($post_id);
      $slug = sanitize_title($slug);
      $is_tax = term_exists($slug, 'evento');

      if($term == 0 && $term == null){
        wp_insert_term(
          get_the_title($post_id),
          'evento',
          array(
            'slug'  => $slug
          )
        );
      }
    }

    public function meta_boxes(){
      add_meta_box('evento-categories', 'Vincular a evento', array($this, 'meta_box_evento'), array('post', 'page'), 'side' );
    }

    public function meta_box_evento($post){
      $post_id = $post->ID;
      $eventos = get_terms('evento', array('hide_empty' => false));
      $term = wp_get_object_terms($post_id, 'evento');
      ?>
        <div>
          <select class="widefat" name="evento" id="evento">
            <option value="">Selecione um evento</option>
            <?php
              foreach((array) $eventos as $evento):
                $selected = selected($term[0]->slug, $evento->slug, true );
                echo sprintf('<option value="%s" %s >%s</option>', $evento->slug, $selected, $evento->name);
              endforeach;
             ?>
          </select>
        </div>
      <?php
    }

    public function save_meta_box_evento($post_id){
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
          return $post_id;
      }

      if( ! in_array(get_post_type($post_id), array('post', 'page')) ) {
        return $post_id;
      }

      if(empty($_POST['evento'])){
        wp_delete_object_term_relationships($post_id, 'evento');
      }

      $term = wp_set_object_terms(
        $post_id,
        $_POST['evento'],
        'evento',
        false
      );

      if(is_wp_error($term)){
        return new WP_Error( 'error', "Erro ao tentar salvar a categoria de eventos" );
      }
    }

    public function action_remove_evento($post_id){
      $slug = sanitize_title( get_the_title( $post_id ) );
      $term = get_term_by( 'slug', $slug, 'evento' );

      if($term){
        wp_delete_term( $term->term_id, 'evento' );
      }
    }
}

new VHR_Post_Categories();
