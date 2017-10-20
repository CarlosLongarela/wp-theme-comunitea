<?php
/**
 * Añadir los estilos del Tema padre y del tema hijo.
 * @ https://digwp.com/2016/01/include-styles-child-theme/
 *
 * @return void
 */
function cl_comunitea_enqueue_themes_styles() {
	// Enqueue parent styles.
	wp_enqueue_style( 'bridge-parent-style', get_template_directory_uri() . '/style.css' );

	// Register and enqueue child styles.
	wp_register_style( 'theme-comunitea', get_stylesheet_directory_uri() . '/style.css', array( 'bridge-parent-style' ) );
	wp_enqueue_style( 'theme-comunitea' );
}
add_action( 'wp_enqueue_scripts', 'cl_comunitea_enqueue_themes_styles' );

/**
 * Eliminar dashicons de la barra de admin para usuarios no conectados.
 *
 * @return void
 *
 * @since     1.2.0
 */
add_action( 'wp_print_styles', function() {
	if ( ! is_admin_bar_showing() ) {
		wp_deregister_style( 'dashicons' );
	}
}, 100);

/**
 * Desactivar Heartbeat API.
 *
 * @return void
 *
 * @since     1.2.0
 */
function cl_stop_heartbeat() {
	wp_deregister_script( 'heartbeat' );
}
add_action( 'init', 'cl_stop_heartbeat', 1 );

/**
 * Para cambiar la frecuencia heartbeat de WP
 *
 * @param array $settings     Array de opciones.
 */
//function cl_heartbeat_settings( $settings ) {
//	//$settings['autostart'] = false; // Apagamos el autostart
//	$settings['interval'] = 60; // En segundos, entre 15 y 60
//	return $settings;
//}
//add_filter( 'heartbeat_settings', 'cl_heartbeat_settings' );

/**
 * Para eliminar el parámetro ver= de js y css.
 *
 * @param string $src Source del archivo CSS ó JS.
 *
 * @return string
 *
 * @since 1.2.0
 */
function cl_remove_cssjs_ver( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
add_filter( 'style_loader_src', 'cl_remove_cssjs_ver', 15, 1 );
add_filter( 'script_loader_src', 'cl_remove_cssjs_ver', 15, 1 );

/**
 * Añadir parámetro defer a los javascript siempre que no sea jquery.
 *
 * @param string $url Url origen del archivo JS.
 *
 * @return string
 *
 * @since 1.2.0
 */
function cl_defer_parsing_of_js( $url ) {
	if ( false === strpos( $url, '.js' ) ) {
		return $url;
	}

	if ( strpos( $url, 'jquery.js' ) ) {
		return $url;
	}

	return "$url' defer onload='";
}
if ( ! is_admin() ) {
	add_filter( 'clean_url', 'cl_defer_parsing_of_js', 11, 1 );
}

/**
 * Eliminar las query strings de gravatar.
 *
 * @param string $url Url al avatar.
 *
 * @return string
 *
 * @since 1.2.0
 */
function cl_avatar_remove_querystring( $url ) {
	$url_parts = explode( '?', $url );
	return $url_parts[0];
}
add_filter( 'get_avatar_url', 'cl_avatar_remove_querystring' );

/**
 * Elimina el script migrate de la lista de dependencias de jQuery.
 *
 * @since 1.2.0
 *
 * @param WP_Scripts $scripts Objeto WP_Scripts pasado por referencia.
 */
function cl_dequeue_jquery_migrate( $scripts ) {
	if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
		$jquery_dependencies = $scripts->registered['jquery']->deps;
		$scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
	}
}
add_action( 'wp_default_scripts', 'cl_dequeue_jquery_migrate' );

/**
 * Varias opciones de limpieza de tags en la cabecera.
 *
 * @return void
 *
 * @since 1.2.0
 */
function cl_clean_header() {
	remove_action( 'wp_head', 'wp_generator' ); // Eliminar el meta tag generator de WP.
	remove_action( 'wp_head', 'wlwmanifest_link' ); // Eliminar el link wlwmanifest.xml solo necesario para Windows Live Writer.
	remove_action( 'wp_head', 'rsd_link' ); // Eliminar RSD, un API para editar el blog desde servicios y clientes externos.
	remove_action( 'wp_head', 'wp_shortlink_wp_head' ); // ELiminar el shotlink http://example.com/?p=ID no necesario.
	remove_action( 'wp_head', 'index_rel_link' ); // Eliminar enlace a página inicial.
	remove_action( 'wp_head', 'wp_resource_hints', 2 ); // Elimina DNS-PREFETCH de s.w.org !
	remove_action( 'wp_head', 'feed_links_extra', 3 ); // Elimina todos los enlaces extra a feeds rss.
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 ); // Eliminar links previo/siguiente de la cabecera (no de la entrada).
	remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // ELiminar links previo/siguiente.
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // Eliminar post link aleatorio.
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // Eliminar parent post link.

	// Eliminar estilos y scripts de Emoji's.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' ); // Eliminar cookies automáticas de comentarios.
}
add_action( 'after_setup_theme', 'cl_clean_header' );

// Elimina el nombre del generador de los feeds RSS
add_filter( 'the_generator', '__return_false' );

// Desactivar REST API.
add_filter( 'json_enabled', '__return_false' );
add_filter( 'json_jsonp_enabled', '__return_false' );

/**
 * Cambiar el nombre del archivo a una versión segura y sanitizada.
 *
 * @param string $filename     Nombre del archivo una vez pasados los primeros filtros de WP.
 * @param string $filename_raw Nombre del archivo "en crudo" al subirse.
 *
 * @return string
 *
 * @since 1.2.0
 */
function cl_nombre_archivo( $filename, $filename_raw ) {
	$info           = pathinfo( $filename_raw );
	$nombre_archivo = $info['filename'];

	if ( ! empty( $info['extension'] ) ) {
		$ext = $info['extension'];
	} else {
		$ext = '';
	}

	$nombre_archivo = remove_accents( $nombre_archivo );
	$nombre_archivo = str_replace( '_', '-', $nombre_archivo );
	$nombre_archivo = str_replace( '%20', '-', $nombre_archivo );
	$nombre_archivo = sanitize_title( $nombre_archivo );
	$nombre_archivo = $nombre_archivo . '.' . $ext;

	return $nombre_archivo;
}
add_filter( 'sanitize_file_name', 'cl_nombre_archivo', 10, 2 );

/**
 * Añade al ALT de las imágenes el título de la foto.
 *
 * @param int    $meta_id    Id del metadato.
 * @param int    $post_id    Id del post.
 * @param string $meta_key   Clave del metadato.
 * @param mixed  $meta_value Valor del metadato.
 *
 * @return void
 *
 * @since 1.2.0
 */
function cl_alt_after_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
	// _wp_attachment_metadata añadido.
	if ( '_wp_attachment_metadata' === $meta_key ) {
		$titulo = get_the_title( $post_id ); // Obtenemos el título del archivo.

		// Actualizamos el Texto del ALT.
		update_post_meta( $post_id, '_wp_attachment_image_alt', $titulo );
	}
}
add_action( 'added_post_meta', 'cl_alt_after_post_meta', 10, 4 );
