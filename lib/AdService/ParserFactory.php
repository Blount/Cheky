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
        throw new Exception("No parser found");
    }
}