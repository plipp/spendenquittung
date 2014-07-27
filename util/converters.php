<?php

class Converters {

    public static function toFloat($string_number)
    {
        return floatval(str_replace(',', '.', str_replace('.', '', $string_number)));
    }
} 