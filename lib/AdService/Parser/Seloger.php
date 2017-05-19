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

        $adNodes = $section_results->getElementsByTagName("article");
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

            // image
            $imgs = $adNode->getElementsByTagName("img");
            if ($imgs->length) {
                foreach ($imgs AS $img) {
                    if (false !== strpos($img->getAttribute("class"), "listing_photo")) {
                        $ad->setThumbnailLink(
                            str_replace(
                                array("c175", "c250"),
                                "b600",
                                $img->getAttribute("src"))
                        );
                        break;
                    }
                }
            }

            $nodes = $adNode->getElementsByTagName("div");
            foreach ($nodes AS $node) {
                $className = trim($node->getAttribute("class"));

                // Titre + lien
                if (false !== strpos($className, "title")) {
                    $link = $node->getElementsByTagName("a")->item(0);
                    $ad->setTitle(trim($node->nodeValue));
                    $ad->setLink($link->getAttribute("href"));

                // Prix
                } elseif (false !== strpos($className, "price")) {
                    $value = htmlentities((string) $node->nodeValue, null, "UTF-8");
                    if (preg_match("#([0-9]+(".preg_quote("&nbsp;", "#").")?[0-9]*)+#", $value, $m)) {
                        $ad->setPrice((int) preg_replace("#[^0-9]*#", "", $m[1]));
                    }

                // Lieu
                } elseif (false !== strpos($className, "locality")) {
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
