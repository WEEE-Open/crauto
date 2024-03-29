<?php


namespace WEEEOpen\Crauto;


class Sir {
	protected $directory;

	static $replace = [
		"#"  => "\\#",
		"$"  => "\\$",
		"%"  => "\\%",
		"&"  => "\\&",
		"~"  => "\\~{}",
		"_"  => "\\_",
		"^"  => "\\^{}",
		"\\" => "\\textbackslash{}",
		"{"  => "\\{",
		"}"  => "\\}",
	];

	public function __construct(string $directory) {
		if(substr($directory, -1, 1) !== '/' && substr($directory, -1, 1) !== DIRECTORY_SEPARATOR) {
			$directory = $directory . '/';
		}
		$this->directory = $directory;
		$this->ensureDirectory($this->directory);
	}

	private function filePath(string $filename, string $ext): string {
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
		$cwd = getcwd();
		chdir($this->directory);
		// For some reason, "pdflatex -v" works, "ls" works, and "pdflatex -interaction=..." does not. You *NEED* the
		// full path. Or else the pdflatex process dies with SIGABRT without even starting. For no discernible reason.
		$command = '/usr/bin/pdflatex -interaction=nonstopmode -output-directory=' . escapeshellarg($this->directory) . ' ' . escapeshellarg($tex);
		exec($command, $output, $ret);
		chdir($cwd);
		if($ret !== 0) {
			$output = implode("\n", $output);
			throw new SirException("pdflatex failed, exit status $ret\nHere's the output:\n\n$output");
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

	public static function politoMail(string $matricola): string {
		$first = strtolower(substr($matricola, 0, 1));
		if($first === 'd') {
			return "$matricola@polito.it";
		} else {
			return "$matricola@studenti.polito.it";
		}
	}

	public static function escapeString(string $s): string {
		return str_replace(array_keys(self::$replace), array_values(self::$replace), $s);
	}
}
