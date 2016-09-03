<?php

namespace Glay\Network;

class IPAddress
{
	public static function remote ()
	{
		return new IPAddress ($_SERVER['REMOTE_ADDR']);
	}

	public function __construct ($string)
	{
		$this->string = $string;
	}

	public function is_public ()
	{
		return filter_var ($this->string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
	}
}

?>
