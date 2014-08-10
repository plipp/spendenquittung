<?php

class Converters {

    public static function toFloat($string_number)
    {
        return floatval(str_replace(',', '.', str_replace('.', '', $string_number)));
    }

    public static function toCurrencyString($number)
    {
        return number_format($number, 2, ',', ' ');
    }
}