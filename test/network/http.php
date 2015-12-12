<?php

require ('../../src/network/http.php');

use Glay\Network\HTTP;

function assert_message ($message, $status = null, $contents = null, $headers = array ())
{
	assert ($message !== null, 'Message is null');

	if ($contents !== null)
		assert (preg_match ($contents, $message->contents), 'Contents "' . $message->contents . '" does not match "' . $contents . '"');

	if ($status !== null)
		assert ($message->status === $status, 'Status "' . $message->status . '" does not match "' . $status . '"');

	foreach ($headers as $name => $value)
	{
		assert (isset ($message->headers[$name]), 'Header "' . $name . '" is not set');
		assert ($message->headers[$name] === $value, 'Value of header "' . $name . '" is not "' . $value . '"');
	}
}

header ('Content-Type: text/plain');

assert_options (ASSERT_BAIL, true);

$http = new HTTP ();

assert_message ($http->send ('GET', 'http://httpbin.org/get?key=value'), 200, '/"key": "value"/');
assert_message ($http->send ('POST', 'http://httpbin.org/post', array ('key' => 'value')), 200, '/"key": "value"/');
assert_message ($http->send ('GET', 'http://httpbin.org/image/png'), 200, null, array ('content-type' => 'image/png'));

assert_message (HTTP::code (302), 302);
assert_message (HTTP::code (404, 'test'), 404, '/test/');
assert_message (HTTP::ok ('valid'), 200, '/valid/');
assert_message (HTTP::to ('http://localhost/'), HTTP::REDIRECT_FOUND, null, array ('location' => 'http://localhost/'));
assert_message (HTTP::to ('http://localhost/', HTTP::REDIRECT_PERMANENT), HTTP::REDIRECT_PERMANENT, null, array ('location' => 'http://localhost/'));

echo "OK";

?>
