<?php

namespace AdService\Parser;

use AdService\Filter;
use AdService\Ad;

class Lbc extends AbstractParser
{
    protected static $months = array(
        "jan" => 1, "fév" => 2, "mars" => 3, "avr" => 4,
        "mai" => 5, "juin" => 6, "juillet" => 7, "août" => 8,
        "sept" => 9, "oct" => 10, "nov" => 11,
        "déc" => 12
    );

    protected $scheme;

    public function process($content, Filter $filter = null, $scheme = "http") {
        if (!$content) {
            return;
        }
        $this->scheme = $scheme;
        $this->loadHTML('<?xml encoding="UTF-8">'.$content);

        $timeToday = strtotime(date("Y-m-d")." 23:59:59");
        $dateYesterday = $timeToday - 24*3600;
        $ads = array();

        if ($filter) {
            $exclude_ids = $filter->getExcludeIds();

            /**
             * Afin de garder une rétrocompatibilité, on prend en compte
             * que $exclude_ids peut être numérique.
             */
            if (!is_numeric($exclude_ids) && !is_array($exclude_ids)) {
                unset($exclude_ids);
            }
        }

        $adNodes = $this->getElementsByTagName("a");

        foreach ($adNodes AS $result) {
            // est-ce bien une annonce ?
            if (false === strpos($result->getAttribute("class"), "list_item")) {
                continue;
            }

            $ad = new Ad();
            $ad->setProfessional(false)->setUrgent(false);

            // pas d'ID, pas d'annonce
            if (!preg_match('/([0-9]+)\.htm.*/', $result->getAttribute("href"), $m)) {
                continue;
            }

            // permet d'éliminer les annonces déjà envoyées.
            if (isset($exclude_ids)) {
                if (is_numeric($exclude_ids)) {
                    /**
                     * Si $exclude_ids est numérique, alors détection
                     * à l'ancienne. Quand on rencontre l'ID de la
                     * dernière annonce, on stoppe la boucle.
                     */
                    if ($m[1] == $exclude_ids) {
                        break;
                    }

                } elseif (in_array($m[1], $exclude_ids)) {
                    continue;
                }
            }

            // permet d'éliminer les annonces déjà envoyées.
            if ($filter && $m[1] <= $filter->getMinId()) {
                continue;
            }

            $ad->setLink($this->formatLink($result->getAttribute("href")))
                ->setId($m[1])
                ->setTitle($result->getAttribute("title"))
                ->setLinkMobile(str_replace(
                    array("http://www.", "https://www."),
                    array("http://mobile.", "https://mobile."),
                    $ad->getLink()
                ));

            // recherche de l'image
            foreach ($result->getElementsByTagName("span") AS $node) {
                if ($src = $node->getAttribute("data-imgsrc")) {
                    $ad->setThumbnailLink($this->formatLink($src));
                }
            }

            $i = 0;
            foreach ($result->getElementsByTagName("p") AS $node) {
                $class = (string) $node->getAttribute("class");
                if (false !== strpos($class, "item_supp")) {
                    $value = trim($node->nodeValue);
                    if ($i == 0) { // catégorie
                        if (false !== strpos($value, "(pro)")) {
                            $ad->setProfessional(true);
                        }
                        $ad->setCategory(trim(str_replace("(pro)", "", $value)));

                    } elseif ($i == 1) { // localisation
                        if (false !== strpos($value, "/")) {
                            $value = explode("/", $value);
                            $ad->setCountry(trim($value[1]))
                                ->setCity(trim($value[0]));
                        } else {
                            $ad->setCountry(trim($value));
                        }

                    } elseif ($i == 2) { // date de l'annonce + urgent
                        $spans = $node->getElementsByTagName("span");
                        if ($spans->length > 0) {
                            $ad->setUrgent(true);
                            $node->removeChild($spans->item(0));
                            $value = trim($node->nodeValue);
                        }

                        $dateStr = preg_replace("#\s+#", " ", $value);
                        $aDate = explode(' ', $dateStr);
                        $aDate[1] = trim($aDate[1], ",");
                        if (false !== strpos($dateStr, 'Aujourd')) {
                            $time = strtotime(date("Y-m-d")." 00:00:00");
                        } elseif (false !== strpos($dateStr, 'Hier')) {
                            $time = strtotime(date("Y-m-d")." 00:00:00");
                            $time = strtotime("-1 day", $time);
                        } else {
                            if (!isset(self::$months[$aDate[1]])) {
                                continue;
                            }
                            $time = strtotime(date("Y")."-".self::$months[$aDate[1]]."-".$aDate[0]);
                        }
                        $aTime = explode(":", $aDate[count($aDate) - 1]);
                        $time += (int)$aTime[0] * 3600 + (int)$aTime[1] * 60;
                        if ($timeToday < $time) {
                            $time = strtotime("-1 year", $time);
                        }
                        $ad->setDate($time);
                    }
                    $i++;
                }
            }

            // recherche du prix
            foreach ($result->getElementsByTagName("h3") AS $node) {
                $class = (string) $node->getAttribute("class");
                if (false !== strpos($class, "item_price")) {
                    if (preg_match("#[0-9 ]+#", $node->nodeValue, $m)) {
                        $ad->setPrice((int)str_replace(" ", "", trim($m[0])));
                    }
                }
            }

            // exclure les annonces ne correspondant pas au filtre.
            if ($filter && !$filter->isValid($ad)) {
                continue;
            }

            $ads[$ad->getId()] = $ad;
        }

        return $ads;
    }

    /**
     * Analyse une fiche d'annonce.
     * @return Ad
     */
    public function processAd($content, $scheme = "http")
    {
        $this->loadHTML('<?xml encoding="UTF-8">'.$content);
        $this->scheme = $scheme;

        // Recherche du conteneur principal
        $sections = $this->getElementsByTagName("section");
        $container = null;
        foreach ($sections AS $section) {
            if (false !== strpos((string) $section->getAttribute("class"), "adview")) {
                $container = $section;
                break;
            }
        }

        // Ca ne semble pas une annonce valide
        if (!$container) {
            return null;
        }

        $ad = new Ad();
        $ad->setProfessional(false)->setUrgent(false);

        // Lien vers l'annonce
        $links = $this->getElementsByTagName("link");
        foreach ($links AS $link) {
            if ("canonical" == $link->getAttribute("rel")) {
                $ad->setLink($this->formatLink($link->getAttribute("href")))
                    ->setLinkMobile(str_replace(
                        array("http://www.", "https://www."),
                        array("http://mobile.", "https://mobile."),
                        $ad->getLink()
                    ));
            }
        }

        // pas d'ID, pas d'annonce
        if (!preg_match('/([0-9]+)\.htm.*/', $ad->getLink(), $m)) {
            return null;
        }
        $ad->setId($m[1]);

        // Catégorie
        $navs = $this->getElementsByTagName("nav");
        foreach ($navs AS $nav) {
            if (false !== strpos($nav->getAttribute("class"), "breadcrumbsNav")) {
                $li = $nav->getElementsByTagName("li")->item(2);
                if ($li) {
                    $ad->setCategory(trim($li->nodeValue));
                }
            }
        }

        // Date de publication
        if (preg_match("#publish_date\s*:\s*\"([0-9]{2})/([0-9]{2})/([0-9]{4})\"#", $content, $m)) {
            $ad->setDate($m[3]."-".$m[2]."-".$m[1]);
        }

        // Récupération des images
        $scripts = $container->getElementsByTagName("script");
        foreach ($scripts AS $script) {
            if (preg_match_all("#images\[[0-9]+\]\s*=\s*\"([^\"]+)\"\s*;#imsU", $script->nodeValue, $images)) {
                $photos = array();
                foreach ($images[1] AS $image) {
                    $image = $this->formatLink($image);
                    $photos[] = array(
                        "remote" => $image,
                        "local" => sha1($image).".jpg",
                    );
                }
                $ad->setPhotos($photos);
            }
        }

        // Urgent
        $ad->setUrgent(false !== strpos($content, "urgent : \"1\""));

        $elements = $container->getElementsByTagName("*");
        foreach ($elements AS $element) {
            $itemprop = $element->getAttribute("itemprop");
            $tag = strtolower($element->tagName);

            // Titre
            if ($tag == "h1") {
                $ad->setTitle(trim($element->nodeValue));
                continue;
            }

            // Pohoto
            if ($tag == "div"
                && !$ad->getPhotos()
                && $value = $element->getAttribute("data-popin-content")
            ) {
                $image = $this->formatLink($value);
                $ad->setPhotos(array(array(
                    "remote" => $image,
                    "local" => sha1($image).".jpg",
                )));
                continue;
            }

            // Adresse
            if ($itemprop == "address") {
                if (preg_match("#(.*)\s+([0-9]{5})#", trim($element->nodeValue), $m)) {
                    $ad->setCity($m[1])
                        ->setZipCode($m[2]);
                }
                continue;
            }

            // Prix
            if ($itemprop == "price") {
                $ad->setPrice($element->getAttribute("content"));
                continue;
            }

            // Contenu
            if ($itemprop == "description") {
                $description = "";
                foreach ($element->childNodes AS $sub_element) {
                    if (isset($sub_element->tagName) && "br" == $sub_element->tagName) {
                        $description .= "\n";
                        continue;
                    }
                    $description .= $sub_element->nodeValue;
                }
                $ad->setDescription($description);
                continue;
            }

            // PRO
            if ("ispro" == $element->getAttribute("class")) {
                $ad->setProfessional(true);
                continue;
            }

            // Auteur
            if ($tag == "a"
                && false !== strpos($element->getAttribute("data-info"), "email::pseudo_annonceur")) {
                $ad->setAuthor(trim($element->nodeValue));
                continue;
            }

            // Autre propriété
            if ("property" == $element->getAttribute("class")) {
                $name = trim($element->nodeValue);
                if ("Prix" == $name || "Ville" == $name) {
                    continue;
                }
                $value = trim($element->nextSibling->nextSibling->nodeValue);
                $ad->addProperty($name, $value);
            }
        }

        return $ad;
    }

    protected function formatLink($link)
    {
        if (0 === strpos($link, "//")) {
            $link = $this->scheme.":".$link;
        }
        return $link;
    }
}
