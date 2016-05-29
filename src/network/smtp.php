<?php

namespace Glay\Network;

class SMTP
{
	const HEADER_BCC = 'Bcc';
	const HEADER_CC = 'Cc';
	const HEADER_CONTENT_ENCODING = 'Content-Transfer-Encoding';
	const HEADER_CONTENT_TYPE = 'Content-Type';
	const HEADER_FROM = 'From';
	const HEADER_REPLY_TO = 'Reply-To';

	public static $default_content_encoding = 'quoted-printable';
	public static $default_content_type = 'text/plain; charset=utf-8';
	public static $default_headers = array ();

	public function __construct ()
	{
		$this->content_encoding = self::$default_content_encoding;
		$this->content_type = self::$default_content_type;
		$this->headers = self::$default_headers;
		$this->recipients_bcc = array ();
		$this->recipients_cc = array ();
		$this->recipients_to = array ();
	}

	public function add_bcc ($address, $name = null)
	{
		$this->recipients_bcc[] = self::escape_recipient ($address, $name);
	}

	public function add_cc ($address, $name = null)
	{
		$this->recipients_cc[] = self::escape_recipient ($address, $name);
	}

	public function add_to ($address, $name = null)
	{
		$this->recipients_to[] = self::escape_recipient ($address, $name);
	}

	public function from ($address, $name = null)
	{
		$this->header (self::HEADER_FROM, self::escape_recipient ($address, $name));
	}

	public function header ($name, $value = null)
	{
		$this->headers[strtolower ($name)] = $name . ($value !== null ? ': ' . $value : '') . "\r\n";
	}

	public function reply_to ($address, $name = null)
	{
		$this->header (self::HEADER_REPLY_TO, self::escape_recipient ($address, $name));
	}

	public function send ($subject, $body)
	{
		if (!isset ($this->headers[strtolower (self::HEADER_BCC)]) && count ($this->recipients_bcc) > 0)
			$this->header (self::HEADER_BCC, implode (', ', $this->recipients_bcc));

		if (!isset ($this->headers[strtolower (self::HEADER_CC)]) && count ($this->recipients_cc) > 0)
			$this->header (self::HEADER_CC, implode (', ', $this->recipients_cc));

		if (!isset ($this->headers[strtolower (self::HEADER_CONTENT_ENCODING)]))
			$this->header (self::HEADER_CONTENT_ENCODING, $this->content_encoding);

		if (!isset ($this->headers[strtolower (self::HEADER_CONTENT_TYPE)]))
			$this->header (self::HEADER_CONTENT_TYPE, $this->content_type);

		switch (strtolower ($this->content_encoding))
		{
			case '8bit':
				$encode = function ($s) { return $s; };

				break;

			case 'base64':
				$encode = 'base64_encode';

				break;

			case 'quoted-printable':
				$encode = 'quoted_printable_encode';

				break;

			default:
				throw new \Exception ('unknown content encoding');
		}

		return mail (implode (', ', $this->recipients_to), mb_encode_mimeheader ($subject), $encode ($body), implode ('', $this->headers));
	}

	private static function escape_recipient ($address, $name)
	{
		return '"' . self::sanitize ($name ?: $address) . '" <' . self::sanitize ($address) . '>';
	}

	private static function sanitize ($string)
	{
		return strtr ($string, array ("\r" => '', "\n" => '', "\t" => '', '"' => '', ',' => '', '<' => '', '>' => ''));
	}
}

?>
