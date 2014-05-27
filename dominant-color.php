<?php
/**
 * Plugin Name: Dominant Color
 * Description: Save the dominant color of the image files you upload.
 * Author: Sunny Ripert & Guillaume Morisseau
 * Author URI: https://github.com/theamnesic/dominant-color
 * Version: 1.0
 */

// Debug helper

function xlog($str) {
  header('X-Log: '.preg_replace('/\n/', '', $str));
}

// RVB to HEX

function rgb2hex($rgb) {
  $hex = "#";
  $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
  $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
  $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

  return $hex;
}

// Calculate the dominant color
// Thanks to @onion2k on http://forums.devnetwork.net/viewtopic.php?t=39594

function dominant_color($path) {
  $i = imagecreatefromjpeg($path);

  $rTotal = 0;
  $gTotal = 0;
  $bTotal = 0;
  $total = 0;

  for ($x=0;$x<imagesx($i);$x++) {
    for ($y=0;$y<imagesy($i);$y++) {
      $rgb = imagecolorat($i,$x,$y);
      $r   = ($rgb >> 16) & 0xFF;
      $g   = ($rgb >> 8) & 0xFF;
      $b   = $rgb & 0xFF;

      $rTotal += $r;
      $gTotal += $g;
      $bTotal += $b;
      $total++;
    }
  }

  $rAverage = round($rTotal/$total);
  $gAverage = round($gTotal/$total);
  $bAverage = round($bTotal/$total);

  return rgb2hex(array($rAverage, $gAverage, $bAverage));
}

// Callback that saves the dominant color in the meta

function dominant_attachment_save($attachment_id) {
  if (wp_attachment_is_image($attachment_id)) {
    $path = get_attached_file($attachment_id);
    $color = dominant_color($path);
    update_post_meta($attachment_id, 'dominant_color', $color);
  }
}

// Use this in your templates

function dominant_attachment_rgb($attachment_id) {
  return get_post_meta($attachment_id, 'dominant_color');
}

add_action('add_attachment', 'dominant_attachment_save');


// Adds a box to the attachment page

function dominant_add_meta_box() {

  add_meta_box(
    'dominant',
    'Dominant color',
    'dominant_meta_box_callback',
    'attachment'
  );
}

add_action( 'add_meta_boxes', 'dominant_add_meta_box' );

function dominant_meta_box_callback($post) {

  wp_nonce_field('dominant_meta_box', 'dominant_meta_box_nonce');

  $value = "#ffffff"; // white

  $meta = get_post_meta( $post->ID, 'dominant_color', true );
  $value = (empty($meta)) ? $value : $meta;

  echo '<input id="field_rgb" name="field_rgb" type="text" value="' . esc_attr( $value ) . '" class="dominant-color-field" />';
}

function dominant_save_meta_box_data( $post_id ) {

  if (!isset( $_POST['dominant_meta_box_nonce']))
    return;

  if (!wp_verify_nonce( $_POST['dominant_meta_box_nonce'], 'dominant_meta_box'))
    return;

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return;

  if (isset( $_POST['post_type']) && 'page' == $_POST['post_type']) {

    if (!current_user_can('edit_page', $post_id))
      return;

  } else {

    if (!current_user_can('edit_post', $post_id))
      return;
  }

  if (!isset($_POST['field_rgb']))
    return;

  $color = sanitize_text_field($_POST['field_rgb']);

  update_post_meta($post_id, 'dominant_color', $color);
}

add_action('edit_attachment', 'dominant_save_meta_box_data');

// Add the colorpicker

add_action('admin_enqueue_scripts', 'mw_enqueue_color_picker');

function mw_enqueue_color_picker($hook_suffix) {
  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('my-script-handle', plugins_url('script.js', __FILE__ ), array('wp-color-picker'), false, true);
}
