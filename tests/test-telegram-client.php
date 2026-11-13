<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Telegram_Client extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_links_become_text_link_entities(): void {
		$client = new Calypsosub_Telegram_Client();
		$html   = "<p>Ciao 🌊 a tutti,</p>\n<p>iscrivetevi <a href=\"https://calypsosub.it/?a=1&amp;b=2\">qui sul <strong>sito</strong></a> &amp; poi</p>";

		$message = $client->html_to_message( $html );

		$this->assertSame( "Ciao 🌊 a tutti,\niscrivetevi qui sul sito & poi", $message['text'] );
		// 🌊 counts as 2 UTF-16 units: "Ciao 🌊 a tutti,\niscrivetevi " = 29.
		$this->assertSame(
			[ [ 'type' => 'text_link', 'offset' => 29, 'length' => 12, 'url' => 'https://calypsosub.it/?a=1&b=2' ] ],
			$message['entities']
		);
	}

	public function test_non_http_links_keep_only_their_text(): void {
		$client  = new Calypsosub_Telegram_Client();
		$message = $client->html_to_message( '<p>scrivete a <a href="mailto:x@y.it">x@y.it</a></p>' );

		$this->assertSame( 'scrivete a x@y.it', $message['text'] );
		$this->assertSame( [], $message['entities'] );
	}

	public function test_shift_entities_clips_to_limit(): void {
		$entities = [
			[ 'type' => 'text_link', 'offset' => 0, 'length' => 5, 'url' => 'https://a.it' ],
			[ 'type' => 'text_link', 'offset' => 8, 'length' => 5, 'url' => 'https://b.it' ],
		];

		$this->assertSame(
			[ [ 'type' => 'text_link', 'offset' => 3, 'length' => 5, 'url' => 'https://a.it' ] ],
			Calypsosub_Telegram_Client::shift_entities( $entities, 3, 10 )
		);
	}
}
