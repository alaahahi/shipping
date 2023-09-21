<?php

namespace App\Helpers;
use Alkoumi\LaravelArabicTafqeet\Tafqeet;

class help
{

    public static function numberToWords($number)
    {
	    $tafqeetInArabic = Tafqeet::inArabic($number,'usd');
       return $tafqeetInArabic;
    }
    

}