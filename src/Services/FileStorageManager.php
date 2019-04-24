<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/4/2019
 * Time: 18:40
 */

namespace App\Services;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileStorageManager
{
    public static function Upload($ruta, $file)
    {
        if (null === $file) {
            return;
        }
        $fs = new Filesystem();
        $camino = $fs->makePathRelative($ruta, __DIR__);
        $directorioDestino = __DIR__ . DIRECTORY_SEPARATOR . $camino;
        $nombreArchivoFoto = uniqid('codice-') . '-' . $file->getClientOriginalName();
        $file->move($directorioDestino . DIRECTORY_SEPARATOR, $nombreArchivoFoto);
        return $nombreArchivoFoto;
    }

    public static function Download($ruta): Response
    {
        if (!file_exists($ruta))
            throw new NotFoundHttpException();

        $archivo = file_get_contents($ruta);
        return new Response($archivo, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Transfer-Encoding' => 'binary',
            'Content-length' => strlen($archivo),
            'Pragma' => 'no-cache',
            'Expires' => '0'));
    }

    public static function removeUpload($rutaPc)
    {
        $fs = new Filesystem();
        if (null != $rutaPc && $fs->exists($rutaPc)) {
            $fs->remove($rutaPc);
        }
    }
}