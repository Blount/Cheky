<?php

namespace AdService\Parser;

use AdService\Filter;
use AdService\Ad;

class Olx extends AbstractParser
{
    protected static $months = array(
        "jan" => 1, "fév" => 2, "mars" => 3, "апр." => 4,
        "mai" => 5, "juin" => 6, "juillet" => 7, "août" => 8,
        "sept" => 9, "oct" => 10, "nov" => 11,
        "déc" => 12
    );

    public function process($content, Filter $filter = null) {
        if (!$content) {
            return;
        }

        $content = str_replace("<br/>", " ", $content);
        $this->loadHTML($content);

        $timeToday = strtotime(date("Y-m-d")." 23:59:59");
        $dateYesterday = $timeToday - 24*3600;
        $ads = array();

        $tables = $this->getElementsByTagName("table");
        $tableOffers = null;
        foreach ($tables AS $table) {
            if (false !== strpos($table->getAttribute("id"), "offers_table")) {
                $tableOffers = $table;
                break;
            }
        }
        if (!$tableOffers) {
            return array();
        }
        $adNodes = $tableOffers->getElementsByTagName("td");
        foreach ($adNodes AS $adNode) {
            if (false === strpos($adNode->getAttribute("class"), "offer")) {
                continue;
            }
            $ad = new Ad();
            $ad->setUrgent(false);

            // aucun indicateur pour savoir si c'est un pro ou non.
            $ad->setProfessional(false);

            // permet d'éliminer les annonces déjà envoyées.
            // @todo pour le moment, pas possible. Les IDs ne semblent pas
            // numérique et incrémentals.
//             if ($filter && $m[1] <= $filter->getMinId()) {
//                 continue;
//             }

            $rows = $adNode->getElementsByTagName("tr");
            $columns = $adNode->getElementsByTagName("td");

            $row2_td = $rows->item(1)->getElementsByTagName("td");
            $row2_p = $rows->item(1)->getElementsByTagName("p");

            // analyse de la date
            $dateStr = preg_replace("#\s+#", " ", trim($row2_p->item(1)->nodeValue));
            if (!$dateStr) {
                continue;
            }
            $aDate = explode(' ', $dateStr);
            if (false !== strpos($dateStr, 'Сегодня')) { // aujourd'hui
                $time = strtotime(date("Y-m-d")." 00:00:00");
            } elseif (false !== strpos($dateStr, 'Вчера')) {
                $time = strtotime(date("Y-m-d")." 00:00:00");
                $time = strtotime("-1 day", $time);
            } else {
                if (!isset(self::$months[$aDate[1]])) {
                    continue;
                }
                $time = strtotime(date("Y")."-".self::$months[$aDate[1]]."-".$aDate[0]);
            }
            $timeStr = $aDate[count($aDate) - 1];
            if (false !== $pos = mb_strpos($dateStr, ":")) {
                $time += (int)mb_substr($dateStr, $pos - 2, 2) * 3600;
                $time += (int)mb_substr($dateStr, $pos + 1, 2) * 60;
                if ($timeToday < $time) {
                    $time = strtotime("-1 year", $time);
                }
            }
            $ad->setDate($time);

            // image
            $img = $columns->item(0)->getElementsByTagName("img");
            if ($img->length) {
                $ad->setThumbnailLink(str_replace("94x72", "644x461", $img->item(0)->getAttribute("src")));
            }

            // titre + lien
            $link = $adNode->getElementsByTagName("h3")->item(0)->getElementsByTagName("a")->item(0);
            if ($link) {
                $ad->setTitle(trim($link->nodeValue));
                $ad->setLink($link->getAttribute("href"));
            }

            // urgent
            if (false !== strpos($adNode->nodeValue, "Срочно")) {
                $ad->setUrgent(true);
            }

            // lieu
            $ad->setCity(trim($row2_p->item(0)->nodeValue));

            // catégorie
            $ad->setCategory(trim($columns->item(1)->getElementsByTagName("p")->item(0)->nodeValue));

            if (!preg_match("#ID([^.]+)\.html#", $ad->getLink(), $m)) {
                continue;
            }
            $ad->setId(base_convert($m[1], 32, 10));

            $priceColumn = trim($columns->item(2)->nodeValue);
            if (preg_match('#(?<price>[0-9\s]+)\s+(?<currency>грн|\$|€)#imsU', $priceColumn, $m)) {
                $ad->setPrice((int) str_replace(" ", "", $m["price"]))
                    ->setCurrency($m["currency"]);
            }

            if ($filter && !$filter->isValid($ad)) {
                continue;
            }

            $ads[$ad->getId()] = $ad;
        }

        return $ads;
    }
}
