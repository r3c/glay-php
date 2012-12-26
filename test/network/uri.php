<?php

require ('../../src/network/uri.php');

use Glay\Network\URI;

function	testCombine ($base, $string, $expected)
{
	$result = (string)$base->combine (new URI ($string))->canonical ();

	if ($expected !== $result)
		echo "testCombine fail: $expected != $result\n";
	else
		echo "testCombine pass: $expected\n";
}

function	testConstruct ($string, $parts)
{
	$uri = new URI ($string);

	if (count ($parts) !== 8)
		echo "testConstruct fail: invalid parts array\n";
	else if ($parts[0] !== $uri->scheme)
		echo "testConstruct fail: invalid scheme, " . var_export ($parts[0], true) . " != " . var_export ($uri->scheme, true) . "\n";
	else if ($parts[1] !== $uri->user)
		echo "testConstruct fail: invalid user, " . var_export ($parts[1], true) . " != " . var_export ($uri->user, true) . "\n";
	else if ($parts[2] !== $uri->pass)
		echo "testConstruct fail: invalid pass, " . var_export ($parts[2], true) . " != " . var_export ($uri->pass, true) . "\n";
	else if ($parts[3] !== $uri->host)
		echo "testConstruct fail: invalid host, " . var_export ($parts[3], true) . " != " . var_export ($uri->host, true) . "\n";
	else if ($parts[4] !== $uri->port)
		echo "testConstruct fail: invalid port, " . var_export ($parts[4], true) . " != " . var_export ($uri->port, true) . "\n";
	else if ($parts[5] !== $uri->path)
		echo "testConstruct fail: invalid path, " . var_export ($parts[5], true) . " != " . var_export ($uri->path, true) . "\n";
	else if ($parts[6] !== $uri->query)
		echo "testConstruct fail: invalid query, " . var_export ($parts[6], true) . " != " . var_export ($uri->query, true) . "\n";
	else if ($parts[7] !== $uri->fragment)
		echo "testConstruct fail: invalid fragment, " . var_export ($parts[7], true) . " != " . var_export ($uri->fragment, true) . "\n";
	else
		echo "testConstruct pass: $string\n";
}

function	testString ($expected)
{
	$result = (string)(new URI ($expected));

	if ($expected !== $result)
		echo "testString fail: $expected != $result\n";
	else
		echo "testString pass: $expected\n";
}

header ('Content-Type: text/plain');

testConstruct ('http://domain.com', array ('http', null, null, 'domain.com', null, null, null, null));
testConstruct ('http://domain.com/', array ('http', null, null, 'domain.com', null, '/', null, null));
testConstruct ('http://domain.com/path/', array ('http', null, null, 'domain.com', null, '/path/', null, null));
testConstruct ('http://domain.com/file', array ('http', null, null, 'domain.com', null, '/file', null, null));
testConstruct ('http://domain.com/path/?qs=1', array ('http', null, null, 'domain.com', null, '/path/', 'qs=1', null));
testConstruct ('http://domain.com/path/to/#hash', array ('http', null, null, 'domain.com', null, '/path/to/', null, 'hash'));
testConstruct ('http://domain.com/path/?qs=1#hash', array ('http', null, null, 'domain.com', null, '/path/', 'qs=1', 'hash'));
testConstruct ('http://domain.com/path/file?qs=1', array ('http', null, null, 'domain.com', null, '/path/file', 'qs=1', null));
testConstruct ('http://domain.com/path/file#hash', array ('http', null, null, 'domain.com', null, '/path/file', null, 'hash'));
testConstruct ('http://domain.com/path/file?qs=1#hash', array ('http', null, null, 'domain.com', null, '/path/file', 'qs=1', 'hash'));

testConstruct ('http://user@domain.com:8080/', array ('http', 'user', null, 'domain.com', 8080, '/', null, null));
testConstruct ('ftp://user:pass@domain.com', array ('ftp', 'user', 'pass', 'domain.com', null, null, null, null));

testConstruct ('//domain.com', array (null, null, null, 'domain.com', null, null, null, null));
testConstruct ('/path/', array (null, null, null, null, null, '/path/', null, null));
testConstruct ('/path/#hash', array (null, null, null, null, null, '/path/', null, 'hash'));
testConstruct ('file?qs=1', array (null, null, null, null, null, 'file', 'qs=1', null));
testConstruct ('?qs=1', array (null, null, null, null, null, null, 'qs=1', null));
testConstruct ('#hash', array (null, null, null, null, null, null, null, 'hash'));

testString ("http://domain.com/");
testString ("http://domain.com/file");
testString ("http://domain.com/path/");
testString ("http://domain.com/path/file");
testString ("http://domain.com/?get");
testString ("http://domain.com/#hash");
testString ("http://domain.com/?get#hash");
testString ("http://domain.com/file?get#hash");
testString ("http://domain.com/path/file?get#hash");

testString ("//domain.com");
testString ("//domain.com/file");
testString ("//domain.com/path/");
testString ("//domain.com/path/file");
testString ("//domain.com/?get");
testString ("//domain.com/#hash");
testString ("//domain.com/?get#hash");
testString ("//domain.com/file?get#hash");
testString ("//domain.com/path/file?get#hash");

testString ("file");
testString ("/path/");
testString ("path/file");
testString ("/?get");
testString ("#hash");
testString ("?get#hash");
testString ("file?get#hash");
testString ("/path/file?get#hash");

$base = new URI ('http://www.yaronet.com/sp/dposts.php?id=5');

testCombine ($base, 'dposts2.php', 'http://www.yaronet.com/sp/dposts2.php');
testCombine ($base, '/truc', 'http://www.yaronet.com/truc');
testCombine ($base, '?id=9', 'http://www.yaronet.com/sp/dposts.php?id=9');
testCombine ($base, '#bas', 'http://www.yaronet.com/sp/dposts.php?id=5#bas');

$base = new URI ('http://a/b/c/d;p?q');

testCombine ($base, 'g:h', 'g:h');
testCombine ($base, 'g', 'http://a/b/c/g');
testCombine ($base, './g', 'http://a/b/c/g');
testCombine ($base, 'g/', 'http://a/b/c/g/');
testCombine ($base, '/g', 'http://a/g');
testCombine ($base, '//g', 'http://g');
testCombine ($base, '?y', 'http://a/b/c/d;p?y');
testCombine ($base, 'g?y', 'http://a/b/c/g?y');
testCombine ($base, '#s', 'http://a/b/c/d;p?q#s');
testCombine ($base, 'g#s', 'http://a/b/c/g#s');
testCombine ($base, 'g?y#s', 'http://a/b/c/g?y#s');
testCombine ($base, ';x', 'http://a/b/c/;x');
testCombine ($base, 'g;x', 'http://a/b/c/g;x');
testCombine ($base, 'g;x?y#s', 'http://a/b/c/g;x?y#s');
testCombine ($base, '', 'http://a/b/c/d;p?q');
testCombine ($base, '.', 'http://a/b/c/');
testCombine ($base, './', 'http://a/b/c/');
testCombine ($base, '..', 'http://a/b/');
testCombine ($base, '../', 'http://a/b/');
testCombine ($base, '../g', 'http://a/b/g');
testCombine ($base, '../..', 'http://a/');
testCombine ($base, '../../', 'http://a/');
testCombine ($base, '../../g', 'http://a/g');

?>
