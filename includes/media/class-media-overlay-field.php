<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Media_Overlay_Field {

	public const META_KEY = '_calypso_overlay_text';

	public function init(): void {
		add_filter( 'attachment_fields_to_edit', [ $this, 'add_field' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'save_field' ], 10, 2 );
	}

	public function add_field( array $form_fields, $post ): array {
		if ( ! $post || strpos( (string) $post->post_mime_type, 'image/' ) !== 0 ) {
			return $form_fields;
		}

		$form_fields['calypso_overlay_text'] = [
			'label' => __( 'Testo overlay', 'calypsosub' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, self::META_KEY, true ),
			'helps' => __( "Testo mostrato in overlay sopra l'immagine nei blocchi galleria. Se vuoto: didascalia, poi testo alternativo.", 'calypsosub' ),
		];

		return $form_fields;
	}

	public function save_field( array $post, array $attachment ): array {
		if ( isset( $attachment['calypso_overlay_text'] ) ) {
			update_post_meta(
				$post['ID'],
				self::META_KEY,
				sanitize_text_field( $attachment['calypso_overlay_text'] )
			);
		}

		return $post;
	}
}
