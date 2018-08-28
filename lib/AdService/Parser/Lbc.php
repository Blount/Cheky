<?php

namespace AdService\Parser;

use AdService\Filter;
use AdService\Ad;

class Lbc extends AbstractParser
{
    protected $scheme;

    public function process($content, Filter $filter = null, $scheme = "http") {
        if (!$content) {
            return;
        }
        $this->scheme = $scheme;

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


        $datas = json_decode($content, true);
        if (!$datas || !is_array($datas) || !$datas["ads"]) {
            return $ads;
        }

        foreach ($datas["ads"] AS $data) {
            if (!isset($data["list_id"])) {
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
                    if ($data["list_id"] == $exclude_ids) {
                        break;
                    }

                } elseif (in_array($data["list_id"], $exclude_ids)) {
                    continue;
                }
            }

            // permet d'éliminer les annonces déjà envoyées.
            if ($filter && $data["list_id"] <= $filter->getMinId()) {
                continue;
            }

            $ad = new Ad();
            $ad->setProfessional(false)
               ->setUrgent(false);

            $ad->setId($data["list_id"]);

            if (isset($data["url"])) {
                $ad->setLink($data["url"]);
            }

            if (isset($data["index_date"])) {
                $ad->setDate(strtotime($data["index_date"]));
            }

            if (isset($data["subject"])) {
                $ad->setTitle($data["subject"]);
            }

            if (isset($data["category_name"])) {
                $ad->setCategory($data["category_name"]);
            }

            if (isset($data["price"][0])) {
                $ad->setPrice($data["price"][0]);
            }

            if (isset($data["owner"]["type"])
                && "pro" == $data["owner"]["type"]
            ) {
                $ad->setProfessional(true);
            }

            if (isset($data["options"]["urgent"])) {
                $ad->setUrgent($data["options"]["urgent"]);
            }

            if (isset($data["location"]["department_name"])) {
                $ad->setCountry($data["location"]["department_name"]);
            }

            if (isset($data["location"]["city"])) {
                $ad->setCity($data["location"]["city"]);
            }

            if (isset($data["images"]["urls"][0])) {
                $ad->setThumbnailLink($data["images"]["urls"][0]);
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
        $ad = new Ad();
        $ad->setProfessional(false)->setUrgent(false);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return null;
        }

        if (isset($data["first_publication_date"])) {
            $ad->setDate($data["first_publication_date"]);
        }

        if (isset($data["list_id"])) {
            $ad->setId($data["list_id"]);
        }

        if (isset($data["url"])) {
            $ad->setLink($data["url"]);
        }

        if (isset($data["category_name"])) {
            $ad->setCategory($data["category_name"]);
        }

        if (isset($data["subject"])) {
            $ad->setTitle($data["subject"]);
        }

        if (isset($data["body"])) {
            $ad->setDescription($data["body"]);
        }

        if (isset($data["location"]["city"])) {
            $ad->setCity($data["location"]["city"]);
        }

        if (isset($data["location"]["zipcode"])) {
            $ad->setZipCode($data["location"]["zipcode"]);
        }

        if (isset($data["images"]["urls_large"])) {
            $images = $data["images"]["urls_large"];

        } elseif (isset($data["images"]["urls"])) {
            $images = $data["images"]["urls"];
        }

        if (!empty($images)) {
            $photos = array();
            foreach ($images AS $image) {
                $image = $this->formatLink($image);
                $photos[] = array(
                    "remote" => $image,
                    "local" => sha1($image).".jpg",
                );
            }
            $ad->setPhotos($photos);
        }

        if (!empty($data["options"]["urgent"])) {
            $ad->setUrgent(true);
        }

        if (isset($data["owner"]["name"])) {
            $ad->setAuthor($data["owner"]["name"]);
        }

        if (isset($data["price"][0])) {
            $ad->setPrice($data["price"][0]);
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
