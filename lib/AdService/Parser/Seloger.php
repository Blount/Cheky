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

        // pourquoi est-ce nécessaire ?! Je n'ai pas encore trouvé la raison.
        $content = utf8_decode($content);

        $this->loadHTML($content);

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
        $adNodes = $section_results->getElementsByTagName("article");
        foreach ($adNodes AS $adNode) {
            if (!$id = (int) $adNode->getAttribute("data-listing-id")) {
                continue;
            }
//             if ($id == 106823053) {
//                 var_dump($id);
//             }

            // permet d'éliminer les annonces déjà envoyées.
            if ($filter && $id == $filter->getLastId()) {
                break;
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

            // titre + lien + lieu
            $link = $adNode->getElementsByTagName("h2")->item(0)
                ->getElementsByTagName("a")->item(0);
            if ($link) {
                $city = $link->getElementsByTagName("span")->item(0);
                if ($city) {
                    // lieu
                    $ad->setCity(trim($city->nodeValue));
                }
                $ad->setTitle(trim($link->nodeValue));
                $ad->setLink($link->getAttribute("href"));
            }

            $links = $adNode->getElementsByTagName("a");
            if ($links->length) {
                foreach ($links AS $link) {
                    $classCSS = $link->getAttribute("class");
                    if (false !== strpos($classCSS, "amount")) {
                        $ad->setPrice((int) preg_replace("#[^0-9]*#", "", $link->nodeValue));
                    }
                }
            }

            if ($filter && !$filter->isValid($ad)) {
                continue;
            }

            $ads[$ad->getId()] = $ad;
        }
//         var_dump($ads[174267]);
//         exit;
        return $ads;
    }
}
