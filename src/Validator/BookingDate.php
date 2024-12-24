<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BookingDate extends Constraint
{
    public string $message = 'Cette chambre est déjà réservée pour les dates sélectionnées.';
}