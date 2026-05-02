<?php
/**
 * Course meta-box view (used inside the standard WP post editor, not needed
 * since the plugin uses custom admin pages — kept as a fallback).
 *
 * @package InScience_Training
 * Variables: $meta (array), $post (WP_Post)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<p><em><?php esc_html_e( 'Please use the InScience Training admin pages to manage courses.', 'inscience-training' ); ?></em></p>
