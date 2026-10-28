<?php

namespace WEEEOpen\Crauto;

use Jumbojett\OpenIDConnectClient;

class OpenIDConnectRefreshClient extends OpenIDConnectClient
{
	protected $nonce;

	public function setNonceForRefresh($nonce)
	{
		$this->setNonce($nonce);
	}

	protected function setNonce(string $nonce): string
	{
		$this->nonce = $nonce;
		return $nonce;
	}

	protected function getNonce()
	{
		return $this->nonce;
	}

	protected function startSession()
	{
		error_log('startSession called, override mode methods to prevent this');
	}
}
