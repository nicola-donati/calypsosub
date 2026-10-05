<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Header minimale per i template classici del plugin (archive/single dei CPT).
 *
 * Il parent "twentytwentyfour" è un block theme e non ha un header.php: senza
 * questo file, get_header() ricade sul fallback di WP (wp-includes/theme-compat/header.php),
 * che inietta il markup legacy <div id="page"><div id="header">... con
 * #page in position:relative — questo rompe il posizionamento assoluto
 * dell'header Gutenberg (.cso-site-header-wrap), che finisce ancorato a
 * #page invece che al documento, a differenza delle pagine a blocchi (FSE)
 * dove l'header vive dentro .wp-site-blocks senza antenati position:relative.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( function_exists( 'the_block_template_skip_link' ) ) the_block_template_skip_link(); ?>
