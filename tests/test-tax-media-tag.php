<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Tax_Media_Tag extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_init_hooks_register_on_init_action(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'init', \Mockery::type( 'array' ) );

		( new Calypsosub_Tax_Media_Tag() )->init();
	}

	public function test_register_registers_taxonomy_on_attachment(): void {
		Functions\when( '__' )->returnArg( 1 );
		Functions\expect( 'register_taxonomy' )
			->once()
			->withArgs( function ( $taxonomy, $object_type, $args ) {
				return $taxonomy === 'calypso_media_tag'
					&& $object_type === 'attachment'
					&& $args['hierarchical'] === false
					&& $args['show_ui'] === true
					&& $args['show_in_rest'] === true
					&& $args['rest_base'] === 'calypso_media_tag';
			} );

		( new Calypsosub_Tax_Media_Tag() )->register();
	}
}
