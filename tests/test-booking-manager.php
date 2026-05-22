<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Booking_Manager extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	// -------------------------------------------------------------------------

	public function test_book_invalid_post_type(): void {
		Functions\when( 'get_post_type' )->justReturn( 'post' );

		$email   = $this->createMock( Calypsosub_Booking_Email::class );
		$manager = new Calypsosub_Booking_Manager( $email );
		$result  = $manager->book( 99, 1, [] );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'invalid_post', $result->get_error_code() );
	}

	public function test_book_already_booked(): void {
		Functions\when( 'get_post_type' )->justReturn( 'calypso_uscita' );

		$email   = $this->createMock( Calypsosub_Booking_Email::class );
		$manager = $this->getMockBuilder( Calypsosub_Booking_Manager::class )
		                ->setConstructorArgs( [ $email ] )
		                ->onlyMethods( [ 'user_has_booking' ] )
		                ->getMock();
		$manager->method( 'user_has_booking' )->willReturn( true );

		$result = $manager->book( 1, 1, [] );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'already_booked', $result->get_error_code() );
	}

	public function test_book_no_limit_returns_confermata(): void {
		Functions\when( 'get_post_type' )->justReturn( 'calypso_uscita' );
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\when( 'wp_insert_post' )->justReturn( 10 );
		Functions\when( 'update_post_meta' )->justReturn( true );
		Functions\when( 'current_time' )->justReturn( '2026-05-22 10:00:00' );

		$email = $this->createMock( Calypsosub_Booking_Email::class );
		$email->expects( $this->once() )->method( 'send_booking_confirmed' );
		$email->expects( $this->once() )->method( 'send_admin_notification' );

		$manager = $this->getMockBuilder( Calypsosub_Booking_Manager::class )
		                ->setConstructorArgs( [ $email ] )
		                ->onlyMethods( [ 'user_has_booking' ] )
		                ->getMock();
		$manager->method( 'user_has_booking' )->willReturn( false );

		$result = $manager->book( 1, 1, [ 'accompagnatori' => 0, 'allergie' => '' ] );

		$this->assertSame( 'confermata', $result );
	}

	public function test_book_full_no_waitlist_returns_error(): void {
		Functions\when( 'get_post_type' )->justReturn( 'calypso_evento' );

		$email   = $this->createMock( Calypsosub_Booking_Email::class );
		$manager = $this->getMockBuilder( Calypsosub_Booking_Manager::class )
		                ->setConstructorArgs( [ $email ] )
		                ->onlyMethods( [ 'user_has_booking', 'count_confirmed' ] )
		                ->getMock();
		$manager->method( 'user_has_booking' )->willReturn( false );
		$manager->method( 'count_confirmed' )->willReturn( 10 );

		Functions\expect( 'get_post_meta' )
			->once()
			->with( 1, '_evento_max_partecipanti', true )
			->andReturn( '10' );
		Functions\expect( 'get_post_meta' )
			->once()
			->with( 1, '_evento_lista_attesa', true )
			->andReturn( 0 );

		$result = $manager->book( 1, 1, [] );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'full', $result->get_error_code() );
	}

	public function test_book_full_with_waitlist_returns_lista_attesa(): void {
		Functions\when( 'get_post_type' )->justReturn( 'calypso_uscita' );
		Functions\when( 'wp_insert_post' )->justReturn( 10 );
		Functions\when( 'update_post_meta' )->justReturn( true );
		Functions\when( 'current_time' )->justReturn( '2026-05-22 10:00:00' );

		$email   = $this->createMock( Calypsosub_Booking_Email::class );
		$email->expects( $this->once() )->method( 'send_booking_confirmed' );
		$manager = $this->getMockBuilder( Calypsosub_Booking_Manager::class )
		                ->setConstructorArgs( [ $email ] )
		                ->onlyMethods( [ 'user_has_booking', 'count_confirmed' ] )
		                ->getMock();
		$manager->method( 'user_has_booking' )->willReturn( false );
		$manager->method( 'count_confirmed' )->willReturn( 5 );

		Functions\expect( 'get_post_meta' )
			->with( 1, '_uscita_max_partecipanti', true )
			->andReturn( '5' );
		Functions\expect( 'get_post_meta' )
			->with( 1, '_uscita_lista_attesa', true )
			->andReturn( 1 );

		$result = $manager->book( 1, 1, [] );

		$this->assertSame( 'lista_attesa', $result );
	}

	public function test_cancel_forbidden_for_other_user(): void {
		Functions\when( 'get_post_meta' )->justReturn( 99 );
		Functions\when( 'current_user_can' )->justReturn( false );

		$email   = $this->createMock( Calypsosub_Booking_Email::class );
		$manager = new Calypsosub_Booking_Manager( $email );
		$result  = $manager->cancel_booking( 5, 1 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'forbidden', $result->get_error_code() );
	}

	public function test_cancel_already_cancelled(): void {
		Functions\when( 'get_post_meta' )->alias( function ( $id, $key, $single ) {
			if ( $key === '_booking_user_id' ) return 1;
			if ( $key === '_booking_status' )  return 'annullata';
			return '';
		} );
		Functions\when( 'current_user_can' )->justReturn( false );

		$email   = $this->createMock( Calypsosub_Booking_Email::class );
		$manager = new Calypsosub_Booking_Manager( $email );
		$result  = $manager->cancel_booking( 5, 1 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'already_cancelled', $result->get_error_code() );
	}
}
