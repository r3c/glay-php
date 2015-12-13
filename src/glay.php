<?php

namespace Glay;

function using ($class, $path)
{
	static $libraries;

	if (!isset ($libraries))
	{
		spl_autoload_register (function ($class) use (&$libraries)
		{
			if (isset ($libraries[$class]))
				require ($libraries[$class]);
		});

		$libraries = array ();
	}

	$libraries[$class] = $path;
}

$path = dirname (__FILE__);

using ('Glay\\Network\\HTTP', $path . '/network/http.php');
using ('Glay\\Network\\URI', $path . '/network/uri.php');

?>
