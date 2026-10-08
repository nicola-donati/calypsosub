<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Accesso ai dati di configurazione del modulo Comunicazioni (gruppi
 * Telegram predefiniti e categorie, ognuna mappata a un tag systeme.io + un
 * sottoinsieme dei gruppi). La UI per modificarli vive nella tab
 * "Comunicazioni" della pagina Calypso Sub → Impostazioni esistente
 * (Calypsosub_Admin_Menus::render_settings_page()/save_communications_settings()),
 * non in una pagina propria — niente menu/submenu separato da questa classe.
 */
class Calypsosub_Communications_Settings {

	/** @return array<string,array{label:string,chat_id:string}> */
	public static function get_telegram_groups(): array {
		return (array) get_option( 'calypsosub_telegram_groups', [] );
	}

	/** @return array<string,array{label:string,tag_systemeio:string,groups:string[]}> */
	public static function get_categories(): array {
		return (array) get_option( 'calypsosub_communication_categories', [] );
	}
}
