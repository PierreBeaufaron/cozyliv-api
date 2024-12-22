<?php

namespace App\ApiResource\Enum;

enum Country: string
{
    //  Schengen space countries
    case AUSTRIA = 'Autriche';
    case BELGIUM = 'Belgique';
    case CZECH_REPUBLIC = 'République tchèque';
    case DENMARK = 'Danemark';
    case ESTONIA = 'Estonie';
    case FINLAND = 'Finlande';
    case FRANCE = 'France';
    case GERMANY = 'Allemagne';
    case GREECE = 'Grèce';
    case HUNGARY = 'Hongrie';
    case ICELAND = 'Islande';
    case ITALY = 'Italie';
    case LATVIA = 'Lettonie';
    case LIECHTENSTEIN = 'Liechtenstein';
    case LITHUANIA = 'Lituanie';
    case LUXEMBOURG = 'Luxembourg';
    case MALTA = 'Malte';
    case NETHERLANDS = 'Pays-Bas';
    case NORWAY = 'Norvège';
    case POLAND = 'Pologne';
    case PORTUGAL = 'Portugal';
    case SLOVAKIA = 'Slovaquie';
    case SLOVENIA = 'Slovénie';
    case SPAIN = 'Espagne';
    case SWEDEN = 'Suède';
    case SWITZERLAND = 'Suisse';
    // Other Countries
    case UNITED_KINGDOM = 'Royaume-Uni';

    // Return a list of values
    public static function getChoices(): array
    {
        return array_column(self::cases(), 'value');
    }
}
