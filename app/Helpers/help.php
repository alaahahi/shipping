<?php

namespace App\Helpers;
use Alkoumi\LaravelArabicTafqeet\Tafqeet;

class help
{

    public static function numberToWords($number,$currency='usd')
    {
        if($currency=='$'){
            $currency='usd';
        }
        if($currency=='IQD'){
            $currency='iqd';
        }
        return ($currency);
	    $tafqeetInArabic = Tafqeet::inArabic($number,$currency);
       return $tafqeetInArabic;
    }
    

}