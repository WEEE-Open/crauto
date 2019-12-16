<?php


namespace WEEEOpen\Crauto;


class Image {
	private $uid;
	private $path;

	public function __construct(string $uid) {
		$this->uid = str_replace('/', '_', $uid);
		$this->path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR . "$uid.jpeg";
	}

	function getPath(): string {
		return $this->path;
	}

	public function exists(): bool {
		return is_file($this->path);
	}

	public function tryDownload() {
		// TODO: download if possible, cache its non-existence if it can't be downloaded
	}
}
