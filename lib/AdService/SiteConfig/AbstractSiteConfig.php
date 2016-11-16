<?php

namespace AdService\SiteConfig;

abstract class AbstractSiteConfig
{
    /**
     * Nom du site
     * @var string
     */
    protected $site_name = "";

    /**
     * URL du site
     * @var string
     */
    protected $site_url = "";

    /**
     * Les devises acceptées par le site.
     * @var array
     */
    protected $currencies = array("€");

    /**
     * Indique si l'information d'une annonce pro ou particulier est disponible
     * dans la liste d'annonce.
     * @var bool
     */
    protected $pro_visible = true;

    /**
     * Indique si le site fourni une date par annonce.
     * @var bool
     */
    protected $has_date = true;

    /**
     * Indique si la sauvegarde d'annonce est possible.
     * @var bool
     */
    protected $allow_backup = false;

    /**
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }
}