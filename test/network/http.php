<?php

use Glay\Network\HTTP;
use Glay\Network\URI;

function assert_message($message, $code = null, $data = null, $headers = array())
{
    assert($message !== null, 'Message is null');

    if ($data !== null) {
        if (is_array($data)) {
            assert_json_include($data, json_decode($message->data, true), '');
        } else {
            assert(preg_match($data, $message->data), 'Message must match expression "' . $data . '" but was: ' . $message->data);
        }
    }

    if ($code !== null) {
        assert($message->code === $code, 'Code is "' . $message->code . '", not "' . $code . '"');
    }

    foreach ($headers as $name => $expected) {
        $value = $message->header($name);

        assert($value === $expected, 'Value of header "' . $name . '" is "' . $value . '", not "' . $expected . '"');
    }
}

function assert_json_include($reference, $candidate, $path)
{
    if (is_array($reference)) {
        foreach ($reference as $key => $value) {
            assert(isset($candidate[$key]), 'Value in path "' . $path . '" must have key "' . $key . '" set but was: ' . json_encode($candidate));
            assert_json_include($value, $candidate[$key], $path . '.' . $key);
        }
    } else {
        assert($candidate === $reference, 'Value in path "' . $path . '" must be "' . $reference . '" but was: ' . $candidate);
    }
}

header('Content-Type: text/plain');

ini_set('assert.exception', true);
ini_set('html_errors', false);

$http = new HTTP();

assert_message($http->query('GET', 'http://httpbin.org/get?key=value'), 200, array('args' => array('key' => 'value')));
assert_message($http->query('GET', 'http://httpbin.org/headers'), 200, null, array('content-type' => 'application/json', 'Content-Type' => 'application/json'));
assert_message($http->query('GET', 'http://httpbin.org/image/png'), 200, null, array('content-type' => 'image/png'));
assert_message($http->query('POST', 'http://httpbin.org/post', array('key' => 'value')), 200, array('form' => array('key' => 'value')));
assert_message($http->query('GET', 'http://httpbin.org/status/408'), 408, null);
assert_message($http->query('GET', 'invalid'), 0, null);

$http->cookies = array('a' => 'x', 'b' => 'y');

assert_message($http->query('GET', 'https://httpbin.org/cookies'), 200, array('cookies' => array('a' => 'x', 'b' => 'y')));

$http->cookies = null;

assert_message(HTTP::code(302), 302);
assert_message(HTTP::code(404, 'test'), 404, '/test/');
assert_message(HTTP::data('valid'), 200, '/valid/');
assert_message(HTTP::go('http://absolute/'), HTTP::REDIRECT_FOUND, null, array('Location' => 'http://absolute/', 'location' => 'http://absolute/'));
assert_message(HTTP::go('/relative', HTTP::REDIRECT_PERMANENT), HTTP::REDIRECT_PERMANENT, null, array('location' => (string)URI::here()->combine('/relative')));

echo 'OK';
