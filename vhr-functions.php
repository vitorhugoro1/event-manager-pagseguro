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

function filter_content($content){
  global $post;

  if('eventos' == get_post_type($post)){
    $valores = get_post_meta( $post->ID, '_vhr_valores', true );
    ob_start();
      echo '<ul>';
        foreach ($valores as $val) {
          $day = new VHR_Loja_Meta_Boxes();
          if($val['multiplo']){
            $dia = $day->get_day_event($post->ID, $val['dia-multiplo'], true);
          } else {
            $dia = $day->get_day_event($post->ID, $val['dia-simples']);
          }
          echo sprintf('<li>%s ( %s ) - R$ %s</li>', $val['label'], $dia, $val['valor']);
        }
      echo '</ul>';
    $content .= ob_get_clean();
  }

  return $content;
}

add_filter( 'the_content', 'filter_content' );
