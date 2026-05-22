<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Booking_Email extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_default_templates_have_four_keys(): void {
		$email     = new Calypsosub_Booking_Email();
		$templates = $email->default_templates();

		$this->assertCount( 4, $templates );
		$this->assertArrayHasKey( 'conferma_prenotazione', $templates );
		$this->assertArrayHasKey( 'prenotazione_annullata', $templates );
		$this->assertArrayHasKey( 'promosso_lista_attesa', $templates );
		$this->assertArrayHasKey( 'notifica_admin_prenotazione', $templates );
	}

	public function test_default_templates_have_subject_and_body(): void {
		$email     = new Calypsosub_Booking_Email();
		$templates = $email->default_templates();

		foreach ( $templates as $key => $tpl ) {
			$this->assertArrayHasKey( 'subject', $tpl, "Template {$key} missing subject" );
			$this->assertArrayHasKey( 'body', $tpl, "Template {$key} missing body" );
			$this->assertNotEmpty( $tpl['subject'], "Template {$key} subject is empty" );
			$this->assertNotEmpty( $tpl['body'], "Template {$key} body is empty" );
		}
	}

	public function test_send_confirmed_calls_wp_mail(): void {
		$booking_id = 1;

		Functions\when( 'get_post_meta' )->alias( function ( $id, $key, $single ) {
			$map = [
				'_booking_user_id'    => 2,
				'_booking_post_id'    => 10,
				'_booking_companions' => 1,
				'_booking_allergies'  => 'nessuna',
				'_booking_status'     => 'confermata',
				'_booking_date'       => '2026-05-22 10:00:00',
			];
			return $map[ $key ] ?? '';
		} );

		$user_obj              = new stdClass();
		$user_obj->display_name = 'Mario Rossi';
		$user_obj->user_email   = 'mario@example.com';
		Functions\when( 'get_user_by' )->justReturn( $user_obj );
		Functions\when( 'get_post_type' )->justReturn( 'calypso_uscita' );
		Functions\when( 'get_the_title' )->justReturn( 'Uscita Capri' );
		Functions\when( 'get_option' )->justReturn( 'd/m/Y' );
		Functions\when( 'date_i18n' )->justReturn( '22/05/2026' );
		Functions\when( 'get_permalink' )->justReturn( 'http://localhost/area-personale' );
		Functions\when( 'home_url' )->justReturn( 'http://localhost' );

		Functions\expect( 'wp_mail' )
			->once()
			->with(
				'mario@example.com',
				\Mockery::type( 'string' ),
				\Mockery::type( 'string' ),
				\Mockery::type( 'array' )
			);

		Functions\when( 'get_option' )->alias( function ( $key, $default = '' ) {
			if ( $key === 'calypsosub_account_page_id' ) return 0;
			if ( $key === 'calypsosub_email_subject_conferma_prenotazione' ) return false;
			if ( $key === 'calypsosub_email_body_conferma_prenotazione' ) return false;
			return 'd/m/Y';
		} );
		Functions\when( 'home_url' )->justReturn( 'http://localhost' );

		$email = new Calypsosub_Booking_Email();
		$email->send_booking_confirmed( $booking_id );
	}
}
