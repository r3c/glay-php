<?php

require ('../../src/network/uri.php');

use Glay\Network\URI;

function test_absolute ($string, $expected)
{
	$result = (string)(new URI ($string))->absolute ();

	assert ($expected === $result, "test_absolute: $expected != $result");
}

function test_combine ($base, $string, $expected)
{
	$result = (string)$base->combine (new URI ($string))->canonical ();

	assert ($expected === $result, "test_combine: $expected != $result");
}

function test_construct ($string, $parts)
{
	$uri = new URI ($string);

	assert (count ($parts) === 8, "test_construct: invalid parts array");
	assert ($parts[0] === $uri->scheme, "test_construct: invalid scheme, " . var_export ($parts[0], true) . " != " . var_export ($uri->scheme, true));
	assert ($parts[1] === $uri->user, "test_construct: invalid user, " . var_export ($parts[1], true) . " != " . var_export ($uri->user, true));
	assert ($parts[2] === $uri->pass, "test_construct: invalid pass, " . var_export ($parts[2], true) . " != " . var_export ($uri->pass, true));
	assert ($parts[3] === $uri->host, "test_construct: invalid host, " . var_export ($parts[3], true) . " != " . var_export ($uri->host, true));
	assert ($parts[4] === $uri->port, "test_construct: invalid port, " . var_export ($parts[4], true) . " != " . var_export ($uri->port, true));
	assert ($parts[5] === $uri->path, "test_construct: invalid path, " . var_export ($parts[5], true) . " != " . var_export ($uri->path, true));
	assert ($parts[6] === $uri->query, "test_construct: invalid query, " . var_export ($parts[6], true) . " != " . var_export ($uri->query, true));
	assert ($parts[7] === $uri->fragment, "test_construct: invalid fragment, " . var_export ($parts[7], true) . " != " . var_export ($uri->fragment, true));
}

function test_string ($expected)
{
	$result = (string)(new URI ($expected));

	assert ($expected === $result, "test_string: $expected != $result");
}

header ('Content-Type: text/plain');

assert_options (ASSERT_BAIL, true);

test_absolute ('/test', 'http://' . $_SERVER['HTTP_HOST'] . '/test');

test_construct ('http://domain.com', array ('http', null, null, 'domain.com', null, null, null, null));
test_construct ('http://domain.com/', array ('http', null, null, 'domain.com', null, '/', null, null));
test_construct ('http://domain.com/path/', array ('http', null, null, 'domain.com', null, '/path/', null, null));
test_construct ('http://domain.com/file', array ('http', null, null, 'domain.com', null, '/file', null, null));
test_construct ('http://domain.com/path/?qs=1', array ('http', null, null, 'domain.com', null, '/path/', 'qs=1', null));
test_construct ('http://domain.com/path/to/#hash', array ('http', null, null, 'domain.com', null, '/path/to/', null, 'hash'));
test_construct ('http://domain.com/path/?qs=1#hash', array ('http', null, null, 'domain.com', null, '/path/', 'qs=1', 'hash'));
test_construct ('http://domain.com/path/file?qs=1', array ('http', null, null, 'domain.com', null, '/path/file', 'qs=1', null));
test_construct ('http://domain.com/path/file#hash', array ('http', null, null, 'domain.com', null, '/path/file', null, 'hash'));
test_construct ('http://domain.com/path/file?qs=1#hash', array ('http', null, null, 'domain.com', null, '/path/file', 'qs=1', 'hash'));

test_construct ('http://user@domain.com:8080/', array ('http', 'user', null, 'domain.com', 8080, '/', null, null));
test_construct ('ftp://user:pass@domain.com', array ('ftp', 'user', 'pass', 'domain.com', null, null, null, null));

test_construct ('//domain.com', array (null, null, null, 'domain.com', null, null, null, null));
test_construct ('/path/', array (null, null, null, null, null, '/path/', null, null));
test_construct ('/path/#hash', array (null, null, null, null, null, '/path/', null, 'hash'));
test_construct ('file?qs=1', array (null, null, null, null, null, 'file', 'qs=1', null));
test_construct ('?qs=1', array (null, null, null, null, null, null, 'qs=1', null));
test_construct ('#hash', array (null, null, null, null, null, null, null, 'hash'));

test_string ("http://domain.com/");
test_string ("http://domain.com/file");
test_string ("http://domain.com/path/");
test_string ("http://domain.com/path/file");
test_string ("http://domain.com/?get");
test_string ("http://domain.com/#hash");
test_string ("http://domain.com/?get#hash");
test_string ("http://domain.com/file?get#hash");
test_string ("http://domain.com/path/file?get#hash");

test_string ("//domain.com");
test_string ("//domain.com/file");
test_string ("//domain.com/path/");
test_string ("//domain.com/path/file");
test_string ("//domain.com/?get");
test_string ("//domain.com/#hash");
test_string ("//domain.com/?get#hash");
test_string ("//domain.com/file?get#hash");
test_string ("//domain.com/path/file?get#hash");

test_string ("file");
test_string ("/path/");
test_string ("path/file");
test_string ("/?get");
test_string ("#hash");
test_string ("?get#hash");
test_string ("file?get#hash");
test_string ("/path/file?get#hash");

$base = new URI ('http://www.yaronet.com/sp/dposts.php?id=5');

test_combine ($base, 'dposts2.php', 'http://www.yaronet.com/sp/dposts2.php');
test_combine ($base, '/truc', 'http://www.yaronet.com/truc');
test_combine ($base, '?id=9', 'http://www.yaronet.com/sp/dposts.php?id=9');
test_combine ($base, '#bas', 'http://www.yaronet.com/sp/dposts.php?id=5#bas');

$base = new URI ('http://a/b/c/d;p?q');

test_combine ($base, 'g:h', 'g:h');
test_combine ($base, 'g', 'http://a/b/c/g');
test_combine ($base, './g', 'http://a/b/c/g');
test_combine ($base, 'g/', 'http://a/b/c/g/');
test_combine ($base, '/g', 'http://a/g');
test_combine ($base, '//g', 'http://g');
test_combine ($base, '?y', 'http://a/b/c/d;p?y');
test_combine ($base, 'g?y', 'http://a/b/c/g?y');
test_combine ($base, '#s', 'http://a/b/c/d;p?q#s');
test_combine ($base, 'g#s', 'http://a/b/c/g#s');
test_combine ($base, 'g?y#s', 'http://a/b/c/g?y#s');
test_combine ($base, ';x', 'http://a/b/c/;x');
test_combine ($base, 'g;x', 'http://a/b/c/g;x');
test_combine ($base, 'g;x?y#s', 'http://a/b/c/g;x?y#s');
test_combine ($base, '', 'http://a/b/c/d;p?q');
test_combine ($base, '.', 'http://a/b/c/');
test_combine ($base, './', 'http://a/b/c/');
test_combine ($base, '..', 'http://a/b/');
test_combine ($base, '../', 'http://a/b/');
test_combine ($base, '../g', 'http://a/b/g');
test_combine ($base, '../..', 'http://a/');
test_combine ($base, '../../', 'http://a/');
test_combine ($base, '../../g', 'http://a/g');

echo "OK";

?>
