/**
 * Aggiunge un filtro a tendina per taxonomy "Comunicazioni" (calypso_com_media)
 * nella vista a griglia della libreria media — a differenza della vista elenco,
 * WordPress core non offre nessun punto di estensione nativo per filtri su
 * taxonomy personalizzate lì, va agganciato al livello Backbone del media grid
 * (wp.media.view.AttachmentsBrowser) e il valore passato all'API via
 * ajax_query_attachments_args lato PHP.
 */
( function ( $, _, Backbone, wp ) {
	if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.view || ! wp.media.view.AttachmentsBrowser ) {
		return;
	}
	if ( ! window.calypsoComMediaTerms || ! window.calypsoComMediaTerms.length ) {
		return;
	}

	var ComMediaFilter = wp.media.view.AttachmentFilters.extend( {
		id: 'media-attachment-calypso-com-media-filter',

		createFilters: function () {
			var filters = {
				all: {
					text: window.calypsoComMediaAllLabel || 'Tutte le comunicazioni',
					props: { calypso_com_media: '' },
					priority: 10,
				},
			};

			_.each( window.calypsoComMediaTerms, function ( term, index ) {
				filters[ term.slug ] = {
					text: term.name,
					props: { calypso_com_media: term.slug },
					priority: 20 + index,
				};
			} );

			this.filters = filters;
		},
	} );

	var OriginalAttachmentsBrowser = wp.media.view.AttachmentsBrowser;

	wp.media.view.AttachmentsBrowser = OriginalAttachmentsBrowser.extend( {
		createToolbar: function () {
			OriginalAttachmentsBrowser.prototype.createToolbar.apply( this, arguments );
			this.toolbar.set(
				'calypsoComMediaFilter',
				new ComMediaFilter( {
					controller: this.controller,
					model: this.collection.props,
					priority: -75,
				} ).render()
			);
		},
	} );
} )( jQuery, _, Backbone, wp );
