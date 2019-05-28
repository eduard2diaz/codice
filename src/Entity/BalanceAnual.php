<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @Assert\Length(max=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var \Autor
     *
     * @ORM\ManyToOne(targetEntity="Autor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $usuario;

    /**
     * @var \Area
     *
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="area", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $area;

    /**
     * @var \Institucion
     *
     * @ORM\ManyToOne(targetEntity="Institucion", inversedBy="balanceAnuals")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucion", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $institucion;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    private $rutaArchivo;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha;

    /**
     * @Assert\File(
     * maxSize="20mi",
     * notReadableMessage = "No se puede leer el archivo",
     * maxSizeMessage = "El archivo es demasiado grande. El tamaño máximo permitido es 20Mb",
     * uploadIniSizeErrorMessage = "El archivo es demasiado grande. El tamaño máximo permitido es 20Mb",
     * uploadFormSizeErrorMessage = "El archivo es demasiado grande. El tamaño máximo permitido es 20Mb",
     * uploadErrorMessage = "No se puede subir el archivo")
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

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

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

    public function setFecha(?\DateTimeInterface $fecha): self
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
    public function setFile(UploadedFile $file): void
    {
        $this->file = $file;
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
