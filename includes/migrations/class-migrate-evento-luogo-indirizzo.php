<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Migrazione one-shot: il vecchio campo "Luogo" degli eventi conteneva in
 * realtà indirizzi completi (es. "Viale della Fiera, 20, 40128 Bologna BO").
 * Sposta quei valori in _evento_indirizzo e svuota _evento_luogo, che da
 * ora è un campo nuovo e distinto (nome breve del luogo, usato per il
 * filtro in archivio) da ricompilare a mano.
 */
class Calypsosub_Migrate_Evento_Luogo_Indirizzo {

	private const OPTION = 'calypsosub_migrated_evento_luogo_indirizzo';

	public function init(): void {
		add_action( 'admin_init', [ $this, 'maybe_run' ] );
	}

	public function maybe_run(): void {
		if ( get_option( self::OPTION ) ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;

		$this->run();
		update_option( self::OPTION, 1 );
	}

	public function run(): void {
		$eventi = get_posts( [
			'post_type'      => 'calypso_evento',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] );

		foreach ( $eventi as $evento_id ) {
			$luogo_attuale = (string) get_post_meta( $evento_id, '_evento_luogo', true );
			if ( $luogo_attuale === '' ) continue;

			if ( (string) get_post_meta( $evento_id, '_evento_indirizzo', true ) === '' ) {
				update_post_meta( $evento_id, '_evento_indirizzo', $luogo_attuale );
			}
			delete_post_meta( $evento_id, '_evento_luogo' );
		}
	}
}
