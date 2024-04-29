<?php


use PHPUnit\Framework\TestCase;
use WEEEOpen\Crauto\Sir;

const TEST_DIR = __DIR__ . '/pdftemplates-test';

class SirTest extends TestCase {
	private static function delete_file(?string $file): void {
		if(isset($file) && is_file($file)) {
			// Comment out if you want to check the output files
			unlink($file);
		}
	}
	
	protected function setUp(): void {
		$files = scandir(TEST_DIR . '/output');
		foreach($files as $file) {
			if(str_starts_with($file, 'sir-') && str_ends_with($file, '.pdf')) {
				unlink(TEST_DIR . '/output/' . $file);
			}
		}
	}

	public function testGenerateSir() {
		$pdfFilePath = null;
		register_shutdown_function(function() use (&$pdfFilePath) { self::delete_file($pdfFilePath); });

		$replacements = [
			'[NAME]'     => 'John',
			'[SURNAME]'  => 'Doe',
			'[ID]'       => 's123456',
			'[TESTDATE]' => '2099-01-01',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Ingegneria dell\'ingegnerizzazione',
		];

		$sir = new Sir(TEST_DIR);
		$pdfFilePath = $sir->getSir('john.doe', $replacements);
		self::assertStringEndsWith('.pdf', $pdfFilePath, 'The output file is a pdf');
		self::assertFileExists($pdfFilePath, 'Generated PDF file exists');
	}

	public function testGenerateSirPhD() {
		$pdfFilePath = null;
		register_shutdown_function(function() use (&$pdfFilePath) { self::delete_file($pdfFilePath); });

		$replacements = [
			'[NAME]'     => 'Andrea',
			'[SURNAME]'  => 'Andrea',
			'[ID]'       => 'd12345',
			'[TESTDATE]' => '2099-01-01',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Dottorato in dottoramento',
		];

		$sir = new Sir(TEST_DIR);
		$pdfFilePath = $sir->getSir('andrea.andrea', $replacements);
		self::assertStringEndsWith('.pdf', $pdfFilePath, 'The output file is a pdf');
		self::assertFileExists($pdfFilePath, 'Generated PDF file exists');
	}

	public function testGenerateSir2() {
		$pdfFilePath = null;
		$pdfFilePath2 = null;
		register_shutdown_function(function() use (&$pdfFilePath, &$pdfFilePath2) { self::delete_file($pdfFilePath); self::delete_file($pdfFilePath2); });

		$replacements = [
			'[NAME]'     => 'John',
			'[SURNAME]'  => 'Doe',
			'[ID]'       => 's123456',
			'[TESTDATE]' => '2099-01-01',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Ingegneria dell\'ingegnerizzazione',
		];
		$sir = new Sir(TEST_DIR);
		$pdfFilePath = $sir->getSir('john.doe', $replacements);

		$replacements2 = [
			'[NAME]'     => 'Jane',
			'[SURNAME]'  => 'Smith',
			'[ID]'       => 's456789',
			'[TESTDATE]' => '2099-01-01',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Ingegneria dell\'ingegnerizzazione',
		];
		$sir = new Sir(TEST_DIR);
		$pdfFilePath2 = $sir->getSir('jane.smith', $replacements2);

		self::assertFileExists($pdfFilePath, 'Generated PDF file exists');
		self::assertFileExists($pdfFilePath2, 'Generated PDF file exists');
		self::assertFileNotEquals($pdfFilePath, $pdfFilePath2, 'Files are different');
	}

	public function testGenerateSirOnce() {
		$pdfFilePath = null;
		$pdfFilePath2 = null;
		register_shutdown_function(function() use (&$pdfFilePath, &$pdfFilePath2) { self::delete_file($pdfFilePath); self::delete_file($pdfFilePath2); });

		$replacements = [
			'[NAME]'     => 'John',
			'[SURNAME]'  => 'Doe',
			'[ID]'       => 's123456',
			'[TESTDATE]' => '2099-01-01',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Ingegneria dell\'ingegnerizzazione',
		];
		$sir = new Sir(TEST_DIR);
		$pdfFilePath = $sir->getSir('john.doe', $replacements);

		self::assertFileExists($pdfFilePath, 'Generated PDF file exists');

		$sir = new Sir(TEST_DIR);
		$pdfFilePath2 = $sir->getSir('john.doe', $replacements);

		self::assertEquals($pdfFilePath, $pdfFilePath2, 'The file is the same');
	}

	public function testGenerateSirWithUpdate() {
		$pdfFilePath = null;
		$pdfFilePath2 = null;
		register_shutdown_function(function() use (&$pdfFilePath, &$pdfFilePath2) { self::delete_file($pdfFilePath); self::delete_file($pdfFilePath2); });

		$replacements = [
			'[NAME]'     => 'John',
			'[SURNAME]'  => 'Doe',
			'[ID]'       => 's123456',
			'[TESTDATE]' => '2099-01-01',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Ingegneria dell\'ingegnerizzazione',
		];
		$sir = new Sir(TEST_DIR);
		$pdfFilePath = $sir->getSir('john.doe', $replacements);

		self::assertFileExists($pdfFilePath, 'Generated PDF file exists');

		$replacements = [
			'[NAME]'     => 'John',
			'[SURNAME]'  => 'Doe',
			'[ID]'       => 's123456',
			'[TESTDATE]' => '2099-02-02',
			'[TODAYDATE]'=> '2099-02-02',
			'[CDL]'      => 'Ingegneria del disagio',
		];
		$sir = new Sir(TEST_DIR);
		$pdfFilePath2 = $sir->getSir('john.doe', $replacements);

		self::assertFileExists($pdfFilePath2, 'Generated PDF file exists');
		self::assertNotEquals($pdfFilePath, $pdfFilePath2, 'Different files are generated');
	}
}