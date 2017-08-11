<?php

use Glay\Network\HTTP;
use Glay\Network\URI;

function assert_message ($message, $code = null, $data = null, $headers = array ())
{
	assert ($message !== null, 'Message is null');

	if ($data !== null)
		assert (preg_match ($data, $message->data), 'Data is "' . $message->data . '", does not match "' . $data . '"');

	if ($code !== null)
		assert ($message->code === $code, 'Code is "' . $message->code . '", not "' . $code . '"');

	foreach ($headers as $name => $expected)
	{
		$value = $message->header ($name);

		assert ($value === $expected, 'Value of header "' . $name . '" is "' . $value . '", not "' . $expected . '"');
	}
}

header ('Content-Type: text/plain');

assert_options (ASSERT_BAIL, true);

$http = new HTTP ();

assert_message ($http->query ('GET', 'http://httpbin.org/get?key=value'), 200, '/"key": "value"/');
assert_message ($http->query ('GET', 'http://httpbin.org/headers'), 200, null, array ('content-type' => 'application/json', 'Content-Type' => 'application/json'));
assert_message ($http->query ('GET', 'http://httpbin.org/image/png'), 200, null, array ('content-type' => 'image/png'));
assert_message ($http->query ('POST', 'http://httpbin.org/post', array ('key' => 'value')), 200, '/"key": "value"/');
assert_message ($http->query ('GET', 'http://httpbin.org/status/408'), 408, null);
assert_message ($http->query ('GET', 'invalid'), 0, '//');

assert_message (HTTP::code (302), 302);
assert_message (HTTP::code (404, 'test'), 404, '/test/');
assert_message (HTTP::data ('valid'), 200, '/valid/');
assert_message (HTTP::goto ('http://absolute/'), HTTP::REDIRECT_FOUND, null, array ('Location' => 'http://absolute/', 'location' => 'http://absolute/'));
assert_message (HTTP::goto ('/relative', HTTP::REDIRECT_PERMANENT), HTTP::REDIRECT_PERMANENT, null, array ('location' => (string)URI::here ()->combine ('/relative')));

echo 'OK';

?>
