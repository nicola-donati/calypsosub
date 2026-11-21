<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Webp_Urls extends TestCase {

	private array $meta;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'wp_basename' )->alias( fn( $p ) => basename( (string) $p ) );
		$this->meta = [
			'file'    => '2026/05/hero-scaled.jpg',
			'sources' => [ 'image/webp' => [ 'file' => 'hero-scaled-jpg.webp' ] ],
			'sizes'   => [
				'large'  => [ 'file' => 'hero-1024x768.jpg', 'mime-type' => 'image/jpeg', 'sources' => [ 'image/webp' => [ 'file' => 'hero-1024x768-jpg.webp' ] ] ],
				'medium' => [ 'file' => 'hero-300x225.jpg', 'mime-type' => 'image/jpeg', 'sources' => [ 'image/jpeg' => [ 'file' => 'hero-300x225.jpg' ] ] ],
			],
		];
		Functions\when( 'wp_get_attachment_metadata' )->justReturn( $this->meta );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_full_url_becomes_webp(): void {
		$this->assertSame(
			'https://x.it/wp-content/uploads/2026/05/hero-scaled-jpg.webp',
			Calypsosub_Webp_Urls::webp_url( 'https://x.it/wp-content/uploads/2026/05/hero-scaled.jpg', 5 )
		);
	}

	public function test_size_url_becomes_webp(): void {
		$this->assertSame(
			'https://x.it/wp-content/uploads/2026/05/hero-1024x768-jpg.webp',
			Calypsosub_Webp_Urls::webp_url( 'https://x.it/wp-content/uploads/2026/05/hero-1024x768.jpg', 5 )
		);
	}

	public function test_size_without_webp_keeps_jpeg(): void {
		$url = 'https://x.it/wp-content/uploads/2026/05/hero-300x225.jpg';
		$this->assertSame( $url, Calypsosub_Webp_Urls::webp_url( $url, 5 ) );
	}

	public function test_unknown_file_and_webp_are_untouched(): void {
		$this->assertSame( 'https://x.it/a.jpg', Calypsosub_Webp_Urls::webp_url( 'https://x.it/a.jpg', 5 ) );
		$this->assertSame( 'https://x.it/b.webp', Calypsosub_Webp_Urls::webp_url( 'https://x.it/b.webp', 5 ) );
	}

	public function test_meta_points_sizes_at_webp_files(): void {
		$meta = Calypsosub_Webp_Urls::webp_meta( $this->meta );
		$this->assertSame( '2026/05/hero-scaled-jpg.webp', $meta['file'] );
		$this->assertSame( 'hero-1024x768-jpg.webp', $meta['sizes']['large']['file'] );
		$this->assertSame( 'image/webp', $meta['sizes']['large']['mime-type'] );
		$this->assertSame( 'hero-300x225.jpg', $meta['sizes']['medium']['file'] );
	}
}
