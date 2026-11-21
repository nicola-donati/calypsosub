<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Serves the WebP copy of an image on the front end when one exists.
 *
 * Modern Image Formats (with "fallback JPEG" on) stores a WebP sibling of each
 * image size in the attachment metadata under `sources`, but only rewrites
 * images inside post content. The plugin templates print attachment URLs
 * directly, so they keep serving the JPEG. This class swaps those URLs (and
 * the srcset candidates) for the WebP sibling, size by size, and falls back
 * to the original file whenever a size has no WebP copy.
 *
 * It runs only while a front-end page is rendered. wp_head is excluded so
 * og:image and other social previews keep the JPEG, and admin, REST, AJAX,
 * cron and feeds (emails, exports) are never touched.
 */
class Calypsosub_Webp_Urls {

	private const MIME = 'image/webp';

	private bool $active = false;

	public function init(): void {
		add_action( 'template_redirect', [ $this, 'activate' ], 0 );
		add_action( 'wp_head', [ $this, 'suspend' ], 0 );
		add_action( 'wp_head', [ $this, 'activate' ], PHP_INT_MAX );

		add_filter( 'wp_get_attachment_url', [ $this, 'filter_url' ], 20, 2 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'filter_image_src' ], 20, 2 );
		add_filter( 'wp_calculate_image_srcset_meta', [ $this, 'filter_srcset_meta' ], 20, 4 );
	}

	public function activate(): void {
		$this->active = self::is_front_end_render();
	}

	public function suspend(): void {
		$this->active = false;
	}

	private static function is_front_end_render(): bool {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) return false;
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return false;
		if ( is_feed() || is_embed() ) return false;
		return true;
	}

	/** @param string|false $url */
	public function filter_url( $url, $attachment_id ) {
		if ( ! $this->active || ! is_string( $url ) ) return $url;
		return self::webp_url( $url, (int) $attachment_id );
	}

	/** @param array|false $image */
	public function filter_image_src( $image, $attachment_id ) {
		if ( ! $this->active || ! is_array( $image ) || empty( $image[0] ) ) return $image;
		$image[0] = self::webp_url( (string) $image[0], (int) $attachment_id );
		return $image;
	}

	/**
	 * Point every size of the metadata at its WebP file, so core both matches
	 * the (already swapped) src and builds the srcset from WebP candidates.
	 */
	public function filter_srcset_meta( $image_meta, $size_array, $image_src, $attachment_id ) {
		if ( ! $this->active || ! is_array( $image_meta ) ) return $image_meta;
		return self::webp_meta( $image_meta );
	}

	/** Rewrite file names in attachment metadata to their WebP siblings. */
	public static function webp_meta( array $meta ): array {
		$full_webp = $meta['sources'][ self::MIME ]['file'] ?? '';
		if ( $full_webp && ! empty( $meta['file'] ) ) {
			$dir          = dirname( (string) $meta['file'] );
			$meta['file'] = ( '.' === $dir ? '' : $dir . '/' ) . $full_webp;
		}

		if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $name => $size ) {
				$webp = $size['sources'][ self::MIME ]['file'] ?? '';
				if ( $webp ) {
					$meta['sizes'][ $name ]['file']      = $webp;
					$meta['sizes'][ $name ]['mime-type'] = self::MIME;
				}
			}
		}

		return $meta;
	}

	/** Return the WebP URL for an attachment file URL, or the URL unchanged. */
	public static function webp_url( string $url, int $attachment_id ): string {
		if ( $attachment_id <= 0 || preg_match( '/\.webp(\?|$)/i', $url ) ) return $url;

		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( ! is_array( $meta ) ) return $url;

		$webp = self::webp_file_for( wp_basename( (string) strtok( $url, '?' ) ), $meta );
		if ( '' === $webp ) return $url;

		$basename = wp_basename( (string) strtok( $url, '?' ) );
		$pos      = strrpos( $url, $basename );
		return false === $pos ? $url : substr_replace( $url, $webp, $pos, strlen( $basename ) );
	}

	/** Find the WebP sibling of a file name among the metadata's sizes. */
	public static function webp_file_for( string $basename, array $meta ): string {
		if ( '' === $basename ) return '';

		if ( ! empty( $meta['file'] ) && wp_basename( (string) $meta['file'] ) === $basename ) {
			return (string) ( $meta['sources'][ self::MIME ]['file'] ?? '' );
		}

		foreach ( (array) ( $meta['sizes'] ?? [] ) as $size ) {
			if ( ( $size['file'] ?? '' ) === $basename ) {
				return (string) ( $size['sources'][ self::MIME ]['file'] ?? '' );
			}
		}

		return '';
	}
}
