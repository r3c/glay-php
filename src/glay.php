<?php

namespace Glay;

function using ($class, $path = null)
{
	static $catalog;
	static $include;

	if (!isset ($catalog))
	{
		spl_autoload_register (function ($class) use (&$catalog)
		{
			if (isset ($catalog[$class]))
				require ($catalog[$class]);
		});

		$catalog = array ();
	}

	// Register new class into library
	if ($path !== null)
	{
		if (!isset ($include))
			$include = dirname (__FILE__) . '/';

		$catalog[$class] = $include . $path;
	}

	// Path not set, class contains new inclusing path base
	else
		$include = $class . '/';
}

using ('Glay\\Network\\HTTP', 'network/http.php');
using ('Glay\\Network\\URI', 'network/uri.php');

?>
