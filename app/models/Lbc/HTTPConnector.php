<?php

namespace App\Lbc;

use \HttpClientCurl;

class HTTPConnector extends HttpClientCurl
{
    protected $_categories = array(
        "_emploi_" => 71,
        "offres_d_emploi" => 33,
        "_vehicules_" => 1,
        "voitures" => 2,
        "motos" => 3,
        "caravaning" => 4,
        "utilitaires" => 5,
        "equipement_auto" => 6,
        "equipement_moto" => 44,
        "equipement_caravaning" => 50,
        "nautisme" => 7,
        "equipement_nautisme" => 51,
        "_immobilier_" => 8,
        "ventes_immobilieres" => 9,
        "locations" => 10,
        "colocations" => 11,
        "bureaux_commerces" => 13,
        "_vacances_" => 66,
        "locations_gites" => 12,
        "chambres_d_hotes" => 67,
        "campings" => 68,
        "hotels" => 69,
        "hebergements_insolites" => 70,
        "_multimedia_" => 14,
        "informatique" => 15,
        "consoles_jeux_video" => 43,
        "image_son" => 16,
        "telephonie" => 17,
        "_loisirs_" => 24,
        "dvd_films" => 25,
        "cd_musique" => 26,
        "livres" => 27,
        "animaux" => 28,
        "velos" => 55,
        "sports_hobbies" => 29,
        "instruments_de_musique" => 30,
        "collection" => 40,
        "jeux_jouets" => 41,
        "vins_gastronomie" => 48,
        "_materiel_professionnel_" => 56,
        "materiel_agricole" => 57,
        "transport_manutention" => 58,
        "btp_chantier_gros_oeuvre" => 59,
        "outillage_materiaux_2nd_oeuvre" => 60,
        "equipements_industriels" => 32,
        "restauration_hotellerie" => 61,
        "fournitures_de_bureau" => 62,
        "commerces_marches" => 63,
        "materiel_medical" => 64,
        "_services_" => 31,
        "prestations_de_services" => 34,
        "billetterie" => 35,
        "evenements" => 49,
        "cours_particuliers" => 36,
        "covoiturage" => 65,
        "_maison_" => 18,
        "ameublement" => 19,
        "electromenager" => 20,
        "arts_de_la_table" => 45,
        "decoration" => 39,
        "linge_de_maison" => 46,
        "bricolage" => 21,
        "jardinage" => 52,
        "vetements" => 22,
        "chaussures" => 53,
        "accessoires_bagagerie" => 47,
        "montres_bijoux" => 42,
        "equipement_bebe" => 23,
        "vetements_bebe" => 54,
        "__" => 37,
        "autres" => 38,
    );

    protected $_regions = array(
        "alsace" => 1,
        "aquitaine" => 2,
        "auvergne" => 3,
        "basse_normandie" => 4,
        "bourgogne" => 5,
        "bretagne" => 6,
        "centre" => 7,
        "champagne_ardenne" => 8,
        "corse" => 9,
        "franche_comte" => 10,
        "haute_normandie" => 11,
        "ile_de_france" => 12,
        "languedoc_roussillon" => 13,
        "limousin" => 14,
        "lorraine" => 15,
        "midi_pyrenees" => 16,
        "nord_pas_de_calais" => 17,
        "pays_de_la_loire" => 18,
        "picardie" => 19,
        "poitou_charentes" => 20,
        "provence_alpes_cote_d_azur" => 21,
        "rhone_alpes" => 22,
        "guadeloupe" => 23,
        "martinique" => 24,
        "guyane" => 25,
        "reunion" => 26,
    );

    protected $_departments = array(
        "ain" => 1,
        "aisne" => 2,
        "allier" => 3,
        "alpes_de_haute_provence" => 4,
        "hautes_alpes" => 5,
        "alpes_maritimes" => 6,
        "ardeche" => 7,
        "ardennes" => 8,
        "ariege" => 9,
        "aube" => 10,
        "aude" => 11,
        "aveyron" => 12,
        "bouches_du_rhone" => 13,
        "calvados" => 14,
        "cantal" => 15,
        "charente" => 16,
        "charente_maritime" => 17,
        "cher" => 18,
        "correze" => 19,
        "cote_d_or" => 21,
        "cotes_d_armor" => 22,
        "creuse" => 23,
        "dordogne" => 24,
        "doubs" => 25,
        "drome" => 26,
        "eure" => 27,
        "eure_et_loir" => 28,
        "finistere" => 29,
        "gard" => 30,
        "haute_garonne" => 31,
        "gers" => 32,
        "gironde" => 33,
        "herault" => 34,
        "ille_et_vilaine" => 35,
        "indre" => 36,
        "indre_et_loire" => 37,
        "isere" => 38,
        "jura" => 39,
        "landes" => 40,
        "loir_et_cher" => 41,
        "loire" => 42,
        "haute_loire" => 43,
        "loire_atlantique" => 44,
        "loiret" => 45,
        "lot" => 46,
        "lot_et_garonne" => 47,
        "lozere" => 48,
        "maine_et_loire" => 49,
        "manche" => 50,
        "marne" => 51,
        "haute_marne" => 52,
        "mayenne" => 53,
        "meurthe_et_moselle" => 54,
        "meuse" => 55,
        "morbihan" => 56,
        "moselle" => 57,
        "nievre" => 58,
        "nord" => 59,
        "oise" => 60,
        "orne" => 61,
        "pas_de_calais" => 62,
        "puy_de_dome" => 63,
        "pyrenees_atlantiques" => 64,
        "hautes_pyrenees" => 65,
        "pyrenees_orientales" => 66,
        "bas_rhin" => 67,
        "haut_rhin" => 68,
        "rhone" => 69,
        "haute_saone" => 70,
        "saone_et_loire" => 71,
        "sarthe" => 72,
        "savoie" => 73,
        "haute_savoie" => 74,
        "paris" => 75,
        "seine_maritime" => 76,
        "seine_et_marne" => 77,
        "yvelines" => 78,
        "deux_sevres" => 79,
        "somme" => 80,
        "tarn" => 81,
        "tarn_et_garonne" => 82,
        "var" => 83,
        "vaucluse" => 84,
        "vendee" => 85,
        "vienne" => 86,
        "haute_vienne" => 87,
        "vosges" => 88,
        "yonne" => 89,
        "territoire_de_belfort" => 90,
        "essonne" => 91,
        "hauts_de_seine" => 92,
        "seine_saint_denis" => 93,
        "val_de_marne" => 94,
        "val_d_oise" => 95,
    );


    public function request($url = null)
    {
        if (!$this->_url && !$url) {
            throw new Exception("Aucune URL à appeler.");
        }

        if ($url) {
            $this->setUrl($url);
        }

        $this->removeHeader("Content-Length");

        if ($options = $this->urlToApi($this->_url)) {
            $data = str_replace("[]", "{}", json_encode($options));
            $this->setHeader("Content-Length", strlen($data));
            curl_setopt($this->_resource, CURLOPT_POSTFIELDS, $data);
        }

        if (false === $options) {
            return "";
        }

        $this->_referer = $this->_url;

        $response = parent::request();

        return $response;
    }

    public function urlToApi($url)
    {
        if (preg_match("#.+/([0-9]+)\.htm/?$#", $url, $m)) {
            $this->_url = "https://api.leboncoin.fr/finder/classified/".$m[1];
            $this->setMethod(HttpClientCurl::METHOD_GET);
            return array();
        }

        $this->setMethod(HttpClientCurl::METHOD_POST);

        $this->_url = "https://api.leboncoin.fr/finder/search";

        if (false === strpos($url, "?")) {
            $query_string = array();

            if (preg_match("#/annonces/offres/(?<region>[^/]+)/?$#", $url, $m)) {
                // Toutes catégories dans une région
                if (isset($this->_regions[$m["region"]])) {
                    $query_string["regions"] = $this->_regions[$m["region"]];
                }

            } elseif (preg_match("#/annonces/offres/(?<region>[^/]+)/(?<department>[^/]+)/?$#", $url, $m)) {
                // Toutes catégories dans un département
                if (isset($this->_regions[$m["region"]])) {
                    $query_string["regions"] = $this->_regions[$m["region"]];
                }
                if (isset($this->_departments[$m["department"]])) {
                    $query_string["departments"] = $this->_departments[$m["department"]];
                }

            } elseif (preg_match("#/(?<category>[^/]+)/offres/?$#", $url, $m)) {
                // Une catégorie dans toute la France
                if (isset($this->_categories[$m["category"]])) {
                    $query_string["category"] = $this->_categories[$m["category"]];
                }

            } elseif (preg_match("#/(?<category>[^/]+)/offres/(?<region>[^/]+)/?$#", $url, $m)) {
                // Une catégorie dans une région
                if (isset($this->_regions[$m["region"]])) {
                    $query_string["regions"] = $this->_regions[$m["region"]];
                }
                if (isset($this->_categories[$m["category"]])) {
                    $query_string["category"] = $this->_categories[$m["category"]];
                }

            } elseif (preg_match("#/(?<category>[^/]+)/offres/(?<region>[^/]+)/(?<department>[^/]+)/?$#", $url, $m)) {
                // Une catégorie dans un departement
                if (isset($this->_regions[$m["region"]])) {
                    $query_string["regions"] = $this->_regions[$m["region"]];
                }
                if (isset($this->_categories[$m["category"]])) {
                    $query_string["category"] = $this->_categories[$m["category"]];
                }
                if (isset($this->_departments[$m["department"]])) {
                    $query_string["departments"] = $this->_departments[$m["department"]];
                }
            }

        } else {
            $query_string = parse_url($url, PHP_URL_QUERY);
            parse_str($query_string, $query_string);
        }

        if (empty($query_string)) {
            $this->_respond_code = 400;
            return false;
        }

        $ad_type = "offer";
        if (false !== strpos($url, "/demandes/")) {
            $ad_type = "demand";
        }

        $category = array();
        if (!empty($query_string["category"])) {
            $category["id"] = (string) $query_string["category"];
        }

        $keywords = array();
        if (!empty($query_string["text"])) {
            $keywords["text"] = $query_string["text"];
        }

        if (!empty($query_string["search_in"]) && "subject" == $query_string["search_in"]) {
            $keywords["type"] = $query_string["search_in"];
        }

        $location = array();

        if (!empty($query_string["region"])) {
            $query_string["regions"] = $query_string["region"];
        }
        if (!empty($query_string["departement"])) {
            $query_string["departments"] = $query_string["departement"];
        }
        unset($query_string["region"], $query_string["departement"]);

        if (!empty($query_string["regions"])) {
            $location["regions"] = explode(",", $query_string["regions"]);
        }
        if (!empty($query_string["region_near"])) {
            $location["region_near"] = true;
        }
        if (!empty($query_string["department_near"])) {
            $location["department_near"] = true;
        }
        if (!empty($query_string["departments"])) {
            $location["departments"] = explode(",", $query_string["departments"]);
        }
        if (!empty($query_string["lat"])) {
            $location["area"]["lat"] = (float) $query_string["lat"];
        }
        if (!empty($query_string["lng"])) {
            $location["area"]["lng"] = (float) $query_string["lng"];
        }
        if (!empty($query_string["radius"])) {
            $location["area"]["radius"] = (float) $query_string["radius"];
        }
        if (!empty($query_string["location"]) && empty($query_string["cities"])) {
            $query_string["cities"] = $query_string["location"];
            unset($query_string["location"]);
        }
        if (!empty($query_string["cities"])) {
            $cities = explode(",", $query_string["cities"]);
            $options_cities = array();
            foreach ($cities AS $city) {
                if (0 === strpos($city, "d_")) {
                    $id = (int) substr($city, 2);
                    $options_cities[] = array(
                        "department_id" => (string) $id,
                        "locationType" => "department",
                    );

                } elseif (0 === strpos($city, "rn_")) {
                    $id = (int) substr($city, 3);
                    $options_cities[] = array(
                        "region_id" => (string) $id,
                        "locationType" => "region_near",
                    );

                } elseif (0 === strpos($city, "r_")) {
                    $id = (int) substr($city, 2);
                    $options_cities[] = array(
                        "region_id" => (string) $id,
                        "locationType" => "region",
                    );

                } elseif (5 == strlen($city)) {
                    $options_cities[] = array(
                        "label" => "Toutes les communes ".$city,
                        "zipcode" => $city,
                        "locationType" => "city",
                    );

                } else {
                    $city = explode("_", $city);
                    $options_cities[] = array(
                        "city" => $city[0],
                        "label" => $city[0]." (".$city[1].")",
                        "zipcode" => $city[1],
                        "locationType" => "city",
                    );
                }
            }
            $location["city_zipcodes"] = $options_cities;
        }

        $options = array(
            "filters" => array(
                "category" => $category,
                "enums" => array(
                    "ad_type" => array(
                        0 => $ad_type,
                    ),
                ),
                "keywords" => $keywords,
                "location" => $location,
                "ranges" => array(),
            ),
            "limit" => 35,
            "limit_alu" => 3,
        );

        if (!empty($query_string["owner_type"])) {
            $options["owner_type"] = $query_string["owner_type"];
        }

        if (!empty($query_string["sort"])) {
            $options["sort_by"] = $query_string["sort"];
        }

        if (!empty($query_string["order"])) {
            $options["sort_order"] = $query_string["order"];
        }

        unset(
            $query_string["text"],
            $query_string["category"],
            $query_string["search_in"],
            $query_string["regions"],
            $query_string["region_near"],
            $query_string["region_near"],
            $query_string["departments"],
            $query_string["department_near"],
            $query_string["cities"],
            $query_string["lat"],
            $query_string["lng"],
            $query_string["radius"],
            $query_string["owner_type"],
            $query_string["sort"],
            $query_string["order"]
        );

        if (!empty($query_string)) {
            foreach ($query_string AS $key => $value) {
                if (false !== strpos($value, "-")
                    && preg_match("#(?:min|[0-9+])-(?:max|[0-9+])#", $value)
                ) {
                    $value = array_map(function ($v) {
                        if (is_numeric($v)) {
                            return (float) $v;
                        }
                        return $v;
                    }, explode("-", $value));
                    $data = array();
                    if ("min" != $value[0]) {
                        $data["min"] = $value[0];
                    }
                    if ("max" != $value[1]) {
                        $data["max"] = $value[1];
                    }
                    $options["filters"]["ranges"][$key] = $data;

                } else {
                    $options["filters"]["enums"][$key] = explode(",", $value);
                }
            }
        }

        return $options;
    }
}
