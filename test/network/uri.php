<?php

use Glay\Network\URI;

function assert_absolute ($string, $overrides, $expected)
{
	$backups = array ();

	foreach ($overrides as $key => $value)
	{
		$backups[$key] = isset ($_SERVER[$key]) ? $value : null;
		$_SERVER[$key] = $value;
	}

	$result = (string)URI::here (true)->combine ($string);

	foreach ($backups as $key => $value)
	{
		if ($value !== null)
			$_SERVER[$key] = $value;
		else
			unset ($_SERVER[$key]);
	}

	assert ($expected === $result, "assert_absolute: $expected != $result");
}

function assert_combine ($base, $string, $expected)
{
	$result = (string)$base->combine ($string)->canonical ();

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
	$result = (string)URI::create ($expected);

	assert ($expected === $result, "assert_string: $expected != $result");
}

header ('Content-Type: text/plain');

assert_options (ASSERT_BAIL, true);

assert_absolute ('?query', array ('HTTP_HOST' => 'myhost', 'HTTP_X_SSL' => '', 'REQUEST_URI' => '', 'SERVER_PORT' => ''), 'http://myhost?query');
assert_absolute ('?query', array ('HTTP_HOST' => 'myhost', 'HTTP_X_SSL' => '', 'REQUEST_URI' => '', 'SERVER_PORT' => '81'), 'http://myhost:81?query');
assert_absolute ('?query', array ('HTTP_HOST' => 'myhost', 'HTTP_X_SSL' => '', 'REQUEST_URI' => '/path', 'SERVER_PORT' => ''), 'http://myhost/path?query');
assert_absolute ('?query', array ('HTTP_HOST' => 'myhost', 'HTTP_X_SSL' => 'off', 'REQUEST_URI' => '', 'SERVER_PORT' => ''), 'http://myhost?query');
assert_absolute ('?query', array ('HTTP_HOST' => 'myhost', 'HTTP_X_SSL' => 'on', 'REQUEST_URI' => '', 'SERVER_PORT' => ''), 'https://myhost?query');
assert_absolute ('?query', array ('HTTP_HOST' => 'myhost', 'HTTP_X_SSL' => 'on', 'REQUEST_URI' => '', 'SERVER_PORT' => '443'), 'https://myhost?query');

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

// Test conversion to relative URL
$bases = array (							'http://domain',		'http://domain/',	'http://domain/home',	'http://domain/sub/',	'http://domain/?a=5',	'http://domain/?a=1#key');
$tests = array
(
	'http://domain'				=> array (	'',						'/',				'/',					'/',					'/',					'/'),
	'http://domain/'			=> array (	'/',					'',					'.',					'/',					'?',					'?'),
	'http://domain/home'		=> array (	'/home',				'home',				'',						'/home',				'home',					'home'),
	'http://domain/home/help'	=> array (	'/home/help',			'home/help',		'home/help',			'/home/help',			'home/help',			'home/help'),
	'http://domain/sub'			=> array (	'/sub',					'sub',				'sub',					'/sub',					'sub',					'sub'),
	'http://domain/sub/folder'	=> array (	'/sub/folder',			'sub/folder',		'sub/folder',			'folder',				'sub/folder',			'sub/folder'),
	'http://domain/?a=3'		=> array (	'/?a=3',				'?a=3',				'.?a=3',				'/?a=3',				'?a=3',					'?a=3'),
	'http://domain/?a=5'		=> array (	'/?a=5',				'?a=5',				'.?a=5',				'/?a=5',				'',						'?a=5'),
	'http://domain/?a=1#other'	=> array (	'/?a=1#other',			'?a=1#other',		'.?a=1#other',			'/?a=1#other',			'?a=1#other',			'#other'),
	'http://'					=> array (	'//',					'//',				'//',					'//',					'//',					'//'),
	'//other'					=> array (	'//other',				'//other',			'//other',				'//other',				'//other',				'//other'),
	'/path'						=> array (	'/path',				'/path',			'/path',				'/path',				'/path',				'/path'),
	'?a=1'						=> array (	'?a=1',					'?a=1',				'?a=1',					'?a=1',					'?a=1',					'?a=1'),
	'#key'						=> array (	'#key',					'#key',				'#key',					'#key',					'#key',					'#key')
);

foreach ($bases as $i => $base)
{
	foreach ($tests as $test => $references)
	{
		$result = (string)URI::create ($test)->relative ($base);

		assert ($references[$i] === $result, "'$test' relative to '$base' should be '$references[$i]', got '$result'");
	}
}

echo 'OK';

?>
