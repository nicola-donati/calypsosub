/**
 * Contatore in tempo reale di quanto oggetto+descrizione occuperebbero come
 * didascalia Telegram, mostrato subito sotto il titolo mentre si scrive —
 * solo quando il canale Telegram è selezionato e c'è almeno un'immagine nel
 * corpo (senza immagine non esiste nessuna didascalia/limite). È una guida
 * per chi scrive, non l'autorità finale: il conteggio esatto che decide
 * davvero se Telegram manderà uno o due messaggi resta lato server in
 * Calypsosub_Telegram_Client::send_communication(), eseguito all'invio
 * reale — qui basta un'approssimazione abbastanza precisa da scrivere "a
 * vista" senza dover salvare e ricaricare la pagina per saperlo.
 */
( function () {
	function stripTags( html ) {
		var div = document.createElement( 'div' );
		div.innerHTML = html;
		return div.textContent || div.innerText || '';
	}

	function getBodyHtmlAndText() {
		// editor.isHidden() è la fonte di verità su quale dei due è
		// davvero attivo (true quando switchEditors ha nascosto TinyMCE
		// per mostrare il tab "Testo") — più robusto di un controllo di
		// visibilità separato sul textarea, che può disallinearsi.
		if ( window.tinymce ) {
			var editor = tinymce.get( 'content' );
			if ( editor && ! editor.isHidden() ) {
				return {
					html: editor.getContent(),
					text: editor.getContent( { format: 'text' } ),
				};
			}
		}

		var textarea = document.getElementById( 'content' );
		var html = textarea ? textarea.value : '';
		return { html: html, text: stripTags( html ) };
	}

	function init() {
		var box = document.getElementById( 'cso-com-telegram-counter' );
		var title = document.getElementById( 'title' );
		var telegramCheckbox = document.querySelector( 'input[name="cso_com_channels[]"][value="telegram"]' );
		var imageIdsInput = document.getElementById( 'cso_com_image_ids' );
		if ( ! box || ! title ) return;

		function update() {
			var telegramOn = !! ( telegramCheckbox && telegramCheckbox.checked );
			var body = getBodyHtmlAndText();
			// Le immagini ora si scelgono col picker dedicato (vedi
			// admin-communication-images.js), non più inserite nel corpo —
			// questo campo hidden è la fonte di verità, non il markup
			// dell'editor.
			var hasImage = !! ( imageIdsInput && imageIdsInput.value.trim() !== '' );

			if ( ! telegramOn || ! hasImage ) {
				box.style.display = 'none';
				return;
			}

			var combined = ( title.value + '\n\n' + body.text ).trim();
			var length = combined.length; // le stringhe JS sono già UTF-16, come il limite Telegram
			var max = window.calypsoComTelegramCaptionMax || 1024;
			var fits = length <= max;

			box.style.display = '';
			box.style.borderColor = fits ? '#46b450' : '#d63638';
			box.style.background = fits ? '#edfaef' : '#fcf0f1';
			box.innerHTML =
				'<strong>' + ( window.calypsoComTelegramCounterLabel || 'Telegram' ) + ':</strong> ' +
				length + ' / ' + max + ' ' + ( window.calypsoComTelegramCharsLabel || 'caratteri' ) +
				' — ' + ( fits
					? ( window.calypsoComTelegramFitsLabel || 'entra in un unico messaggio (foto + testo).' )
					: ( window.calypsoComTelegramTooLongLabel || 'verranno inviati due messaggi separati (foto con solo l’oggetto, poi il testo completo).' )
				);
		}

		title.addEventListener( 'input', update );
		if ( telegramCheckbox ) telegramCheckbox.addEventListener( 'change', update );
		if ( imageIdsInput ) imageIdsInput.addEventListener( 'change', update );

		var textarea = document.getElementById( 'content' );
		if ( textarea ) textarea.addEventListener( 'input', update );

		if ( window.tinymce ) {
			var existing = tinymce.get( 'content' );
			if ( existing ) {
				existing.on( 'input keyup SetContent', update );
			} else {
				tinymce.on( 'AddEditor', function ( e ) {
					if ( e.editor.id !== 'content' ) return;
					e.editor.on( 'init', function () {
						e.editor.on( 'input keyup SetContent', update );
						update();
					} );
				} );
			}
		}

		// Cambio tab Visual/Testo (switchEditors) non scatena nessuno degli
		// eventi sopra da solo — un listener diretto sui due bottoni copre
		// anche quel caso.
		document.querySelectorAll( '.wp-editor-tabs button' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () { setTimeout( update, 50 ); } );
		} );

		// Rete di sicurezza: in alcuni ambienti gli eventi 'input' sopra non
		// arrivano in modo affidabile (verificato 2026-10-15 anche con un
		// listener nativo appena creato, quindi non è un problema di questo
		// script) — un controllo periodico garantisce comunque il
		// conteggio aggiornato, indipendentemente dal perché gli eventi
		// siano incoerenti in quel contesto.
		setInterval( update, 500 );

		update();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
