<?php

namespace Glay\Network;

class IPAddress
{
    public static function create(string $string): IPAddress
    {
        return new IPAddress($string);
    }

    public static function remote(): IPAddress
    {
        return new IPAddress($_SERVER['REMOTE_ADDR']);
    }

    public $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public function is_public(): bool
    {
        return filter_var($this->string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    public function is_valid(): bool
    {
        return filter_var($this->string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }
}
