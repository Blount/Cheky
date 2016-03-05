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
        $this->loadHTML($content);

        $timeToday = strtotime(date("Y-m-d")." 23:59:59");
        $dateYesterday = $timeToday - 24*3600;
        $ads = array();

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

            /**
             * @todo si l'annonce est supprimée, risque de renvoie de toutes
             * les annonces. Il faudrait plutôt sauvegarder les IDs x
             * derniers IDs et faire un filtre : exlude_ids
             */
            if ($filter && in_array($m[1], $filter->getLastIds())) {
                continue;
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

    protected function formatLink($link)
    {
        if (0 === strpos($link, "//")) {
            $link = $this->scheme.":".$link;
        }
        return $link;
    }
}
