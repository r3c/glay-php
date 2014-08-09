<?php

namespace Glay\Network;

class	URI
{
	public function	__construct ($uri)
	{
		// Match URI against pattern
		//                 ((2:scheme) ) (  ((5: user  )( (7: pass))  )  (8: host )( (10:prt)) ) (11:pth)(   (13:qs)) ( (15))
		if (preg_match ('!^(([^:/?#]+):)?(//(([^@:/?#]+)(:([^@/?#]*))?@)?([^:/?#]+)(:([0-9]+))?)?([^?#]*)(\\?([^#]*))?(#(.*))?$!', $uri, $matches) !== 1)
			$matches = array ();

		// Extract URI components parts
		$this->fragment = isset ($matches[15]) && $matches[15] !== '' ? $matches[15] : null;
		$this->host = isset ($matches[8]) && $matches[8] !== '' ? strtolower ($matches[8]) : null;
		$this->pass = isset ($matches[7]) && $matches[7] !== '' ? $matches[7] : null;
		$this->path = isset ($matches[11]) && $matches[11] !== '' ? $matches[11] : null;
		$this->port = isset ($matches[10]) && $matches[10] !== '' ? (int)$matches[10] : null;
		$this->query = isset ($matches[13]) && $matches[13] !== '' ? $matches[13] : null;
		$this->scheme = isset ($matches[2]) && $matches[2] !== '' ? strtolower ($matches[2]) : null;
		$this->user = isset ($matches[5]) && $matches[5] !== '' ? $matches[5] : null;
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

	public function	canonical ()
	{
		$clone = clone $this;

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

			$clone->path = implode ('/', $path);
		}

		return $clone;
	}

	public function	combine ($other)
	{
		if ($other->scheme !== null)
			return $other;

		if ($other->host !== null)
		{
			$clone = clone $this;
			$clone->user = $other->user;
			$clone->pass = $other->pass;
			$clone->host = $other->host;
			$clone->port = $other->port;
			$clone->path = $other->path;
			$clone->query = $other->query;
			$clone->fragment = $other->fragment;

			return $clone;
		}

		if ($other->path !== null)
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

			$clone = clone $this;
			$clone->path = $path;
			$clone->query = $other->query;
			$clone->fragment = $other->fragment;

			return $clone;
		}

		if ($other->query !== null)
		{
			$clone = clone $this;
			$clone->query = $other->query;
			$clone->fragment = $other->fragment;

			return $clone;
		}

		if ($other->fragment !== null)
		{
			$clone = clone $this;
			$clone->fragment = $other->fragment;

			return $clone;
		}

		return $this;
	}
}

?>
