<?php


namespace WEEEOpen\Crauto;


class Image {
	private $uid;
	private $path;
	//const default = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR . 'default.png';

	public function __construct(string $uid, ?string $id) {
		return;
	}

	public function exists(): bool {
		return false;
	}
}
