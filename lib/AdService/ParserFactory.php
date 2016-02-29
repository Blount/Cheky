<?php

namespace AdService;

class ParserFactory
{
    /**
     * @param string $url
     * @return \AdService\Parser\AbstractParser
     */
    public static function factory($url)
    {
        if (false !== strpos($url, "leboncoin.fr")) {
            return new Parser\Lbc();
        }
        if (false !== strpos($url, "olx.ua")) {
            return new Parser\Olx();
        }
        if (false !== strpos($url, "seloger.com")) {
            return new Parser\Seloger();
        }
        throw new Exception("No parser found");
    }
}