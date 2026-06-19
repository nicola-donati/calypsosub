<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Media_Overlay_Field extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_init_hooks_both_filters(): void {
		Functions\expect( 'add_filter' )
			->once()
			->with( 'attachment_fields_to_edit', Mockery::on( function ( $callback ) {
				return is_array( $callback )
					&& $callback[0] instanceof Calypsosub_Media_Overlay_Field
					&& $callback[1] === 'add_field';
			} ), 10, 2 );
		Functions\expect( 'add_filter' )
			->once()
			->with( 'attachment_fields_to_save', Mockery::on( function ( $callback ) {
				return is_array( $callback )
					&& $callback[0] instanceof Calypsosub_Media_Overlay_Field
					&& $callback[1] === 'save_field';
			} ), 10, 2 );

		( new Calypsosub_Media_Overlay_Field() )->init();
	}

	public function test_add_field_adds_text_input_for_images(): void {
		Functions\when( '__' )->returnArg( 1 );
		Functions\expect( 'get_post_meta' )
			->once()
			->with( 42, '_calypso_overlay_text', true )
			->andReturn( 'tartaruga' );

		$post = (object) [ 'ID' => 42, 'post_mime_type' => 'image/jpeg' ];
		$result = ( new Calypsosub_Media_Overlay_Field() )->add_field( [], $post );

		$this->assertSame( 'tartaruga', $result['calypso_overlay_text']['value'] );
		$this->assertSame( 'text', $result['calypso_overlay_text']['input'] );
	}

	public function test_add_field_skips_non_image_attachments(): void {
		$post = (object) [ 'ID' => 42, 'post_mime_type' => 'application/pdf' ];
		$result = ( new Calypsosub_Media_Overlay_Field() )->add_field( [ 'existing' => [] ], $post );

		$this->assertSame( [ 'existing' => [] ], $result );
	}

	public function test_save_field_persists_value(): void {
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\expect( 'update_post_meta' )
			->once()
			->with( 42, '_calypso_overlay_text', 'tartaruga' );

		$post = [ 'ID' => 42 ];
		$attachment = [ 'calypso_overlay_text' => 'tartaruga' ];
		$result = ( new Calypsosub_Media_Overlay_Field() )->save_field( $post, $attachment );

		$this->assertSame( $post, $result );
	}

	public function test_save_field_skips_when_field_absent(): void {
		Functions\expect( 'update_post_meta' )->never();

		$post = [ 'ID' => 42 ];
		$result = ( new Calypsosub_Media_Overlay_Field() )->save_field( $post, [] );

		$this->assertSame( $post, $result );
	}
}
