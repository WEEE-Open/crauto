<?php


namespace WEEEOpen\Crauto;


class Sir {
	protected $directory;

	public function __construct(string $directory) {
		if(substr($directory, -1, 1) !== '/' && substr($directory, -1, 1) !== DIRECTORY_SEPARATOR) {
			$directory = $directory . '/';
		}
		$this->directory = $directory;
		$this->ensureDirectory($this->directory);
	}

	private function filePath(string $filename, string $ext) {
		return "$this->directory$filename.$ext";
	}

	public function getSir(string $filename, string $template, array $replacements): string {
		$pdf = $this->filePath($filename, 'pdf');
		if(is_file($pdf)) {
			return $pdf;
		}

		$tex = $this->filePath($filename, 'tex');
		$theTex = $this->generateSirTex($template, $replacements);
		file_put_contents($tex, $theTex);
		$this->compileSir($filename);
		return $pdf;
	}

	private function generateSirTex(string $template, array $replacements): string {
		foreach($replacements as $search => $replace) {
			if(strpos($template, $search) === false) {
				throw new SirException("$search not found in template");
			}
		}

		return str_replace(array_keys($replacements), array_values($replacements), $template);
	}

	private function compileSir($filename) {
		$tex = $this->filePath($filename, 'tex');
		$command = 'pdflatex -interaction=nonstopmode -output-directory='  . escapeshellarg($this->directory) . ' ' . escapeshellarg($tex);
		system($command, $ret);
		if($ret !== 0) {
			throw new SirException("pdflatex failed, exit status $ret");
		}

		// Remove temporary files
		unlink($this->filePath($filename, 'aux'));
		unlink($this->filePath($filename, 'log'));
	}

	private function ensureDirectory(string $directory) {
		if(!is_dir($directory)) {
			if(!mkdir($directory, 0750, true)) {
				throw new SirException("$directory is not a directory and cannot be created");
			}
		}
		if(!is_writable($directory)) {
			throw new SirException("$directory is not writable");
		}
	}

	public function cleanupDirectory(int $days) {
		$seconds = $days * 24 * 60 * 60;
		$limit = time() - $seconds;
		$files = array_diff(scandir($this->directory), ['.', '..']);
		foreach($files as $file) {
			if(filemtime($file) < $limit) {
				unlink($file);
			}
		}
	}

	public function getDirectory(): string {
		return $this->directory;
	}
}
