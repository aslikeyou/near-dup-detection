<?php

class Ngram {
    private $value;
    private $ID;
    function __construct($value)
    {
        $this->value = $value;
        $this->ID = self::hash($value);
       // echo self::hash($value)."\n";
       // die();
    }

    private static function c_mul($a,$b) {
        //return (intval($a) * $b);
        return intval($a) * $b & 0xFFFFFFFF;
    }

    private static function hashCalc($str) {
        $str = str_split($str);
        $value = ord($str[0]) << 7;
        //echo $value.'<br>';
        foreach($str as $char) {
            $value = (self::c_mul(1000003, $value) ^ ord($char));
        }
        //return $value;
        return $value ^ count($str);
    }


    public static function hash($str) {
        return self::hashCalc($str);
        //return crc32($str);
     /*   $crc64 = ( '0x' . hash('crc32', $str) . hash('crc32b', $str) );
        return $crc64 + 0;*/
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }




}