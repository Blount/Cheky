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

    public function process($content, Filter $filter = null) {
        if (!$content) {
            return;
        }
        $this->loadHTML($content);

        $timeToday = strtotime(date("Y-m-d")." 23:59:59");
        $dateYesterday = $timeToday - 24*3600;
        $divsAd = $this->getElementsByTagName("div");
        $ads = array();

        foreach ($divsAd AS $result) {
            if (false === strpos($result->getAttribute("class"), "lbc")) {
                continue;
            }
            $ad = new Ad();
            $ad->setProfessional(false)->setUrgent(false);
            $parent = $result->parentNode;
            if ($parent->tagName == "a") {
                $a = $parent;
            } else {
                $aTags = $result->getElementsByTagName("a");
                if (!$aTags->length) {
                    continue;
                }
                $a = $aTags->item(0);
            }
            if (!preg_match('/([0-9]+)\.htm.*/', $a->getAttribute("href"), $m)) {
                continue;
            }

            // permet d'éliminer les annonces déjà envoyées.
            if ($filter && $m[1] <= $filter->getMinId()) {
                continue;
            }

            $ad->setLink($a->getAttribute("href"))
                ->setId($m[1]);

            foreach ($result->getElementsByTagName("div") AS $node) {
                if ($node->hasAttribute("class")) {
                    $class = $node->getAttribute("class");
                    if ($class == "date") {
                        $dateStr = preg_replace("#\s+#", " ", trim($node->nodeValue));
                        $aDate = explode(' ', $dateStr);
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
                    } elseif ($class == "title") {
                        $ad->setTitle(trim($node->nodeValue));
                    } elseif ($class == "image") {
                        $img = $node->getElementsByTagName("img");
                        if ($img->length > 0) {
                            $img = $img->item(0);
                            $ad->setThumbnailLink($img->getAttribute("src"));
                        }
                    } elseif ($class == "placement") {
                        $placement = $node->nodeValue;
                        if (false !== strpos($placement, "/")) {
                            $placement = explode("/", $placement);
                            $ad->setCountry(trim($placement[1]))
                                ->setCity(trim($placement[0]));
                        } else {
                            $ad->setCountry(trim($placement));
                        }
                    } elseif ($class == "category") {
                        $category = $node->nodeValue;
                        if (false !== strpos($category, "(pro)")) {
                            $ad->setProfessional(true);
                        }
                        $ad->setCategory(trim(str_replace("(pro)", "", $category)));
                    } elseif ($class == "price") {
                        if (preg_match("#[0-9 ]+#", $node->nodeValue, $m)) {
                            $ad->setPrice((int)str_replace(" ", "", trim($m[0])));
                        }
                    } elseif ($class == "urgent") {
                        $ad->setUrgent(true);
                    }
                }
            }
            $h2Tags = $result->getElementsByTagName("h2");
            if ($h2Tags->length) {
                $h2 = $h2Tags->item(0);
                $ad->setTitle(trim($h2->nodeValue));
            }

            if ($filter && !$filter->isValid($ad)) {
                continue;
            }

            if ($ad->getDate()) {
                $ads[$ad->getId()] = $ad;
            }
        }
        return $ads;
    }
}
