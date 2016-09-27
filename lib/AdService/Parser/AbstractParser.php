<?php

namespace AdService\Parser;

use AdService\Filter;
use AdService\Ad;

abstract class AbstractParser extends \DOMDocument
{
    public function __construct()
    {
        libxml_use_internal_errors(true);
    }

    /**
     * @param string $content
     * @param Filter $filter
     */
    abstract public function process($content, Filter $filter = null);

    /**
     * @param string $content
     * @param Filter $filter
     * @return null|Ad
     */
    public function processAd($content)
    {
        return null;
    }
}