<?php

namespace Glay\Network;

class URI
{
	public static function base ()
	{
		static $base;

		if (!isset ($base))
		{
			$https = (isset ($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] === 'true') || (isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off');
			$port = $https ? 443 : 80;

			$base = new URI
			(
				($https ? 'https' : 'http') . '://' .
				(isset ($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '' ? $_SERVER['HTTP_HOST'] : 'localhost') .
				(isset ($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] !== $port ? ':' . $_SERVER['SERVER_PORT'] : '') .
				(isset ($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')
			);
		}

		return $base;
	}

	public static function create ($uri)
	{
		return new URI ($uri);
	}

	public function __construct ($uri)
	{
		// Match URI against pattern: (  (1:scheme) ) (    (  (2: user  )(   (3: pass ))  )  (4: host)(   (5:port)) ) (6:path)(     (7: qs)) (   (8:))
		$this->valid = preg_match ('!^(?:([^:/?#]+):)?(?://(?:([^@:/?#]+)(?::([^@/?#]*))?@)?([^:/?#]+)(?::([0-9]+))?)?([^?#]*)(?:\\?([^#]*))?(?:#(.*))?$!', $uri, $matches) === 1;

		// Extract URI components parts
		$this->fragment = isset ($matches[8]) && $matches[8] !== '' ? $matches[8] : null;
		$this->host = isset ($matches[4]) && $matches[4] !== '' ? strtolower ($matches[4]) : null;
		$this->pass = isset ($matches[3]) && $matches[3] !== '' ? $matches[3] : null;
		$this->path = isset ($matches[6]) && $matches[6] !== '' ? $matches[6] : null;
		$this->port = isset ($matches[5]) && $matches[5] !== '' ? (int)$matches[5] : null;
		$this->query = isset ($matches[7]) && $matches[7] !== '' ? $matches[7] : null;
		$this->scheme = isset ($matches[1]) && $matches[1] !== '' ? strtolower ($matches[1]) : null;
		$this->user = isset ($matches[2]) && $matches[2] !== '' ? $matches[2] : null;
	}

	public function __toString ()
	{
		$uri = '';

		if ($this->scheme !== null)
			$uri .= $this->scheme . ':';

		if ($this->host !== null)
		{
			$uri .= '//';

			if ($this->user !== null)
			{
				$uri .= $this->user;

				if ($this->pass !== null)
					$uri .= ':' . $this->pass;

				$uri .= '@';
			}

			$uri .= $this->host;

			if ($this->port !== null)
				$uri .= ':' . $this->port;
		}

		if ($this->path !== null)
			$uri .= $this->path;

		if ($this->query !== null)
			$uri .= '?' . $this->query;

		if ($this->fragment !== null)
			$uri .= '#' . $this->fragment;

		return $uri;
	}

	public function canonical ()
	{
		$canonical = clone $this;

		if ($this->path !== null)
		{
			$path = explode ('/', $this->path);

			for ($i = 0; $i < count ($path); ++$i)
			{
				switch ($path[$i])
				{
					case '..':
						$count = $i > 0 ? 2 : 1;

						if ($i + 1 < count ($path))
						{
							array_splice ($path, $i - $count + 1, $count);

							$i -= $count;
						}
						else
						{
							array_splice ($path, $i - $count + 1, $count, '');

							$i -= $count - 1;
						}

						break;

					case '.':
						if ($i + 1 < count ($path))
							array_splice ($path, $i--, 1);
						else
							$path[$i] = '';

						break;
				}
			}

			$canonical->path = implode ('/', $path);
		}

		return $canonical;
	}

	public function combine ($other)
	{
		if ($other->scheme !== null)
			$combine = $other;

		else if ($other->host !== null)
		{
			$combine = clone $this;
			$combine->user = $other->user;
			$combine->pass = $other->pass;
			$combine->host = $other->host;
			$combine->port = $other->port;
			$combine->path = $other->path;
			$combine->query = $other->query;
			$combine->fragment = $other->fragment;
		}

		else if ($other->path !== null)
		{
			if ($other->path === '' || $other->path[0] !== '/')
			{
				$offset = strrpos ($this->path, '/');

				if ($offset !== false)
					$path = substr ($this->path, 0, $offset + 1) . $other->path;
				else
					$path = $other->path;
			}
			else
				$path = $other->path;

			$combine = clone $this;
			$combine->path = $path;
			$combine->query = $other->query;
			$combine->fragment = $other->fragment;
		}

		else if ($other->query !== null)
		{
			$combine = clone $this;
			$combine->query = $other->query;
			$combine->fragment = $other->fragment;
		}

		else if ($other->fragment !== null)
		{
			$combine = clone $this;
			$combine->fragment = $other->fragment;
		}

		else
			$combine = $this;

		return $combine;
	}

	public function relative ($base)
	{
		$relative = clone $this;

		if ($base->scheme !== $relative->scheme)
			return $relative;

		$relative->scheme = null;

		if ($base->host !== $relative->host)
			return $relative;

		$relative->host = null;
		$relative->pass = null;
		$relative->port = null;
		$relative->user = null;

		if ($base->path !== $relative->path)
		{
			if ($relative->path === null)
				$relative->path = '/';

			return $relative;
		}

		$relative->path = null;

		if ($base->query !== $relative->query)
		{
			if ($relative->query === null)
				$relative->query = '';

			return $relative;
		}

		$relative->query = null;

		if ($base->fragment !== $relative->fragment)
		{
			if ($relative->fragment === null)
				$relative->fragment = '';

			return $relative;
		}

		return $relative;
	}
}

?>
