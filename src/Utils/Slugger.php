<?php

namespace App\Utils;

class Slugger {

    private $toLower;

    public function __construct($toLower) 
    {
        $this->toLower = $toLower;
    }

    public function slugify(string $strToConvert){

        if($this->toLower){
            $strToConvert = strtolower($strToConvert);
        }
        
        $convertedString = preg_replace( '/[^a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*/', '-', trim(strip_tags($strToConvert)) ); 

        return $convertedString;
    }
}
