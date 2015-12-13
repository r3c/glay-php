<?php

namespace Glay\Network;

class HTTP
{
	const REDIRECT_PERMANENT	= 301;
	const REDIRECT_FOUND		= 302;
	const REDIRECT_PROXY		= 305;
	const REDIRECT_TEMPORARY	= 307;

	public static $default_follow = false;
	public static $default_headers = array ();
	public static $default_proxy = null;
	public static $default_redirect = null;
	public static $default_timeout = null;
	public static $default_useragent = null;

	public static function code ($status, $contents = null)
	{
		return new HTTPMessage ($status, null, $contents);
	}

	public static function ok ($contents)
	{
		return new HTTPMessage (200, null, $contents);
	}

	public static function to ($url, $status = self::REDIRECT_FOUND)
	{
		return new HTTPMessage ($status, array ('Location' => (string)URI::base ()->combine ($url)), null);
	}

	public function __construct ()
	{
		$this->follow = self::$default_follow;
		$this->headers = self::$default_headers;
		$this->proxy = self::$default_proxy;
		$this->redirect = self::$default_redirect;
		$this->timeout = self::$default_timeout;
		$this->useragent = self::$default_useragent;
	}

	public function header ($name, $value = null)
	{
		$this->headers[strtolower ($name)] = $name . ($value !== null ? ': ' . $value : '');
	}

	public function send ($method, $url, $data = null)
	{
		$handle = curl_init ();
		$method = strtoupper ($method);

		if (count ($this->headers) > 0)
			curl_setopt ($handle, CURLOPT_HTTPHEADER, array_values ($this->headers));

		if ($this->redirect !== null)
			curl_setopt ($handle, CURLOPT_MAXREDIRS, $this->redirect);

		if ($this->proxy !== null)
			curl_setopt ($handle, CURLOPT_PROXY, $this->proxy);

		if ($this->timeout !== null)
			curl_setopt ($handle, CURLOPT_TIMEOUT_MS, $this->timeout);

		if ($this->useragent !== null)
			curl_setopt ($handle, CURLOPT_USERAGENT, $this->useragent);

		if ($data !== null)
			curl_setopt ($handle, CURLOPT_POSTFIELDS, $data);

		curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, $this->follow);
		curl_setopt ($handle, CURLOPT_HEADER, true);
		curl_setopt ($handle, CURLOPT_NOBODY, $method === 'HEAD');
		curl_setopt ($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt ($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($handle, CURLOPT_TCP_NODELAY, false);
		curl_setopt ($handle, CURLOPT_URL, $url);

		$output = curl_exec ($handle);
		$status = curl_getinfo ($handle, CURLINFO_HTTP_CODE);

		curl_close ($handle);

		if ($output === false)
			return null;

		$headers = array ();
		$offset = strpos ($output, "\r\n\r\n");

		if ($offset === false)
			$offset = strlen ($output);

		foreach (explode ("\r\n", substr ($output, 0, $offset)) as $header)
		{
			$fragments = explode (':', $header, 2);
			$headers[trim ($fragments[0])] = count ($fragments) > 1 ? trim ($fragments[1]) : '';
		}

		return new HTTPMessage ($status, $headers, (string)substr ($output, $offset + 4));
	}
}

class HTTPMessage
{
	private static $messages = array
	(
		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		410	=> 'Gone',
		500	=> 'Internal Server Error',
		501	=> 'Not Implemented'
	);

	public function __construct ($status, $headers, $contents)
	{
		$this->contents = $contents;
		$this->headers = array_change_key_case ((array)$headers);
		$this->status = (int)$status;
	}

	public function	send ()
	{
		if ($this->status !== 200)
		{
			if (isset (self::$messages[$this->status]))
				header ('HTTP/1.1 ' . $this->status . ' ' . self::$messages[$this->status], true, $this->status);
			else
				header ('HTTP/1.1 ' . $this->status, true, $this->status);
		}

		if ($this->headers !== null)
		{
			foreach ($this->headers as $name => $value)
				header (ucwords ($name) . ': ' . $value);
		}

		if ($this->contents !== null)
			echo $this->contents;
	}
}

?>
