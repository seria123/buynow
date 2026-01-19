<?php

namespace App\Models\Catalogue;

enum AttributeType: string
{
    case Predefined = 'predefined';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Predefined => 'Predefined',
            self::Manual => 'Manual',
        };
    }
}
