<?php

namespace Glay;

function using($class, $path)
{
    static $catalog;

    if (!isset($catalog)) {
        spl_autoload_register(function ($class) use (&$catalog) {
            if (isset($catalog[$class])) {
                require($catalog[$class]);
            }
        });

        $catalog = array();
    }

    // Register new class into library
    $catalog[$class] = $path;
}

$base = dirname(__FILE__) . '/';

using('Glay\\Network\\HTTP', $base . '/network/http.php');
using('Glay\\Network\\IPAddress', $base . '/network/ip.php');
using('Glay\\Network\\SMTP', $base . '/network/smtp.php');
using('Glay\\Network\\URI', $base . '/network/uri.php');
