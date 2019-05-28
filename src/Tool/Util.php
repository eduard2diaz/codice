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

    /*
     * Funcionalidad que se utiliza para dar genero a la publicacion
     */
    public static function generoPublicacion($entidad){
        $articulo='la';
        switch ($entidad){
            case 'Articulo':
            case 'Evento':
            case 'Libro':
            case 'Premio':
            case 'Software':
            $articulo='el';
                break;
        }
        return $articulo;
    }

    /*
     *Funcionalidad que se utiliza para convertir a entidad en campo ChilType de publicacion
     */
    public static function entidadPublicacion($cadena){
        return substr($cadena,11);
    }

    /*
     *Funcionalidad que se encarga de aplicar codificación UTF-8 a las entidades 
     */
    public static function codificacionEntidad($entidad){
        $codificacion=$entidad;
        switch ($entidad){
            case 'Articulo':
                $codificacion='Artículo';
            break;
            case 'Monografia':
                $codificacion='Monografía';
            break;
        }
        return $codificacion;
    }

    /*
     *Funcionalidad que se encarga de parsear una publicacion para representarla en las notificaciones
     */
    public static function notificacionPublicacion($cadena){
        $entidad=self::entidadPublicacion($cadena);
        $genero=self::generoPublicacion($entidad);
        return $genero.' '.$entidad;
    }
}