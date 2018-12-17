<?php

use Glay\Network\IPAddress;

function assert_address($string, $is_valid, $is_public)
{
    $ip_address = new IPAddress($string);

    assert($ip_address->is_public() === $is_public, 'IP address \'' . $string . '\' must' . ($is_public ? '' : ' not') . ' be public');
    assert($ip_address->is_valid() === $is_valid, 'IP address \'' . $string . '\' must' . ($is_valid ? '' : ' not') . ' be valid');
}

header('Content-Type: text/plain');

assert_options(ASSERT_BAIL, true);

assert(IPAddress::create('1.2.3.4')->string === '1.2.3.4', 'IP address static "create" method is broken');

assert_address('1', false, false);
assert_address('1.2.3', false, false);
assert_address('1::2::3', false, false);

assert_address('10.1.2.3', true, false);
assert_address('12.1.2.3', true, true);
assert_address('127.0.0.1', true, false);
assert_address('128.0.0.1', true, true);
assert_address('132.16.4.3', true, true);
assert_address('172.16.4.3', true, false);
assert_address('192.168.42.17', true, false);
assert_address('212.168.42.17', true, true);

assert_address('::1', true, false);
assert_address('fd0a:eaaa:64af::', true, false); // http://unique-local-ipv6.com/
assert_address('2001:4860:4801:21::10', true, true); // http://test-ipv6.com/

echo 'OK';
