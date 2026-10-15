/**
 * Picker immagini dedicato per le comunicazioni: apre il modale media di
 * WordPress (wp.media), ma non inserisce nulla nel corpo — tiene solo gli
 * ID selezionati in un campo hidden. Così l'invio (Telegram/email) può
 * sempre risolvere la versione a piena risoluzione dell'attachment,
 * indipendentemente dalla dimensione che l'operatore vedrebbe se l'avesse
 * invece inserita nell'editor classico (che di default inserisce una
 * dimensione ridotta, non l'originale).
 */
( function ( $ ) {
	function syncHiddenInput( items ) {
		var input = document.getElementById( 'cso_com_image_ids' );
		if ( ! input ) return;
		input.value = items.map( function ( i ) { return i.id; } ).join( ',' );
		// Il contatore Telegram ascolta questo campo per sapere se c'è
		// almeno un'immagine — senza questo evento non si aggiornerebbe.
		input.dispatchEvent( new Event( 'change' ) );
	}

	function renderPreviews( items, onRemove ) {
		var wrap = document.getElementById( 'cso-com-images-preview' );
		if ( ! wrap ) return;
		wrap.innerHTML = '';

		items.forEach( function ( item ) {
			var box = document.createElement( 'div' );
			box.style.position = 'relative';
			box.style.width = '80px';
			box.style.height = '80px';

			var img = document.createElement( 'img' );
			img.src = item.thumb;
			img.style.width = '80px';
			img.style.height = '80px';
			img.style.objectFit = 'cover';
			img.style.borderRadius = '4px';
			img.style.display = 'block';

			var remove = document.createElement( 'button' );
			remove.type = 'button';
			remove.textContent = '×';
			remove.setAttribute( 'aria-label', 'Rimuovi' );
			remove.style.position = 'absolute';
			remove.style.top = '-6px';
			remove.style.right = '-6px';
			remove.style.width = '20px';
			remove.style.height = '20px';
			remove.style.lineHeight = '18px';
			remove.style.padding = '0';
			remove.style.borderRadius = '50%';
			remove.style.border = 'none';
			remove.style.background = '#d63638';
			remove.style.color = '#fff';
			remove.style.cursor = 'pointer';
			remove.addEventListener( 'click', function () { onRemove( item.id ); } );

			box.appendChild( img );
			box.appendChild( remove );
			wrap.appendChild( box );
		} );
	}

	function init() {
		var pickBtn = document.getElementById( 'cso-com-images-pick' );
		if ( ! pickBtn || typeof wp === 'undefined' || ! wp.media ) return;

		var items = ( window.calypsoComInitialImages || [] ).slice();

		function setItems( next ) {
			items = next;
			renderPreviews( items, function ( id ) {
				setItems( items.filter( function ( i ) { return i.id !== id; } ) );
			} );
			syncHiddenInput( items );
		}

		renderPreviews( items, function ( id ) {
			setItems( items.filter( function ( i ) { return i.id !== id; } ) );
		} );

		var frame;
		pickBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( pickBtn.disabled ) return;

			frame = wp.media( {
				title: window.calypsoComPickTitle || 'Scegli immagini',
				multiple: true,
				library: { type: 'image' },
			} );

			frame.on( 'select', function () {
				var selection = frame.state().get( 'selection' );
				setItems( selection.map( function ( attachment ) {
					var json  = attachment.toJSON();
					var thumb = ( json.sizes && json.sizes.thumbnail ) ? json.sizes.thumbnail.url : json.url;
					return { id: json.id, thumb: thumb };
				} ) );
			} );

			frame.open();
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )( jQuery );
