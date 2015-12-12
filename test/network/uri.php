<?php

require ('../../src/network/uri.php');

use Glay\Network\URI;

function assert_absolute ($string, $expected)
{
	$result = (string)(new URI ($string))->absolute ();

	assert ($expected === $result, "assert_absolute: $expected != $result");
}

function assert_combine ($base, $string, $expected)
{
	$result = (string)$base->combine (new URI ($string))->canonical ();

	assert ($expected === $result, "assert_combine: $expected != $result");
}

function assert_construct ($string, $parts)
{
	$uri = new URI ($string);

	assert (count ($parts) === 8, "assert_construct: invalid parts array");
	assert ($parts[0] === $uri->scheme, "assert_construct: invalid scheme, " . var_export ($parts[0], true) . " != " . var_export ($uri->scheme, true));
	assert ($parts[1] === $uri->user, "assert_construct: invalid user, " . var_export ($parts[1], true) . " != " . var_export ($uri->user, true));
	assert ($parts[2] === $uri->pass, "assert_construct: invalid pass, " . var_export ($parts[2], true) . " != " . var_export ($uri->pass, true));
	assert ($parts[3] === $uri->host, "assert_construct: invalid host, " . var_export ($parts[3], true) . " != " . var_export ($uri->host, true));
	assert ($parts[4] === $uri->port, "assert_construct: invalid port, " . var_export ($parts[4], true) . " != " . var_export ($uri->port, true));
	assert ($parts[5] === $uri->path, "assert_construct: invalid path, " . var_export ($parts[5], true) . " != " . var_export ($uri->path, true));
	assert ($parts[6] === $uri->query, "assert_construct: invalid query, " . var_export ($parts[6], true) . " != " . var_export ($uri->query, true));
	assert ($parts[7] === $uri->fragment, "assert_construct: invalid fragment, " . var_export ($parts[7], true) . " != " . var_export ($uri->fragment, true));
}

function assert_string ($expected)
{
	$result = (string)(new URI ($expected));

	assert ($expected === $result, "assert_string: $expected != $result");
}

header ('Content-Type: text/plain');

assert_options (ASSERT_BAIL, true);

assert_absolute ('/test', 'http://' . $_SERVER['HTTP_HOST'] . '/test');

assert_construct ('http://domain.com', array ('http', null, null, 'domain.com', null, null, null, null));
assert_construct ('http://domain.com/', array ('http', null, null, 'domain.com', null, '/', null, null));
assert_construct ('http://domain.com/path/', array ('http', null, null, 'domain.com', null, '/path/', null, null));
assert_construct ('http://domain.com/file', array ('http', null, null, 'domain.com', null, '/file', null, null));
assert_construct ('http://domain.com/path/?qs=1', array ('http', null, null, 'domain.com', null, '/path/', 'qs=1', null));
assert_construct ('http://domain.com/path/to/#hash', array ('http', null, null, 'domain.com', null, '/path/to/', null, 'hash'));
assert_construct ('http://domain.com/path/?qs=1#hash', array ('http', null, null, 'domain.com', null, '/path/', 'qs=1', 'hash'));
assert_construct ('http://domain.com/path/file?qs=1', array ('http', null, null, 'domain.com', null, '/path/file', 'qs=1', null));
assert_construct ('http://domain.com/path/file#hash', array ('http', null, null, 'domain.com', null, '/path/file', null, 'hash'));
assert_construct ('http://domain.com/path/file?qs=1#hash', array ('http', null, null, 'domain.com', null, '/path/file', 'qs=1', 'hash'));

assert_construct ('http://user@domain.com:8080/', array ('http', 'user', null, 'domain.com', 8080, '/', null, null));
assert_construct ('ftp://user:pass@domain.com', array ('ftp', 'user', 'pass', 'domain.com', null, null, null, null));

assert_construct ('//domain.com', array (null, null, null, 'domain.com', null, null, null, null));
assert_construct ('/path/', array (null, null, null, null, null, '/path/', null, null));
assert_construct ('/path/#hash', array (null, null, null, null, null, '/path/', null, 'hash'));
assert_construct ('file?qs=1', array (null, null, null, null, null, 'file', 'qs=1', null));
assert_construct ('?qs=1', array (null, null, null, null, null, null, 'qs=1', null));
assert_construct ('#hash', array (null, null, null, null, null, null, null, 'hash'));

assert_string ("http://domain.com/");
assert_string ("http://domain.com/file");
assert_string ("http://domain.com/path/");
assert_string ("http://domain.com/path/file");
assert_string ("http://domain.com/?get");
assert_string ("http://domain.com/#hash");
assert_string ("http://domain.com/?get#hash");
assert_string ("http://domain.com/file?get#hash");
assert_string ("http://domain.com/path/file?get#hash");

assert_string ("//domain.com");
assert_string ("//domain.com/file");
assert_string ("//domain.com/path/");
assert_string ("//domain.com/path/file");
assert_string ("//domain.com/?get");
assert_string ("//domain.com/#hash");
assert_string ("//domain.com/?get#hash");
assert_string ("//domain.com/file?get#hash");
assert_string ("//domain.com/path/file?get#hash");

assert_string ("file");
assert_string ("/path/");
assert_string ("path/file");
assert_string ("/?get");
assert_string ("#hash");
assert_string ("?get#hash");
assert_string ("file?get#hash");
assert_string ("/path/file?get#hash");

$base = new URI ('http://www.yaronet.com/sp/dposts.php?id=5');

assert_combine ($base, 'dposts2.php', 'http://www.yaronet.com/sp/dposts2.php');
assert_combine ($base, '/truc', 'http://www.yaronet.com/truc');
assert_combine ($base, '?id=9', 'http://www.yaronet.com/sp/dposts.php?id=9');
assert_combine ($base, '#bas', 'http://www.yaronet.com/sp/dposts.php?id=5#bas');

$base = new URI ('http://a/b/c/d;p?q');

assert_combine ($base, 'g:h', 'g:h');
assert_combine ($base, 'g', 'http://a/b/c/g');
assert_combine ($base, './g', 'http://a/b/c/g');
assert_combine ($base, 'g/', 'http://a/b/c/g/');
assert_combine ($base, '/g', 'http://a/g');
assert_combine ($base, '//g', 'http://g');
assert_combine ($base, '?y', 'http://a/b/c/d;p?y');
assert_combine ($base, 'g?y', 'http://a/b/c/g?y');
assert_combine ($base, '#s', 'http://a/b/c/d;p?q#s');
assert_combine ($base, 'g#s', 'http://a/b/c/g#s');
assert_combine ($base, 'g?y#s', 'http://a/b/c/g?y#s');
assert_combine ($base, ';x', 'http://a/b/c/;x');
assert_combine ($base, 'g;x', 'http://a/b/c/g;x');
assert_combine ($base, 'g;x?y#s', 'http://a/b/c/g;x?y#s');
assert_combine ($base, '', 'http://a/b/c/d;p?q');
assert_combine ($base, '.', 'http://a/b/c/');
assert_combine ($base, './', 'http://a/b/c/');
assert_combine ($base, '..', 'http://a/b/');
assert_combine ($base, '../', 'http://a/b/');
assert_combine ($base, '../g', 'http://a/b/g');
assert_combine ($base, '../..', 'http://a/');
assert_combine ($base, '../../', 'http://a/');
assert_combine ($base, '../../g', 'http://a/g');

echo "OK";

?>
