<?php

namespace AdService;

class SiteConfigFactory
{
    protected static $instances;

    /**
     * @param string $url
     * @param bool $singleton en général, c'est la même config pour tous.
     * @return \AdService\SiteConfig\AbstractSiteConfig
     */
    public static function factory($url, $singleton=true)
    {
        if (false !== strpos($url, "leboncoin.fr")) {
            $className = 'AdService\SiteConfig\Lbc';
        } elseif (false !== strpos($url, "olx.ua")) {
            $className = 'AdService\SiteConfig\Olx';
        } else {
            throw new Exception("No config found");
        }
        if ($singleton) {
            if (!isset(self::$instances[$className])) {
                self::$instances[$className] = new $className;
            }
            return self::$instances[$className];
        }
        return new $className;
    }
}