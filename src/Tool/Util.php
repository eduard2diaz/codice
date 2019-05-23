<?php
/**
 * Created by PhpStorm.
 * User: eduardo
 * Date: 11/05/19
 * Time: 22:31
 */

namespace App\Tool;


class  Util
{
    public static function esTelefonoValido($telefono, $codigopais){
        return substr($telefono,0,strlen($codigopais))==$codigopais;
    }
}