<?php

namespace KRG\UserBundle\Util;

class TokenGenerator
{
    static public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
