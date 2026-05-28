<?php
/**
 * Calypso Sub Arezzo — child theme functions
 *
 * - Accoda lo style.css del parent e poi del child
 * - Registra "block style" extra (ghost / dark) per il blocco Button
 *   così appaiono nell'editor sotto "Stili"
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue parent + child stylesheet
 */
add_action( 'wp_enqueue_scripts', function () {
	$parent = 'calypso-parent-style';

	// Parent style
	wp_enqueue_style(
		$parent,
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( get_template() )->get( 'Version' )
	);

	// Child style (dipende dal parent così carica DOPO)
	wp_enqueue_style(
		'calypso-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent ),
		wp_get_theme()->get( 'Version' )
	);
}, 20 );

/**
 * Registra varianti del bottone (block styles) per Gutenberg
 * Le classi .is-style-ghost e .is-style-dark sono già definite in style.css
 */
add_action( 'init', function () {
	if ( ! function_exists( 'register_block_style' ) ) return;

	register_block_style( 'core/button', array(
		'name'  => 'ghost',
		'label' => __( 'Ghost (bordo bianco)', 'calypso-sub' ),
	) );

	register_block_style( 'core/button', array(
		'name'  => 'dark',
		'label' => __( 'Dark (deep ocean)', 'calypso-sub' ),
	) );

	register_block_style( 'core/heading', array(
		'name'  => 'display',
		'label' => __( 'Display oversize', 'calypso-sub' ),
	) );

	register_block_style( 'core/paragraph', array(
		'name'  => 'eyebrow',
		'label' => __( 'Eyebrow / Kicker', 'calypso-sub' ),
	) );
} );
