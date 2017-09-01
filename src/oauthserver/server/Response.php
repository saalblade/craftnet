<?php

namespace craftcom\oauthserver\server;

class Response extends \GuzzleHttp\Psr7\Response
{
    public function __toString()
    {
        return 'hello !';
    }
}