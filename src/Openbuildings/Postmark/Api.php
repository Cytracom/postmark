<?php

namespace Openbuildings\Postmark;

/**
 * Class for manupulating a server
 *
 * @package    openbuildings\postmark
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2013 OpenBuildings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Api {

	const SEND_URI = 'http://api.postmarkapp.com/email';

	protected $_token;

	public function token($token = NULL)
	{
		if ($token !== NULL)
		{
			$this->_token = $token;
			return $this;
		}
		return $this->_token;
	}

	public function __construct($token = NULL)
	{
		if ($token !== NULL)
		{
			$this->token($token);
		}
	}

	public function headers()
	{
		if ( ! $this->token())
			throw new \Exception('You must set postmark token');

		return array(
			'Accept: application/json',
			'Content-Type: application/json',
			'X-Postmark-Server-Token: '.$this->token(),
		);
	}

	public function send(array $data)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => self::SEND_URI,
			CURLOPT_POST => TRUE,
			CURLOPT_HTTPHEADER => $this->headers(),
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_RETURNTRANSFER => TRUE
		));

		$response = curl_exec($curl);

		if ( ! $response)
			throw new \Exception('Postmark delivery failed: '.curl_error($curl));

		$response = @ json_decode($response, TRUE);

		if ( ! $response)
			throw new \Exception('Postmark delivery failed: wrong json response');

		$response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($response_code != 200)
			throw new \Exception('Postmark delivery failed: '.$response['Message']);

		return $response;
	}
}