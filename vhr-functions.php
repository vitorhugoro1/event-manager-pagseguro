<?php
/**
 * Functions for help with load new characters
 * in the admin pages
 */


/**
* Removes metabox from appearing on post new screens before the post
* ID has been set.
*
*
* @param bool $display
* @param array $meta_box The array of metabox options
* @return bool $display True on success, false on failure
*/

function vhr_exclude_from_new( $display, $meta_box ) {
  if ( ! isset( $meta_box['show_on']['alt_key'], $meta_box['show_on']['alt_value'] ) ) {
    return $display;
  }

  global $pagenow;

  // Force to be an array
  $to_exclude = ! is_array( $meta_box['show_on']['alt_value'] )
  ? array( $meta_box['show_on']['alt_value'] )
  : $meta_box['show_on']['alt_value'];

  $is_new_post = 'post-new.php' == $pagenow && in_array( 'post-new.php', $to_exclude );

  return ! $is_new_post;
}

add_filter( 'cmb2_show_on', 'vhr_exclude_from_new', 10, 2 );
