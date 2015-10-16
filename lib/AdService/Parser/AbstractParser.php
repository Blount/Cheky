<?php

namespace AdService\Parser;

use AdService\Filter;

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
}