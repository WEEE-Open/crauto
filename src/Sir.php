<?php

namespace WEEEOpen\Crauto;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfReader\PdfReaderException;

class Sir
{
	protected string $directory;

	public function __construct(string $directory)
	{
		if (str_ends_with($directory, '/')) {
			$directory = substr($directory, 0, strlen($directory) - 1);
		}
		$this->directory = $directory;
		$this->ensureDirectory($this->directory);
		$this->ensureDirectory($this->directory . '/output');
	}

	/** @noinspection PhpSameParameterValueInspection */
	private function definationFilePath(string $filename, string $ext): string
	{
		return "$this->directory/output/$filename.$ext";
	}

	public function getSir(string $targetUid, array $replacements): string
	{
		if (str_starts_with(strtolower($replacements['[CDL]']), 'dottorato')) {
			$template = $this->directory . '/template-dottorandi.csv';
		} else {
			$template = $this->directory . '/template-studenti.csv';
		}
		$filename = "sir-$targetUid-" . sha1(var_export($replacements, 1));

		$pdf = $this->definationFilePath($filename, 'pdf');
		if (is_file($pdf)) {
			return $pdf;
		}

		$this->generateSir($pdf, $template, $replacements);

		return $pdf;
	}

	private function generateSir(string $filename, string $template, array $replacements): void
	{
		$parsed = $this->readCsvTemplate($template);
		$keys = array_keys($replacements);
		$values = array_values($replacements);
		foreach ($parsed as &$lineRef) {
			$lineRef[0] = intval($lineRef[0]); // page
			$lineRef[1] = intval($lineRef[1]); // x
			$lineRef[2] = intval($lineRef[2]); // y
			$lineRef[3] = intval($lineRef[3]); // font size
			$lineRef[4] = str_replace($keys, $values, $lineRef[4]); // text
		}
		$fmod = $this->directory . '/F-MOD-LABORATORI.pdf';
		$input = fopen($fmod, "r");
		if ($input === false) {
			throw new SirException("Cannot open template file $fmod");
		}
		try {
			$pdf = new Fpdi();
			$pdf->setSourceFile($input);

			// There's no way to get the page count from the template file, apparently
			for ($page = 1; $page <= 3; $page++) {
				$tmplPage = $pdf->importPage($page);

				$pdf->AddPage();
				$pdf->useTemplate($tmplPage, 0, 0, null, null, true);

				foreach ($parsed as $line) {
					if ($line[0] === $page) {
						$pdf->SetFont('Courier', '', $line[3]);
						$pdf->SetXY($line[1], $line[2]);
						$pdf->Write(8, $line[4]);
					}
				}
			}

			$pdf->Output("F", $filename);
		} catch (PdfParserException $e) {
			throw new SirException("Cannot parse $fmod: " . $e->getMessage());
		} catch (PdfReaderException $e) {
			throw new SirException("Cannot read $fmod: " . $e->getMessage());
		} finally {
			fclose($input);
		}
	}

	private function ensureDirectory(string $directory): void
	{
		if (!is_dir($directory)) {
			if (!mkdir($directory, 0750, true)) {
				throw new SirException("$directory is not a directory and cannot be created");
			}
		}
		if (!is_writable($directory)) {
			throw new SirException("$directory is not writable");
		}
	}

//	public static function politoMail(string $matricola): string {
//		$first = strtolower(substr($matricola, 0, 1));
//		if($first === 'd') {
//			return "$matricola@polito.it";
//		} else {
//			return "$matricola@studenti.polito.it";
//		}
//	}

	protected function readCsvTemplate(string $template): array
	{
		$result = [];

		$row = 0;
		$handle = fopen($template, "r");
		if ($handle === false) {
			throw new SirException("Cannot open template $template");
		}
		try {
			while (($data = fgetcsv($handle)) !== false) {
				$row++;
				$x = count($data);
				if ($x != 5) {
					throw new SirException("Error reading template $template at row $row, expected 4 fields but found " . $x);
				}
				$result[] = $data;
			}
		} finally {
			fclose($handle);
		}

		return $result;
	}
}
