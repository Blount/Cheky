<?php

namespace AdService\Parser;

use AdService\Filter;
use AdService\Ad;

class Seloger extends AbstractParser
{
    public function process($content, Filter $filter = null) {
        if (!$content) {
            return;
        }

        $this->loadHTML('<?xml encoding="UTF-8">'.$content);

        $timeToday = strtotime(date("Y-m-d")." 23:59:59");
        $dateYesterday = $timeToday - 24*3600;
        $ads = array();

        $sections = $this->getElementsByTagName("section");
        $section_results = null;
        foreach ($sections AS $section) {
            if (false !== strpos($section->getAttribute("class"), "liste_resultat")) {
                $section_results = $section;
                break;
            }
        }
        if (!$section_results) {
            return array();
        }

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

        $adNodes = $section_results->getElementsByTagName("div");
        foreach ($adNodes AS $adNode) {
            if (!$id = (int) $adNode->getAttribute("data-listing-id")) {
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
                    if ($id == $exclude_ids) {
                        break;
                    }

                } elseif (in_array($id, $exclude_ids)) {
                    continue;
                }
            }

            $ad = new Ad();
            $ad->setUrgent(false)
                ->setId($id);

            // aucun indicateur pour savoir si c'est un pro ou non.
            $ad->setProfessional(false);

            // Titre + lien
            $links = $adNode->getElementsByTagName("a");
            foreach ($links AS $link) {
                if (false !== strpos($link->getAttribute("class"), "c-pa-link")) {
                    $ad->setTitle(trim($link->getAttribute("title")));
                    $ad->setLink($link->getAttribute("href"));
                    break;
                }
            }

            $nodes = $adNode->getElementsByTagName("div");
            foreach ($nodes AS $node) {
                $className = trim($node->getAttribute("class"));
                $parentNode = $node->parentNode;

                // Image
                if (false !== strpos($className, "c-pa-imgs")) {
                    $div_for_imgs = $node->getElementsByTagName("div");
                    foreach ($div_for_imgs AS $div_img) {
                        if ($data_image = trim($div_img->getAttribute("data-lazy"))) {
                            $data_image = json_decode($data_image, true);
                            if (is_array($data_image) && !empty($data_image["url"])) {
                                $ad->setThumbnailLink($data_image["url"]);
                            }
                        }
                    }
                }

                if (false === strpos($parentNode->getAttribute("class"), "c-pa-info")) {
                    continue;
                }

                // Titre + lien
                if (false !== strpos($className, "c-pa-price")) {
                    $value = htmlentities((string) $node->nodeValue, null, "UTF-8");
                    if (preg_match("#([0-9]+(".preg_quote("&nbsp;", "#").")?[0-9]*)+#", $value, $m)) {
                        $ad->setPrice((int) preg_replace("#[^0-9]*#", "", $m[1]));
                    }

                // Lieu
                } elseif (false !== strpos($className, "c-pa-city")) {
                    $ad->setCity(trim($node->nodeValue));
                }
            }

            if ($filter && !$filter->isValid($ad)) {
                continue;
            }

            $ads[$ad->getId()] = $ad;
        }
        return $ads;
    }
}
