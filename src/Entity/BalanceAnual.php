<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @ORM\Entity
 */
class BalanceAnual
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Autor")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Institucion", inversedBy="balanceAnuals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $institucion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rutaArchivo;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha;

    /**
     * @Assert\File()
     */
    private $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getUsuario(): ?Autor
    {
        return $this->usuario;
    }

    public function setUsuario(?Autor $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getInstitucion(): ?Institucion
    {
        return $this->institucion;
    }

    public function setInstitucion(?Institucion $institucion): self
    {
        $this->institucion = $institucion;

        return $this;
    }

    public function getRutaArchivo(): ?string
    {
        return $this->rutaArchivo;
    }

    public function setRutaArchivo(string $rutaArchivo): self
    {
        $this->rutaArchivo = $rutaArchivo;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    public function Upload($ruta) {
        if (null === $this->file) {
            return;
        }
        $fs = new Filesystem();
        $camino = $fs->makePathRelative($ruta, __DIR__);
        $directorioDestino = __DIR__ . DIRECTORY_SEPARATOR . $camino;
        $nombreArchivoFoto = uniqid('codice-') . '-' . $this->file->getClientOriginalName();
        $this->file->move($directorioDestino.DIRECTORY_SEPARATOR, $nombreArchivoFoto);
        $this->setRutaArchivo($nombreArchivoFoto);
    }

    public function removeUpload($directorioDestino) {
        $fs=new Filesystem();
        $rutaPc = $directorioDestino.DIRECTORY_SEPARATOR.$this->getRutaArchivo();
        if (null!=$this->getRutaArchivo()  && $fs->exists($rutaPc)) {
            $fs->remove($rutaPc);
        }
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null==$this->getUsuario())
            $context->addViolation('Seleccione un usuario');
        elseif (null==$this->getInstitucion())
            $context->addViolation('Seleccione la institución');
    }
}
