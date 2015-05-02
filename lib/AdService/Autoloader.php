<?php

namespace AdService;

spl_autoload_register(function ($className) {
    if (0 === strpos($className, __NAMESPACE__."\\")) {
        $filename = realpath(__DIR__."/../".str_replace("\\", "/", $className).".php");
        if ($filename && is_file($filename)) {
            require_once $filename;
        }
    }
});
