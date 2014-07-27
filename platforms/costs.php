<?php

abstract class Weight
{
    const WEIGHT_CLASS_450 = 1; // <450g
    const WEIGHT_CLASS_950 = 2; //  450-950g
    const WEIGHT_CLASS_MAX = 3; // >950g

    public static function classes()
    {
        return array(Weight::WEIGHT_CLASS_450, Weight::WEIGHT_CLASS_950, Weight::WEIGHT_CLASS_MAX);
    }
}

abstract class Porto
{
    // Tatsächliche Kosten fürs Porto für die drei Gewichtsklassen 1:<450g, 2:450-950g, 3:>950g.
    private static $porto = array(
        Weight::WEIGHT_CLASS_450 => 0.82,
        Weight::WEIGHT_CLASS_950 => 1.42,
        Weight::WEIGHT_CLASS_MAX => 5.0);

    public static function by($weightClass)
    {
        return self::$porto[$weightClass];
    }
}

const ADDITIONAL_COSTS = 1.0; #geschätzte Kosten fürs Einstellen, Lagern, Raussuchen, Verpacken...
const MWST = 0.07; #Mehrwertsteuer als Anteil von 1. Also: 0.07 eingeben wenns 7% sind.