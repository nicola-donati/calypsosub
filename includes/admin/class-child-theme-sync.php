<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Copia il child theme bundlato nel plugin (cartella /child-theme) dentro
 * la cartella del tema "Calypso Sub Arezzo" installato su WordPress, così
 * theme.json/style.css/functions.php/assets restano allineati al plugin
 * senza dover rifare manualmente lo zip del tema a ogni release.
 *
 * Scatta da sola a fine aggiornamento del plugin (upgrader_process_complete)
 * e può anche essere rilanciata a mano dalla pagina Calypso Sub → Child theme.
 */
class Calypsosub_Child_Theme_Sync {

	private const OPTION_VERSION = 'calypsosub_child_theme_synced_version';
	private const OPTION_LOG     = 'calypsosub_child_theme_sync_log';
	private const THEME_NAME     = 'Calypso Sub Arezzo';
	private const THEME_AUTHOR   = 'Calypso Sub';

	public function init(): void {
		add_action( 'upgrader_process_complete',        [ $this, 'maybe_sync' ] );
		add_action( 'admin_init',                        [ $this, 'maybe_sync' ] );
		add_action( 'admin_post_calypsosub_sync_theme',  [ $this, 'handle_manual_sync' ] );
		add_action( 'admin_menu',                        [ $this, 'register_page' ] );
	}

	public function register_page(): void {
		add_submenu_page(
			'calypsosub',
			__( 'Child theme', 'calypsosub' ),
			__( 'Child theme', 'calypsosub' ),
			'calypsosub_manage',
			'calypsosub-child-theme',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Scatta sia da upgrader_process_complete sia da admin_init: la versione
	 * del plugin è l'unica fonte di verità su "serve risincronizzare?",
	 * perché il formato di $hook_extra passato da upgrader_process_complete
	 * cambia secondo il tipo di aggiornamento (update singolo, bulk, upload
	 * con sovrascrittura...) e provare a riconoscerli tutti è fragile.
	 * Il controllo su admin_init garantisce che la sync parta comunque al
	 * primo caricamento di una pagina di wp-admin dopo l'aggiornamento.
	 */
	public function maybe_sync(): void {
		if ( ! is_admin() ) return;
		if ( get_option( self::OPTION_VERSION ) === CALYPSOSUB_VERSION ) return;
		if ( ! current_user_can( 'calypsosub_manage' ) ) return;

		$this->sync( 'auto' );
	}

	public function handle_manual_sync(): void {
		if ( ! current_user_can( 'calypsosub_manage' ) ) wp_die( 'Permesso negato.' );
		check_admin_referer( 'calypsosub_sync_theme', '_cso_sync_nonce' );

		$this->sync( 'manual' );

		wp_redirect( add_query_arg(
			[ 'page' => 'calypsosub-child-theme', 'synced' => '1' ],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Copia child-theme/ (tranne i file .md) dal plugin nella cartella del
	 * tema live. Se il tema non è installato/trovato non tocca nulla.
	 */
	public function sync( string $trigger = 'manual' ): array {
		$source = CALYPSOSUB_PATH . 'child-theme';
		$log    = [ sprintf( '[%s] Sync %s avviata.', current_time( 'mysql' ), $trigger ) ];

		if ( ! is_dir( $source ) ) {
			$log[] = 'Nessuna cartella child-theme nel plugin — sync annullata.';
			$this->save_log( $log );
			return $log;
		}

		$target_theme = $this->find_target_theme();
		if ( ! $target_theme ) {
			$log[] = sprintf( 'Tema "%s" non trovato tra quelli installati — sync annullata, nessun file toccato.', self::THEME_NAME );
			$this->save_log( $log );
			return $log;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		global $wp_filesystem;
		if ( ! WP_Filesystem() || ! $wp_filesystem ) {
			$log[] = 'Impossibile inizializzare il filesystem WP (credenziali richieste?) — sync annullata.';
			$this->save_log( $log );
			return $log;
		}

		$target = $target_theme->get_stylesheet_directory();
		$copied = $this->copy_recursive( $source, $target, $wp_filesystem );

		$log[] = sprintf( 'Target: %s (%s)', $target_theme->get( 'Name' ), $target );
		$log[] = sprintf( '%d file copiati.', count( $copied ) );
		foreach ( $copied as $file ) {
			$log[] = '  + ' . $file;
		}

		update_option( self::OPTION_VERSION, CALYPSOSUB_VERSION );
		$this->save_log( $log );
		return $log;
	}

	private function find_target_theme(): ?WP_Theme {
		$active = wp_get_theme();
		if ( $this->theme_matches( $active ) ) return $active;

		foreach ( wp_get_themes() as $theme ) {
			if ( $this->theme_matches( $theme ) ) return $theme;
		}
		return null;
	}

	private function theme_matches( WP_Theme $theme ): bool {
		return $theme->exists()
			&& $theme->get( 'Name' ) === self::THEME_NAME
			&& $theme->get( 'Author' ) === self::THEME_AUTHOR;
	}

	/** @return string[] percorsi relativi copiati */
	private function copy_recursive( string $source, string $target, $fs ): array {
		$copied = [];
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $it as $file ) {
			if ( strtolower( $file->getExtension() ) === 'md' ) continue; // mai il README

			$rel  = ltrim( str_replace( $source, '', $file->getPathname() ), '/\\' );
			$rel  = str_replace( '\\', '/', $rel );
			$dest = $target . '/' . $rel;

			wp_mkdir_p( dirname( $dest ) );
			if ( $fs->copy( $file->getPathname(), $dest, true, FS_CHMOD_FILE ) ) {
				$copied[] = $rel;
			}
		}
		return $copied;
	}

	private function save_log( array $log ): void {
		update_option( self::OPTION_LOG, $log );
		foreach ( $log as $line ) {
			error_log( '[calypsosub][theme-sync] ' . $line );
		}
	}

	/* ── Pagina admin ──────────────────────────────────────────────────────── */

	public function render_page(): void {
		if ( ! current_user_can( 'calypsosub_manage' ) ) return;

		$synced  = isset( $_GET['synced'] );
		$version = get_option( self::OPTION_VERSION, '' );
		$log     = (array) get_option( self::OPTION_LOG, [] );
		$target  = $this->find_target_theme();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Calypso Sub — Child theme', 'calypsosub' ); ?></h1>

			<?php if ( $synced ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Sincronizzazione completata — guarda il log sotto.', 'calypsosub' ); ?></p></div>
			<?php endif; ?>

			<p>
				<?php if ( $target ) : ?>
					<?php printf(
						/* translators: 1: nome tema, 2: slug cartella tema */
						esc_html__( 'Tema trovato: %1$s (%2$s)', 'calypsosub' ),
						esc_html( $target->get( 'Name' ) ),
						esc_html( $target->get_stylesheet() )
					); ?>
				<?php else : ?>
					<strong style="color:#b32d2e">
						<?php printf(
							esc_html__( 'Nessun tema "%s" trovato tra quelli installati — la sincronizzazione non farà nulla finché non lo installi.', 'calypsosub' ),
							esc_html( self::THEME_NAME )
						); ?>
					</strong>
				<?php endif; ?>
			</p>
			<p>
				<?php printf( esc_html__( 'Ultima versione sincronizzata: %s', 'calypsosub' ), esc_html( $version ?: '—' ) ); ?>
				&nbsp;·&nbsp;
				<?php printf( esc_html__( 'Versione plugin attuale: %s', 'calypsosub' ), esc_html( CALYPSOSUB_VERSION ) ); ?>
			</p>
			<p class="description">
				<?php esc_html_e( 'Dopo ogni aggiornamento del plugin, i file dentro child-theme/ (tutto tranne README.md) vengono copiati automaticamente nel tema live qui sopra. Usa il bottone per rilanciarla a mano in qualsiasi momento.', 'calypsosub' ); ?>
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'calypsosub_sync_theme', '_cso_sync_nonce' ); ?>
				<input type="hidden" name="action" value="calypsosub_sync_theme">
				<?php submit_button( __( 'Sincronizza ora il child theme', 'calypsosub' ), 'primary', 'submit', true,
					$target ? [] : [ 'disabled' => 'disabled' ]
				); ?>
			</form>

			<?php if ( $log ) : ?>
			<h2><?php esc_html_e( 'Ultimo log', 'calypsosub' ); ?></h2>
			<pre style="background:#fff;border:1px solid #ddd;padding:12px;max-height:320px;overflow:auto;font-size:12px;line-height:1.5"><?php echo esc_html( implode( "\n", $log ) ); ?></pre>
			<?php endif; ?>
		</div>
		<?php
	}
}
