<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/4/2019
 * Time: 18:40
 */

namespace App\Tool;

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

        // Generate response
        $response = new Response();
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($ruta));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($ruta) . '";');
        $response->headers->set('Content-length', filesize($ruta));
        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(file_get_contents($ruta));
        return $response;
    }

    public static function removeUpload($rutaPc)
    {
        $fs = new Filesystem();
        if (null != $rutaPc && $fs->exists($rutaPc)) {
            $fs->remove($rutaPc);
        }
    }
}