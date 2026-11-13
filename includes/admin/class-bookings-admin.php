<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pagina "Prenotazioni" in admin, al posto dell'elenco standard del CPT
 * calypso_prenotazione (una riga per prenotazione, senza raggruppamenti).
 *
 * - Riepilogo: una riga per uscita/evento/corso con i conteggi (prenotanti,
 *   accompagnatori, persone, posti, da confermare), filtri per tipo e
 *   periodo, ricerca (anche per nome/email di chi ha prenotato), ordinamento.
 * - Dettaglio (?item=ID): i prenotati di quell'elemento con filtri, ricerca,
 *   ordinamento, conferma/rifiuto per riga o in blocco, export CSV e stampa.
 *
 * I volumi sono piccoli (un club): si caricano tutte le prenotazioni in una
 * query e si filtra/ordina in PHP, invece di costruire query SQL per ogni
 * combinazione di filtri.
 */
class Calypsosub_Bookings_Admin {

	public const PAGE_SLUG = 'calypsosub-bookings';

	private const ACTIVE_STATUSES = [ 'confermata', 'in_attesa' ];

	private Calypsosub_Booking_Manager $manager;

	public function __construct( Calypsosub_Booking_Manager $manager ) {
		$this->manager = $manager;
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'load-edit.php', [ $this, 'redirect_legacy_list' ] );
		add_action( 'admin_post_calypso_bookings_bulk',   [ $this, 'handle_bulk' ] );
		add_action( 'admin_post_calypso_bookings_export', [ $this, 'handle_export' ] );
		add_action( 'admin_post_calypso_bookings_print',  [ $this, 'handle_print' ] );
	}

	public function register_page(): void {
		add_submenu_page(
			'calypsosub',
			__( 'Prenotazioni', 'calypsosub' ),
			__( 'Prenotazioni', 'calypsosub' ),
			'calypsosub_manage',
			self::PAGE_SLUG,
			[ $this, 'render_page' ],
			1 // subito dopo Impostazioni
		);
	}

	public static function page_url( array $args = [] ): string {
		return add_query_arg( array_merge( [ 'page' => self::PAGE_SLUG ], $args ), admin_url( 'admin.php' ) );
	}

	/** Il vecchio elenco (edit.php?post_type=calypso_prenotazione) porta qui. */
	public function redirect_legacy_list(): void {
		if ( ( $_GET['post_type'] ?? '' ) !== 'calypso_prenotazione' ) return;
		wp_safe_redirect( self::page_url() );
		exit;
	}

	public static function status_labels(): array {
		return [
			'in_attesa'    => [ 'label' => __( 'Da confermare', 'calypsosub' ), 'color' => '#92400e', 'bg' => '#fef3c7' ],
			'confermata'   => [ 'label' => __( 'Confermata', 'calypsosub' ),    'color' => '#065f46', 'bg' => '#d1fae5' ],
			'lista_attesa' => [ 'label' => __( 'Lista d\'attesa', 'calypsosub' ), 'color' => '#1e40af', 'bg' => '#dbeafe' ],
			'annullata'    => [ 'label' => __( 'Annullata', 'calypsosub' ),     'color' => '#991b1b', 'bg' => '#fee2e2' ],
			'rifiutata'    => [ 'label' => __( 'Rifiutata', 'calypsosub' ),     'color' => '#991b1b', 'bg' => '#fee2e2' ],
		];
	}

	private static function type_labels(): array {
		return [
			'uscite' => __( 'Uscita', 'calypsosub' ),
			'eventi' => __( 'Evento', 'calypsosub' ),
			'corsi'  => __( 'Corso', 'calypsosub' ),
			'altro'  => __( 'Eliminato', 'calypsosub' ),
		];
	}

	// -------------------------------------------------------------------------
	// Dati
	// -------------------------------------------------------------------------

	/**
	 * Tutte le prenotazioni, una riga ciascuna con i campi già risolti.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function load_bookings(): array {
		$ids = get_posts( [
			'post_type'      => 'calypso_prenotazione',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'ASC',
		] );
		update_meta_cache( 'post', $ids );

		$rows = [];
		foreach ( $ids as $id ) {
			$rows[] = $this->booking_row( (int) $id );
		}
		return $rows;
	}

	private function booking_row( int $id ): array {
		$form = get_post_meta( $id, '_booking_form_data', true );
		$form = is_array( $form ) ? $form : [];
		$uid  = (int) get_post_meta( $id, '_booking_user_id', true );
		$user = $uid ? get_user_by( 'id', $uid ) : false;

		$pick = static function ( array $keys ) use ( $form ): string {
			foreach ( $keys as $k ) {
				if ( isset( $form[ $k ] ) && $form[ $k ] !== '' && $form[ $k ] !== [] ) {
					return trim( is_array( $form[ $k ] ) ? implode( ', ', $form[ $k ] ) : (string) $form[ $k ] );
				}
			}
			return '';
		};

		$nome    = $pick( [ 'nome', 'your-name', 'first-name' ] );
		$cognome = $pick( [ 'cognome', 'last-name' ] );
		if ( $user ) {
			$nome    = $nome ?: (string) $user->first_name;
			$cognome = $cognome ?: (string) $user->last_name;
		}
		$email = $user ? (string) $user->user_email : '';
		$email = $email ?: $pick( [ 'email', 'mail', 'e-mail', 'your-email' ] ) ?: (string) get_post_meta( $id, '_booking_guest_email', true );
		if ( $nome === '' && $cognome === '' ) {
			$nome = $user ? (string) $user->display_name : $email;
		}

		$notes = array_filter( [
			(string) get_post_meta( $id, '_booking_allergies', true ),
			$pick( [ 'note-richieste', 'note', 'messaggio', 'your-message' ] ),
		] );

		$companions = Calypsosub_Booking_Manager::get_companions( $id );

		return [
			'id'         => $id,
			'item_id'    => (int) get_post_meta( $id, '_booking_post_id', true ),
			'status'     => (string) get_post_meta( $id, '_booking_status', true ),
			'nome'       => $nome,
			'cognome'    => $cognome,
			'email'      => $email,
			'telefono'   => $pick( [ 'telefono', 'phone', 'cellulare', 'tel', 'your-phone' ] ),
			'guest'      => ! $user,
			'companions' => $companions,
			'people'     => 1 + $companions,
			'received'   => (string) get_post_meta( $id, '_booking_date', true ),
			'notes'      => implode( ' · ', array_unique( $notes ) ),
			'form'       => $form,
		];
	}

	/**
	 * Dati dell'elemento prenotato (uscita/evento/corso).
	 *
	 * @return array{id:int,type:string,title:string,date:string,capacity:?int,edit:string,view:string}
	 */
	private function item_info( int $item_id ): array {
		$post_type = get_post_type( $item_id );
		$info      = [
			'id'       => $item_id,
			'type'     => 'altro',
			'title'    => sprintf( __( 'Elemento eliminato (#%d)', 'calypsosub' ), $item_id ),
			'date'     => '',
			'capacity' => null,
			'edit'     => '',
			'view'     => '',
		];
		if ( ! $post_type ) return $info;

		$info['title'] = get_the_title( $item_id );
		$info['edit']  = (string) get_edit_post_link( $item_id, 'raw' );
		$info['view']  = (string) get_permalink( $item_id );

		if ( $post_type === 'calypso_occ_uscita' ) {
			$uscita_id     = (int) get_post_meta( $item_id, '_occorrenza_uscita_uscita_id', true );
			$info['type']  = 'uscite';
			$info['title'] = $uscita_id ? get_the_title( $uscita_id ) : $info['title'];
			$info['view']  = $uscita_id ? (string) get_permalink( $uscita_id ) : '';
			$info['date']  = (string) get_post_meta( $item_id, '_occorrenza_uscita_data', true );
		} elseif ( $post_type === 'calypso_evento' ) {
			$info['type'] = 'eventi';
			$info['date'] = calypso_next_future_date( (array) ( get_post_meta( $item_id, '_evento_date', true ) ?: [] ) );
		} elseif ( $post_type === 'calypso_corso' ) {
			$occ          = calypso_get_next_occorrenza( $item_id );
			$info['type'] = 'corsi';
			$info['date'] = $occ ? (string) get_post_meta( $occ->ID, '_occorrenza_data_inizio', true ) : '';
		}
		$info['capacity'] = Calypsosub_Booking_Manager::get_capacity( $item_id );
		return $info;
	}

	/**
	 * Conteggi di un gruppo di prenotazioni. "Prenotanti/accompagnatori/
	 * persone" contano solo quelle che occupano posti (confermate + da
	 * confermare), come i posti sul sito.
	 */
	private static function counts( array $rows ): array {
		$c = [ 'prenotanti' => 0, 'accompagnatori' => 0, 'persone' => 0, 'confermate' => 0, 'da_confermare' => 0, 'lista_attesa' => 0, 'lista_persone' => 0, 'chiuse' => 0, 'totale' => count( $rows ) ];
		foreach ( $rows as $r ) {
			if ( in_array( $r['status'], self::ACTIVE_STATUSES, true ) ) {
				$c['prenotanti']++;
				$c['accompagnatori'] += $r['companions'];
				$c['persone']        += $r['people'];
				$c[ $r['status'] === 'confermata' ? 'confermate' : 'da_confermare' ]++;
			} elseif ( $r['status'] === 'lista_attesa' ) {
				$c['lista_attesa']++;
				$c['lista_persone'] += $r['people'];
			} else {
				$c['chiuse']++;
			}
		}
		return $c;
	}

	private static function matches_search( array $row, string $q ): bool {
		if ( $q === '' ) return true;
		$hay = remove_accents( strtolower( $row['nome'] . ' ' . $row['cognome'] . ' ' . $row['cognome'] . ' ' . $row['nome'] . ' ' . $row['email'] . ' ' . $row['telefono'] ) );
		return str_contains( $hay, remove_accents( strtolower( $q ) ) );
	}

	private static function format_date( string $date ): string {
		return $date === '' ? '—' : calypso_format_datetime( $date );
	}

	private static function arg( string $key, string $default = '' ): string {
		return isset( $_REQUEST[ $key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) : $default;
	}

	// -------------------------------------------------------------------------
	// Pagina
	// -------------------------------------------------------------------------

	public function render_page(): void {
		if ( ! current_user_can( 'calypsosub_manage' ) ) return;

		$item_id = absint( $_GET['item'] ?? 0 );
		echo '<div class="wrap cso-bk">';
		$this->render_styles();
		$this->render_notice();
		if ( $item_id ) {
			$this->render_item( $item_id );
		} else {
			$this->render_overview();
		}
		echo '</div>';
	}

	private function render_notice(): void {
		$msg   = self::arg( 'cso_msg' );
		$count = absint( $_GET['cso_count'] ?? 0 );
		$texts = [
			'confirmed' => sprintf( _n( '%d prenotazione confermata.', '%d prenotazioni confermate.', $count, 'calypsosub' ), $count ),
			'rejected'  => sprintf( _n( '%d prenotazione rifiutata/annullata.', '%d prenotazioni rifiutate/annullate.', $count, 'calypsosub' ), $count ),
			'none'      => __( 'Nessuna prenotazione selezionata.', 'calypsosub' ),
		];
		if ( isset( $texts[ $msg ] ) ) {
			echo '<div class="notice notice-' . ( $msg === 'none' ? 'warning' : 'success' ) . ' is-dismissible"><p>' . esc_html( $texts[ $msg ] ) . '</p></div>';
		}
	}

	private function render_overview(): void {
		$tipo    = self::arg( 'tipo', 'tutti' );
		$periodo = self::arg( 'periodo', 'prossimi' );
		$q       = self::arg( 's' );
		$orderby = self::arg( 'orderby', 'data' );
		$order   = self::arg( 'order', $periodo === 'passati' ? 'desc' : 'asc' ) === 'desc' ? 'desc' : 'asc';
		$today   = current_time( 'Y-m-d' );

		$by_item = [];
		foreach ( $this->load_bookings() as $row ) {
			$by_item[ $row['item_id'] ][] = $row;
		}

		$items = [];
		foreach ( $by_item as $item_id => $rows ) {
			$info = $this->item_info( (int) $item_id );
			if ( $tipo !== 'tutti' && $info['type'] !== $tipo ) continue;

			$day = substr( $info['date'], 0, 10 );
			if ( $periodo === 'prossimi' && $day !== '' && $day < $today ) continue;
			if ( $periodo === 'passati' && ( $day === '' || $day >= $today ) ) continue;

			$hits = 0;
			if ( $q !== '' ) {
				$hits = count( array_filter( $rows, static fn( $r ) => self::matches_search( $r, $q ) ) );
				$title_hit = str_contains( remove_accents( strtolower( $info['title'] ) ), remove_accents( strtolower( $q ) ) );
				if ( ! $title_hit && ! $hits ) continue;
			}
			$items[] = [ 'info' => $info, 'counts' => self::counts( $rows ), 'hits' => $hits ];
		}

		$key = [
			'data'          => static fn( $i ) => $i['info']['date'] === '' ? '9999' : $i['info']['date'],
			'titolo'        => static fn( $i ) => remove_accents( strtolower( $i['info']['title'] ) ),
			'prenotanti'    => static fn( $i ) => $i['counts']['prenotanti'],
			'persone'       => static fn( $i ) => $i['counts']['persone'],
			'da_confermare' => static fn( $i ) => $i['counts']['da_confermare'],
		][ $orderby ] ?? null;
		if ( $key ) {
			usort( $items, static function ( $a, $b ) use ( $key, $order ) {
				$cmp = $key( $a ) <=> $key( $b );
				return $order === 'desc' ? -$cmp : $cmp;
			} );
		}

		$tot = self::counts( array_merge( ...array_values( array_map(
			static fn( $i ) => $by_item[ $i['info']['id'] ],
			$items
		) ?: [ [] ] ) ) );

		$base = [ 'tipo' => $tipo, 'periodo' => $periodo, 's' => $q, 'orderby' => $orderby, 'order' => $order ];
		?>
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Prenotazioni', 'calypsosub' ); ?></h1>
		<hr class="wp-header-end">

		<form method="get" class="cso-bk__toolbar">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>">
			<input type="hidden" name="order" value="<?php echo esc_attr( $order ); ?>">
			<div class="cso-bk__seg">
				<?php foreach ( [ 'tutti' => __( 'Tutti', 'calypsosub' ), 'uscite' => __( 'Uscite', 'calypsosub' ), 'eventi' => __( 'Eventi', 'calypsosub' ), 'corsi' => __( 'Corsi', 'calypsosub' ) ] as $k => $label ) : ?>
					<a class="<?php echo $tipo === $k ? 'is-active' : ''; ?>" href="<?php echo esc_url( self::page_url( array_merge( $base, [ 'tipo' => $k ] ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>
			<input type="hidden" name="tipo" value="<?php echo esc_attr( $tipo ); ?>">
			<select name="periodo" onchange="this.form.submit()">
				<option value="prossimi" <?php selected( $periodo, 'prossimi' ); ?>><?php esc_html_e( 'Prossimi e senza data', 'calypsosub' ); ?></option>
				<option value="passati" <?php selected( $periodo, 'passati' ); ?>><?php esc_html_e( 'Passati', 'calypsosub' ); ?></option>
				<option value="tutti" <?php selected( $periodo, 'tutti' ); ?>><?php esc_html_e( 'Tutti i periodi', 'calypsosub' ); ?></option>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php esc_attr_e( 'Cerca evento o persona (nome, email, telefono)', 'calypsosub' ); ?>">
			<button class="button"><?php esc_html_e( 'Cerca', 'calypsosub' ); ?></button>
			<?php if ( $q !== '' ) : ?><a class="button-link" href="<?php echo esc_url( self::page_url( array_merge( $base, [ 's' => '' ] ) ) ); ?>"><?php esc_html_e( 'Azzera ricerca', 'calypsosub' ); ?></a><?php endif; ?>
		</form>

		<?php $this->render_stats( [
			__( 'Partecipanti', 'calypsosub' ) => [
				__( 'Prenotanti', 'calypsosub' )     => $tot['prenotanti'],
				__( 'Accompagnatori', 'calypsosub' ) => $tot['accompagnatori'],
				__( 'Persone totali', 'calypsosub' ) => $tot['persone'],
			],
			__( 'Stato prenotazioni', 'calypsosub' ) => [
				__( 'Da confermare', 'calypsosub' )      => $tot['da_confermare'],
				__( 'In lista d\'attesa', 'calypsosub' ) => $tot['lista_attesa'],
			],
			__( 'Elenco', 'calypsosub' ) => [
				__( 'Elementi mostrati', 'calypsosub' ) => count( $items ),
			],
		], [ __( 'Da confermare', 'calypsosub' ) => $tot['da_confermare'] > 0 ] ); ?>

		<table class="widefat striped cso-bk__table">
			<thead><tr>
				<?php
				$this->sort_header( __( 'Evento / uscita / corso', 'calypsosub' ), 'titolo', $base );
				$this->sort_header( __( 'Data', 'calypsosub' ), 'data', $base );
				$this->sort_header( __( 'Prenotanti', 'calypsosub' ), 'prenotanti', $base, 'num' );
				echo '<th class="num">' . esc_html__( 'Accomp.', 'calypsosub' ) . '</th>';
				$this->sort_header( __( 'Persone', 'calypsosub' ), 'persone', $base, 'num' );
				echo '<th>' . esc_html__( 'Posti', 'calypsosub' ) . '</th>';
				$this->sort_header( __( 'Da confermare', 'calypsosub' ), 'da_confermare', $base, 'num' );
				echo '<th class="num">' . esc_html__( 'Lista attesa', 'calypsosub' ) . '</th>';
				echo '<th class="num">' . esc_html__( 'Annull./rif.', 'calypsosub' ) . '</th>';
				?>
			</tr></thead>
			<tbody>
			<?php if ( ! $items ) : ?>
				<tr><td colspan="9" class="cso-bk__empty"><?php esc_html_e( 'Nessuna prenotazione con questi filtri.', 'calypsosub' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $items as $it ) :
				$info = $it['info'];
				$c    = $it['counts'];
				$url  = self::page_url( [ 'item' => $info['id'], 's' => $it['hits'] ? $q : '' ] );
				?>
				<tr class="cso-bk__row" onclick="if(!event.target.closest('a'))location.href=this.dataset.href" data-href="<?php echo esc_url( $url ); ?>">
					<td>
						<span class="cso-bk__type cso-bk__type--<?php echo esc_attr( $info['type'] ); ?>"><?php echo esc_html( self::type_labels()[ $info['type'] ] ); ?></span>
						<a class="cso-bk__title" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $info['title'] ); ?></a>
						<?php if ( $it['hits'] ) : ?><span class="cso-bk__hits"><?php echo esc_html( sprintf( _n( '%d persona trovata', '%d persone trovate', $it['hits'], 'calypsosub' ), $it['hits'] ) ); ?></span><?php endif; ?>
					</td>
					<td><?php echo esc_html( self::format_date( $info['date'] ) ); ?></td>
					<td class="num"><?php echo (int) $c['prenotanti']; ?></td>
					<td class="num"><?php echo (int) $c['accompagnatori']; ?></td>
					<td class="num"><strong><?php echo (int) $c['persone']; ?></strong></td>
					<td><?php $this->render_capacity( $c['persone'], $info['capacity'] ); ?></td>
					<td class="num"><?php echo $c['da_confermare'] ? '<span class="cso-bk__pill">' . (int) $c['da_confermare'] . '</span>' : '0'; ?></td>
					<td class="num"><?php echo (int) $c['lista_attesa']; ?></td>
					<td class="num cso-bk__muted"><?php echo (int) $c['chiuse']; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description"><?php esc_html_e( 'Prenotanti, accompagnatori e persone contano le prenotazioni confermate e da confermare: sono quelle che occupano i posti.', 'calypsosub' ); ?></p>
		<?php
	}

	private function render_item( int $item_id ): void {
		$info   = $this->item_info( $item_id );
		$all    = array_values( array_filter( $this->load_bookings(), static fn( $r ) => $r['item_id'] === $item_id ) );
		$c      = self::counts( $all );
		$stato  = self::arg( 'stato', 'tutti' );
		$q      = self::arg( 's' );
		$rows   = $this->filter_sort_rows( $all, $stato, $q, self::arg( 'orderby', 'ricevuta' ), self::arg( 'order', 'asc' ) );
		$base   = [ 'item' => $item_id, 'stato' => $stato, 's' => $q, 'orderby' => self::arg( 'orderby', 'ricevuta' ), 'order' => self::arg( 'order', 'asc' ) ];
		$export = [ 'item' => $item_id, 'stato' => $stato, 's' => $q, 'orderby' => $base['orderby'], 'order' => $base['order'] ];
		$labels = self::status_labels();
		?>
		<p><a href="<?php echo esc_url( self::page_url() ); ?>">← <?php esc_html_e( 'Tutte le prenotazioni', 'calypsosub' ); ?></a></p>
		<h1 class="wp-heading-inline">
			<span class="cso-bk__type cso-bk__type--<?php echo esc_attr( $info['type'] ); ?>"><?php echo esc_html( self::type_labels()[ $info['type'] ] ); ?></span>
			<?php echo esc_html( $info['title'] ); ?>
		</h1>
		<p class="cso-bk__sub">
			<?php echo esc_html( self::format_date( $info['date'] ) ); ?>
			<?php if ( $info['view'] ) : ?> · <a href="<?php echo esc_url( $info['view'] ); ?>" target="_blank"><?php esc_html_e( 'Vedi sul sito', 'calypsosub' ); ?></a><?php endif; ?>
			<?php if ( $info['edit'] ) : ?> · <a href="<?php echo esc_url( $info['edit'] ); ?>"><?php esc_html_e( 'Modifica', 'calypsosub' ); ?></a><?php endif; ?>
		</p>
		<hr class="wp-header-end">

		<?php
		$stats = [
			__( 'Partecipanti', 'calypsosub' ) => [
				__( 'Prenotanti', 'calypsosub' )     => $c['prenotanti'],
				__( 'Accompagnatori', 'calypsosub' ) => $c['accompagnatori'],
				__( 'Persone totali', 'calypsosub' ) => $c['persone'],
			],
		];
		if ( $info['capacity'] !== null ) {
			$stats[ __( 'Posti', 'calypsosub' ) ] = [
				__( 'Liberi', 'calypsosub' ) => max( 0, $info['capacity'] - $c['persone'] ) . ' / ' . $info['capacity'],
			];
		}
		$stats[ __( 'Stato prenotazioni', 'calypsosub' ) ] = [
			__( 'Confermate', 'calypsosub' )    => $c['confermate'],
			__( 'Da confermare', 'calypsosub' ) => $c['da_confermare'],
			__( 'Lista d\'attesa', 'calypsosub' ) => $c['lista_attesa'] ? sprintf( '%d (%d pers.)', $c['lista_attesa'], $c['lista_persone'] ) : 0,
		];
		$this->render_stats( $stats, [ __( 'Da confermare', 'calypsosub' ) => $c['da_confermare'] > 0 ] );
		?>

		<form method="get" class="cso-bk__toolbar">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
			<input type="hidden" name="item" value="<?php echo (int) $item_id; ?>">
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $base['orderby'] ); ?>">
			<input type="hidden" name="order" value="<?php echo esc_attr( $base['order'] ); ?>">
			<select name="stato" onchange="this.form.submit()">
				<option value="tutti" <?php selected( $stato, 'tutti' ); ?>><?php echo esc_html( sprintf( __( 'Tutti gli stati (%d)', 'calypsosub' ), $c['totale'] ) ); ?></option>
				<option value="attive" <?php selected( $stato, 'attive' ); ?>><?php echo esc_html( sprintf( __( 'Confermate + da confermare (%d)', 'calypsosub' ), $c['prenotanti'] ) ); ?></option>
				<?php foreach ( $labels as $k => $l ) :
					$n = count( array_filter( $all, static fn( $r ) => $r['status'] === $k ) );
					?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $stato, $k ); ?>><?php echo esc_html( sprintf( '%s (%d)', $l['label'], $n ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php esc_attr_e( 'Cerca nome, email, telefono', 'calypsosub' ); ?>">
			<button class="button"><?php esc_html_e( 'Cerca', 'calypsosub' ); ?></button>
			<span class="cso-bk__spacer"></span>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( [ 'action' => 'calypso_bookings_export' ], $export ), admin_url( 'admin-post.php' ) ), 'calypso_bookings_export' ) ); ?>"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Esporta CSV (Excel)', 'calypsosub' ); ?></a>
			<a class="button" target="_blank" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( [ 'action' => 'calypso_bookings_print' ], $export ), admin_url( 'admin-post.php' ) ), 'calypso_bookings_print' ) ); ?>"><span class="dashicons dashicons-printer"></span> <?php esc_html_e( 'Stampa elenco', 'calypsosub' ); ?></a>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="calypso_bookings_bulk">
			<input type="hidden" name="redirect" value="<?php echo esc_url( self::page_url( $base ) ); ?>">
			<?php wp_nonce_field( 'calypso_bookings_bulk' ); ?>
			<div class="cso-bk__bulk">
				<select name="bulk_action">
					<option value=""><?php esc_html_e( 'Azioni sui selezionati', 'calypsosub' ); ?></option>
					<option value="confirm"><?php esc_html_e( 'Conferma (invia email)', 'calypsosub' ); ?></option>
					<option value="reject"><?php esc_html_e( 'Rifiuta / annulla (invia email)', 'calypsosub' ); ?></option>
				</select>
				<button class="button" onclick="return confirm('<?php echo esc_js( __( 'Applicare l\'azione alle prenotazioni selezionate? A ciascuno verrà inviata l\'email corrispondente.', 'calypsosub' ) ); ?>');"><?php esc_html_e( 'Applica', 'calypsosub' ); ?></button>
				<span class="cso-bk__muted"><?php echo esc_html( sprintf( _n( '%d prenotazione mostrata', '%d prenotazioni mostrate', count( $rows ), 'calypsosub' ), count( $rows ) ) ); ?></span>
			</div>

			<table class="widefat striped cso-bk__table">
				<thead><tr>
					<td class="check-column"><input type="checkbox" onclick="for(const c of this.closest('table').querySelectorAll('tbody input[type=checkbox]'))c.checked=this.checked"></td>
					<?php
					$this->sort_header( __( 'Nome', 'calypsosub' ), 'nome', $base );
					echo '<th>' . esc_html__( 'Contatti', 'calypsosub' ) . '</th>';
					$this->sort_header( __( 'Accomp.', 'calypsosub' ), 'accompagnatori', $base, 'num' );
					echo '<th class="num">' . esc_html__( 'Persone', 'calypsosub' ) . '</th>';
					$this->sort_header( __( 'Stato', 'calypsosub' ), 'stato', $base );
					$this->sort_header( __( 'Ricevuta', 'calypsosub' ), 'ricevuta', $base );
					echo '<th>' . esc_html__( 'Note', 'calypsosub' ) . '</th>';
					echo '<th>' . esc_html__( 'Azioni', 'calypsosub' ) . '</th>';
					?>
				</tr></thead>
				<tbody>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="9" class="cso-bk__empty"><?php esc_html_e( 'Nessuna prenotazione con questi filtri.', 'calypsosub' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $r ) :
					$l = $labels[ $r['status'] ] ?? [ 'label' => $r['status'], 'color' => '#444', 'bg' => '#eee' ];
					?>
					<tr>
						<th class="check-column"><input type="checkbox" name="booking_ids[]" value="<?php echo (int) $r['id']; ?>"></th>
						<td>
							<a class="cso-bk__title" href="<?php echo esc_url( (string) get_edit_post_link( $r['id'] ) ); ?>"><?php echo esc_html( trim( $r['cognome'] . ' ' . $r['nome'] ) ); ?></a>
							<?php if ( $r['guest'] ) : ?><span class="cso-bk__muted"><?php esc_html_e( 'ospite', 'calypsosub' ); ?></span><?php endif; ?>
						</td>
						<td class="cso-bk__contacts">
							<?php if ( $r['email'] ) : ?><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo esc_html( $r['email'] ); ?></a><?php endif; ?>
							<?php if ( $r['telefono'] ) : ?><br><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $r['telefono'] ) ); ?>"><?php echo esc_html( $r['telefono'] ); ?></a><?php endif; ?>
						</td>
						<td class="num"><?php echo (int) $r['companions']; ?></td>
						<td class="num"><?php echo (int) $r['people']; ?></td>
						<td><span class="cso-bk__badge" style="background:<?php echo esc_attr( $l['bg'] ); ?>;color:<?php echo esc_attr( $l['color'] ); ?>"><?php echo esc_html( $l['label'] ); ?></span></td>
						<td><?php echo esc_html( $r['received'] ? mysql2date( 'd/m/Y H:i', $r['received'] ) : '—' ); ?></td>
						<td class="cso-bk__notes"><?php echo esc_html( $r['notes'] ); ?></td>
						<td class="cso-bk__actions"><?php $this->render_row_actions( $r ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
		<?php
	}

	private function render_row_actions( array $r ): void {
		$confirm = wp_nonce_url( admin_url( 'admin.php?action=calypso_confirm_booking&booking_id=' . $r['id'] ), 'calypso_confirm_' . $r['id'] );
		$reject  = wp_nonce_url( admin_url( 'admin.php?action=calypso_reject_booking&booking_id=' . $r['id'] ), 'calypso_reject_' . $r['id'] );
		if ( in_array( $r['status'], [ 'in_attesa', 'lista_attesa' ], true ) ) {
			echo '<a class="button button-small cso-bk__ok" href="' . esc_url( $confirm ) . '">✓ ' . esc_html__( 'Conferma', 'calypsosub' ) . '</a> ';
			echo '<a class="button button-small cso-bk__ko" href="' . esc_url( $reject ) . '" onclick="return confirm(\'' . esc_js( __( 'Rifiutare la prenotazione? Verrà inviata l\'email di rifiuto.', 'calypsosub' ) ) . '\')">✕ ' . esc_html__( 'Rifiuta', 'calypsosub' ) . '</a>';
		} elseif ( $r['status'] === 'confermata' ) {
			echo '<a class="button button-small cso-bk__ko" href="' . esc_url( $reject ) . '" onclick="return confirm(\'' . esc_js( __( 'Annullare la prenotazione confermata? Verrà inviata un\'email e il posto andrà al primo in lista d\'attesa.', 'calypsosub' ) ) . '\')">' . esc_html__( 'Annulla', 'calypsosub' ) . '</a>';
		}
	}

	private function filter_sort_rows( array $rows, string $stato, string $q, string $orderby, string $order ): array {
		$rows = array_values( array_filter( $rows, static function ( $r ) use ( $stato, $q ) {
			if ( $stato === 'attive' && ! in_array( $r['status'], self::ACTIVE_STATUSES, true ) ) return false;
			if ( ! in_array( $stato, [ 'tutti', 'attive' ], true ) && $r['status'] !== $stato ) return false;
			return self::matches_search( $r, $q );
		} ) );

		$status_rank = array_flip( array_keys( self::status_labels() ) );
		$key = [
			'nome'           => static fn( $r ) => remove_accents( strtolower( $r['cognome'] . ' ' . $r['nome'] ) ),
			'accompagnatori' => static fn( $r ) => $r['companions'],
			'stato'          => static fn( $r ) => $status_rank[ $r['status'] ] ?? 99,
			'ricevuta'       => static fn( $r ) => $r['received'],
		][ $orderby ] ?? static fn( $r ) => $r['received'];
		usort( $rows, static function ( $a, $b ) use ( $key, $order ) {
			$cmp = $key( $a ) <=> $key( $b );
			return $order === 'desc' ? -$cmp : $cmp;
		} );
		return $rows;
	}

	private function sort_header( string $label, string $key, array $base, string $class = '' ): void {
		$active = ( $base['orderby'] ?? '' ) === $key;
		$next   = $active && ( $base['order'] ?? 'asc' ) === 'asc' ? 'desc' : 'asc';
		$arrow  = $active ? ( $base['order'] === 'asc' ? ' ▲' : ' ▼' ) : '';
		echo '<th class="' . esc_attr( $class ) . '"><a href="' . esc_url( self::page_url( array_merge( $base, [ 'orderby' => $key, 'order' => $next ] ) ) ) . '">' . esc_html( $label . $arrow ) . '</a></th>';
	}

	/**
	 * Riepilogo a sola lettura: gruppi di numeri separati da una linea, in
	 * un unico pannello piatto — non riquadri singoli che sembrano pulsanti.
	 *
	 * @param array<string,array<string,int|string>> $groups
	 * @param array<string,bool>                     $highlight
	 */
	private function render_stats( array $groups, array $highlight = [] ): void {
		echo '<div class="cso-bk__stats" role="group" aria-label="' . esc_attr__( 'Riepilogo', 'calypsosub' ) . '">';
		foreach ( $groups as $group => $stats ) {
			echo '<div class="cso-bk__group"><div class="cso-bk__group-title">' . esc_html( $group ) . '</div><dl>';
			foreach ( $stats as $label => $value ) {
				echo '<div class="cso-bk__stat' . ( ! empty( $highlight[ $label ] ) ? ' is-warn' : '' ) . '"><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( (string) $value ) . '</dd></div>';
			}
			echo '</dl></div>';
		}
		echo '</div>';
	}

	private function render_capacity( int $people, ?int $capacity ): void {
		if ( $capacity === null ) {
			echo '<span class="cso-bk__muted">' . esc_html__( 'illimitati', 'calypsosub' ) . '</span>';
			return;
		}
		$pct  = $capacity > 0 ? min( 100, (int) round( $people / $capacity * 100 ) ) : 100;
		$full = $people >= $capacity;
		echo '<div class="cso-bk__cap' . ( $full ? ' is-full' : '' ) . '"><i style="width:' . (int) $pct . '%"></i></div>';
		echo '<small>' . (int) $people . ' / ' . (int) $capacity . ( $full ? ' · ' . esc_html__( 'pieno', 'calypsosub' ) : '' ) . '</small>';
	}

	private function render_styles(): void {
		?>
		<style>
		.cso-bk__toolbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:16px 0}
		.cso-bk__toolbar input[type=search]{min-width:280px}
		.cso-bk__spacer{flex:1}
		.cso-bk__toolbar .dashicons{vertical-align:text-bottom;font-size:16px}
		.cso-bk__seg{display:inline-flex;border:1px solid #c3c4c7;border-radius:4px;overflow:hidden;background:#fff}
		.cso-bk__seg a{padding:5px 14px;text-decoration:none;color:#2c3338;border-left:1px solid #c3c4c7}
		.cso-bk__seg a:first-child{border-left:0}
		.cso-bk__seg a.is-active{background:#2271b1;color:#fff}
		.cso-bk__stats{display:flex;flex-wrap:wrap;margin:12px 0 16px;padding:12px 0;border-top:1px solid #dcdcde;border-bottom:1px solid #dcdcde;cursor:default;user-select:text}
		.cso-bk__group{padding:0 28px;border-left:1px solid #dcdcde}
		.cso-bk__group:first-child{padding-left:0;border-left:0}
		.cso-bk__group-title{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#8c8f94;margin-bottom:4px}
		.cso-bk__group dl{display:flex;gap:24px;margin:0}
		.cso-bk__stat{display:flex;flex-direction:column-reverse}
		.cso-bk__stat dd{margin:0;font-size:20px;font-weight:700;color:#1d2327;line-height:1.3}
		.cso-bk__stat dt{font-size:12px;color:#50575e}
		.cso-bk__stat.is-warn dd{color:#b26200}
		@media(max-width:782px){.cso-bk__group{padding:8px 0;border-left:0;width:100%}}
		.cso-bk__table th.num,.cso-bk__table td.num{text-align:center}
		.cso-bk__table th a{color:inherit;text-decoration:none}
		.cso-bk__row{cursor:pointer}
		.cso-bk__row:hover td{background:#f0f6fc}
		.cso-bk__title{font-weight:600;font-size:14px}
		.cso-bk__type{display:inline-block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:3px;margin-right:6px;vertical-align:middle;background:#f0f0f1;color:#50575e}
		.cso-bk__type--uscite{background:#e0f2fe;color:#075985}
		.cso-bk__type--eventi{background:#fce7f3;color:#9d174d}
		.cso-bk__type--corsi{background:#ede9fe;color:#5b21b6}
		.cso-bk__hits{display:block;font-size:12px;color:#2271b1;margin-top:2px}
		.cso-bk__pill{display:inline-block;min-width:22px;padding:1px 7px;border-radius:999px;background:#dba617;color:#fff;font-weight:700}
		.cso-bk__badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;white-space:nowrap}
		.cso-bk__muted{color:#8c8f94;font-size:12px}
		.cso-bk__cap{width:90px;height:6px;border-radius:3px;background:#dcdcde;overflow:hidden;margin:4px 0 2px}
		.cso-bk__cap i{display:block;height:100%;background:#2271b1}
		.cso-bk__cap.is-full i{background:#d63638}
		.cso-bk__empty{text-align:center;padding:24px!important;color:#8c8f94}
		.cso-bk__sub{margin:4px 0 0;color:#50575e}
		.cso-bk__bulk{display:flex;gap:8px;align-items:center;margin:8px 0}
		.cso-bk__contacts{font-size:12px}
		.cso-bk__notes{font-size:12px;max-width:260px}
		.cso-bk__actions{white-space:nowrap}
		.cso-bk__ok{color:#065f46!important;border-color:#065f46!important}
		.cso-bk__ko{color:#991b1b!important;border-color:#991b1b!important}
		h1 .cso-bk__type{font-size:11px}
		</style>
		<?php
	}

	// -------------------------------------------------------------------------
	// Azioni: blocco, export, stampa
	// -------------------------------------------------------------------------

	public function handle_bulk(): void {
		check_admin_referer( 'calypso_bookings_bulk' );
		if ( ! current_user_can( 'calypsosub_manage' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		$ids    = array_filter( array_map( 'absint', (array) ( $_POST['booking_ids'] ?? [] ) ) );
		$action = self::arg( 'bulk_action' );
		$done   = 0;
		foreach ( $ids as $id ) {
			if ( get_post_type( $id ) !== 'calypso_prenotazione' ) continue;
			$ok = $action === 'confirm' ? $this->manager->admin_confirm( $id ) : ( $action === 'reject' ? $this->manager->admin_reject( $id ) : false );
			if ( $ok ) $done++;
		}

		$redirect = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['redirect'] ?? '' ) ), self::page_url() );
		$msg      = ! $ids || ! $action ? 'none' : ( $action === 'confirm' ? 'confirmed' : 'rejected' );
		wp_safe_redirect( add_query_arg( [ 'cso_msg' => $msg, 'cso_count' => $done ], $redirect ) );
		exit;
	}

	/** Righe filtrate/ordinate per export e stampa, dagli stessi parametri della pagina. */
	private function request_rows(): array {
		$item_id = absint( $_GET['item'] ?? 0 );
		$rows    = array_values( array_filter( $this->load_bookings(), static fn( $r ) => $r['item_id'] === $item_id ) );
		return [ $this->item_info( $item_id ), $this->filter_sort_rows( $rows, self::arg( 'stato', 'tutti' ), self::arg( 's' ), self::arg( 'orderby', 'ricevuta' ), self::arg( 'order', 'asc' ) ) ];
	}

	public function handle_export(): void {
		check_admin_referer( 'calypso_bookings_export' );
		if ( ! current_user_can( 'calypsosub_manage' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		[ $info, $rows ] = $this->request_rows();
		$labels          = self::status_labels();

		// Tutti i campi del form presenti in almeno una prenotazione, in coda
		// alle colonne fisse (i form CF7 possono avere campi diversi).
		$skip       = [ 'nome', 'cognome', 'email', 'telefono' ];
		$form_keys  = [];
		foreach ( $rows as $r ) {
			foreach ( array_keys( $r['form'] ) as $k ) {
				// Solo nomi di campo "normali": i dati del form arrivano dal
				// visitatore e un nome di campo arbitrario finirebbe
				// nell'intestazione del CSV.
				if ( ! in_array( $k, $skip, true ) && ! Calypsosub_Booking_Fields::is_hidden( (string) $k ) && preg_match( '/^[a-z0-9][a-z0-9_-]*$/i', (string) $k ) ) $form_keys[ $k ] = true;
			}
		}
		$form_keys = array_keys( $form_keys );

		$filename = sanitize_title( $info['title'] . ' ' . substr( $info['date'], 0, 10 ) ) . '-prenotazioni.csv';
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // BOM: Excel riconosce l'UTF-8 (accenti).
		// Punto e virgola: separatore che Excel in italiano apre in colonne.
		fputcsv( $out, array_map( [ self::class, 'csv_safe' ], array_merge( [ 'Cognome', 'Nome', 'Email', 'Telefono', 'Accompagnatori', 'Persone', 'Stato', 'Ricevuta', 'Note' ], array_map( [ 'Calypsosub_Booking_Fields', 'label' ], $form_keys ) ) ), ';' );
		foreach ( $rows as $r ) {
			$line = [ $r['cognome'], $r['nome'], $r['email'], $r['telefono'], $r['companions'], $r['people'], $labels[ $r['status'] ]['label'] ?? $r['status'], $r['received'], $r['notes'] ];
			foreach ( $form_keys as $k ) {
				$v      = $r['form'][ $k ] ?? '';
				$line[] = is_array( $v ) ? implode( ', ', $v ) : (string) $v;
			}
			fputcsv( $out, array_map( [ self::class, 'csv_safe' ], $line ), ';' );
		}
		fclose( $out );
		exit;
	}

	/** Evita che Excel interpreti come formula un valore inserito da un utente. */
	private static function csv_safe( $value ): string {
		$value = (string) $value;
		return ( $value !== '' && in_array( $value[0], [ '=', '+', '-', '@', "\t", "\r" ], true ) && ! is_numeric( $value ) ) ? "'" . $value : $value;
	}

	public function handle_print(): void {
		check_admin_referer( 'calypso_bookings_print' );
		if ( ! current_user_can( 'calypsosub_manage' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		[ $info, $rows ] = $this->request_rows();
		$c               = self::counts( $rows );
		$labels          = self::status_labels();
		header( 'Content-Type: text/html; charset=utf-8' );
		?>
<!doctype html>
<html lang="it"><head><meta charset="utf-8"><title><?php echo esc_html( $info['title'] ); ?> — <?php esc_html_e( 'Elenco partecipanti', 'calypsosub' ); ?></title>
<style>
body{font-family:-apple-system,Segoe UI,Roboto,sans-serif;margin:24px;color:#111;font-size:13px}
h1{font-size:20px;margin:0 0 4px}.sub{color:#555;margin:0 0 12px}
.tot{margin:0 0 14px;font-weight:600}
table{width:100%;border-collapse:collapse}th,td{border:1px solid #bbb;padding:6px 8px;text-align:left;vertical-align:top}
th{background:#eee}td.n{text-align:center}td.chk{width:28px}
@media print{body{margin:10mm}button{display:none}}
</style></head>
<body onload="window.print()">
<button onclick="window.print()" style="float:right"><?php esc_html_e( 'Stampa', 'calypsosub' ); ?></button>
<h1><?php echo esc_html( $info['title'] ); ?></h1>
<p class="sub"><?php echo esc_html( self::format_date( $info['date'] ) ); ?></p>
<p class="tot"><?php echo esc_html( sprintf( __( '%1$d prenotanti · %2$d accompagnatori · %3$d persone', 'calypsosub' ), $c['prenotanti'], $c['accompagnatori'], $c['persone'] ) ); ?></p>
<table>
<thead><tr><th>#</th><th><?php esc_html_e( 'Cognome e nome', 'calypsosub' ); ?></th><th><?php esc_html_e( 'Telefono', 'calypsosub' ); ?></th><th><?php esc_html_e( 'Accomp.', 'calypsosub' ); ?></th><th><?php esc_html_e( 'Stato', 'calypsosub' ); ?></th><th><?php esc_html_e( 'Note', 'calypsosub' ); ?></th><th class="chk">✓</th></tr></thead>
<tbody>
<?php foreach ( $rows as $i => $r ) : ?>
<tr><td class="n"><?php echo (int) $i + 1; ?></td><td><?php echo esc_html( trim( $r['cognome'] . ' ' . $r['nome'] ) ); ?></td><td><?php echo esc_html( $r['telefono'] ); ?></td><td class="n"><?php echo (int) $r['companions']; ?></td><td><?php echo esc_html( $labels[ $r['status'] ]['label'] ?? $r['status'] ); ?></td><td><?php echo esc_html( $r['notes'] ); ?></td><td class="chk"></td></tr>
<?php endforeach; ?>
</tbody></table>
</body></html>
		<?php
		exit;
	}
}
