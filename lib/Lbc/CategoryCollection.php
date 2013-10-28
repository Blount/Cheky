<?php

namespace Lbc;

class CategoryCollection
{
    protected $_categories = array(
        "Véhicules" => array(
            2 => "Voitures",
            3 => "Motos",
            4 => "Caravaning",
            5 => "Utilitaires",
            6 => "Equipement Auto",
            44 => "Equipement Moto",
            50 => "Equipement Caravaning",
            7 => "Nautisme",
            51 => "Equipement Nautisme"
        ),
        "Immobilier" => array(
            9 => "Ventes immobilières",
            10 => "Locations",
            11 => "Colocations",
            12 => "Locations de vacances",
            13 => "Bureaux & Commerces"
        ),
        "Multimedia" => array(
            15 => "Informatique",
            43 => "Consoles & Jeux vidéo",
            16 => "Image & Son",
            17 => "Téléphonie"
        ),
        "Maison" => array(
            19 => "Ameublement",
            20 => "Electroménager",
            45 => "Arts de la table",
            39 => "Décoration",
            46 => "Linge de maison",
            21 => "Bricolage",
            52 => "Jardinage",
            22 => "Vêtements",
            53 => "Chaussures",
            47 => "Accessoires & Bagagerie",
            42 => "Montres & Bijoux",
            23 => "Equipement bébé",
            54 => "Vêtements bébé"
        ),
        "Loisirs" => array(
            25 => "DVD / Films",
            26 => "CD / Musique",
            27 => "Livres",
            28 => "Animaux",
            55 => "Vélos",
            29 => "Sports & Hobbies",
            30 => "Instruments de musique",
            40 => "Collection",
            41 => "Jeux & Jouets",
            48 => "Vins & Gastronomie"
        ),
        "Matériel professionnel" => array(
            57 => "Matériel Agricole",
            58 => "Transport - Manutention",
            59 => "BTP - Chantier Gros-oeuvre",
            60 => "Outillage - Matériaux 2nd-oeuvre",
            32 => "Équipements Industriels",
            61 => "Restauration - Hôtellerie",
            62 => "Fournitures de Bureau",
            63 => "Commerces & Marchés",
            64 => "Matériel Médical"
        ),
        "Emploi & Services" => array(
            33 => "Emploi",
            34 => "Services",
            35 => "Billetterie",
            49 => "Evénements",
            36 => "Cours particuliers"
        ),
        "--" => array(
            38 => "Autres"
        )
    );

    public function fetchAll()
    {
        return $this->_categories;
    }
}