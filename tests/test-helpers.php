<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Helpers extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_calypso_is_user_logged_in_uses_wp(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( true );
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$this->assertTrue( calypso_is_user_logged_in() );
	}

	public function test_calypso_is_user_logged_in_false(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$this->assertFalse( calypso_is_user_logged_in() );
	}

	public function test_calypso_can_book_no_manager_returns_false(): void {
		$GLOBALS['calypsosub_booking_manager'] = null;
		$this->assertFalse( calypso_can_book( 1, 1 ) );
	}

	public function test_calypso_get_user_bookings_no_manager_returns_empty(): void {
		$GLOBALS['calypsosub_booking_manager'] = null;
		$this->assertSame( [], calypso_get_user_bookings( 1 ) );
	}
}
