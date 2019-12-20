<?php


namespace WEEEOpen\Crauto;


class Image {
	private $uid;
	private $path;
	//const default = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR . 'default.png';

	public function __construct(string $uid, ?string $id) {
		$this->uid = str_replace('/', '_', $uid);
		$this->path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR . "$uid.jpeg";

		if($id !== null && substr($id, 0, 1) === 's' && !$this->fileExists()) {
			$this->tryDownload($id);
		}
	}

	public function getUrl(bool $self = false): string {
		if($self) {
			return '/image.php';
		} else {
			return '/image.php?uid=' . rawurlencode($this->uid);
		}
	}

	public function getPath(): string {
		return $this->path;
//		if($this->fileExists()) {
//			return $this->path;
//		} else {
//			return self::default;
//		}
	}

	private function fileExists(): bool {
		return is_file($this->path);
	}

	public function exists(): bool {
		return $this->fileExists();
	}

	public function bumpAccessTime() {
		if($this->fileExists()) {
			touch($this->path, filemtime($this->path), time());
		}
	}

	protected function tryDownload(string $id): bool {
		$url = sprintf(CRAUTO_IMAGE_URL, rawurlencode(substr($id, 1)));
		$fp = fopen($this->path, 'w');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Wget/1.9.1');
		curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		fclose($fp);
		if($status === 200) {
			return true;
		} else {
			unlink($this->path);
			return false;
		}
		return false;
	}
}
