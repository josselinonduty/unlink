<?php

function generateShortId($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $shortId = '';
    for ($i = 0; $i < $length; $i++) {
        $shortId .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $shortId;
}
