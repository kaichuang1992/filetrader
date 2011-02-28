<?php
class osapiConextProvider extends osapiProvider {
	public function __construct(osapiHttpProvider $httpProvider = null) {
		parent::__construct('https://os.surfconext.nl/oauth/request_token','https://os.surfconext.nl/oauth/authorize','https://os.surfconext.nl/oauth/access_token','https://os.surfconext.nl/social/rest','https://os.surfconext.nl/social/rpc','SURFconext', TRUE, $httpProvider);
	}

	/**
	 * Set's the signer's useBodyHash to true
	 * @param mixed $request The osapiRequest object being processed, or an array
	 *     of osapiRequest objects.
	 * @param string $method The HTTP method used for this request.
	 * @param string $url The url being fetched for this request.
	 * @param array $headers The headers being sent in this request.
	 * @param osapiAuth $signer The signing mechanism used for this request.
	 */
	public function preRequestProcess(&$request, &$method, &$url, &$headers, osapiAuth &$signer) {
		if (method_exists($signer, 'setUseBodyHash')) {
			$signer->setUseBodyHash(true);
		}
	}
}
?>
