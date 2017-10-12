<?php
/**
 * Enqueue the parent theme stylesheet.
 */

//function bridge_child_enqueue_parent_style() {
//	wp_enqueue_style( 'bridge-parent-style', get_template_directory_uri() . '/style.css' );
//}
//add_action( 'wp_enqueue_scripts', 'bridge_child_enqueue_parent_style', 8 );

//add_action( 'wp_enqueue_scripts', function() {
//	wp_enqueue_style( 'bridge-parent-style', get_template_directory_uri() . '/style.css' );
//});

/**
 * Para cambiar la frecuencia heartbeat de WP
 *
 * @param array $settings     Array de opciones.
 */
function cl_heartbeat_settings( $settings ) {
	//$settings['autostart'] = false; // Apagamos el autostart
	$settings['interval'] = 60; // En segundos, entre 15 y 60
	return $settings;
}
add_filter( 'heartbeat_settings', 'cl_heartbeat_settings' );

/**
 * Diferentes opciones de limpieza de cabecera de WP
 */
function cl_clean_header() {
	remove_action( 'wp_head', 'wp_generator' ); // Eliminar el meta tag generator de WP.
	remove_action( 'wp_head', 'wlwmanifest_link' ); // Eliminar el link wlwmanifest.xml solo necesario para Windows Live Writer.
	remove_action( 'wp_head', 'rsd_link' ); // Eliminar RSD, un API para editar el blog desde servicios y clientes externos.
	remove_action( 'wp_head', 'wp_shortlink_wp_head' ); // ELiminar el shotlink http://example.com/?p=ID no necesario.
	remove_action( 'wp_head', 'index_rel_link' ); // Eliminar enlace a página inicial.
	remove_action( 'wp_head', 'feed_links_extra', 3 ); // Elimina todos los enlaces extra a feeds rss.
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 ); // Eliminar links previo/siguiente de la cabecera (no de la entrada).
	remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // ELiminar links previo/siguiente.
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // Eliminar post link aleatorio.
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // Eliminar parent post link.

	add_filter( 'the_generator', '__return_false' ); // Elimina el nombre del generador de los feeds RSS

	// Eliminar css y js de emoji.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'after_setup_theme', 'cl_clean_header' );

// Eliminar cookies automáticas de comentarios.
remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' );
