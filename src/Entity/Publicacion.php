<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Autor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Publicacion
 *
 * @ORM\Table(name="publicacion")
 * @ORM\Entity
 * @UniqueEntity(fields={"titulo","autor","childType"},message="Ya tiene una publicación con ese nombre")
 */
class Publicacion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="publicacion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="titulo", type="string",length=200, nullable=false)
     * @Assert\Length(max=200)
     */
    private $titulo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="resumen", type="text", nullable=false)
     */
    private $resumen;

    /**
     * @var string|null
     *
     * @ORM\Column(name="keywords", type="string",length=200, nullable=false)
     * @Assert\Length(max=200)
     */
    private $keywords;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_captacion", type="date", nullable=false)
     */
    private $fechaCaptacion;

    /**
     * @var \Autor
     *
     * @ORM\ManyToOne(targetEntity="Autor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="autor", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $autor;

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

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max=255)
     */
    private $rutaArchivo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="child_type", type="string",length=255, nullable=false)
     * @Assert\Length(max=255)
     */
    private $childType;

    /**
     * @ORM\Column(type="integer")
     */
    private $estado;

    /**
     * Publicacion constructor.
     */
    public function __construct()
    {
        $this->estado=0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getResumen(): ?string
    {
        return $this->resumen;
    }

    public function setResumen(?string $resumen): self
    {
        $this->resumen = $resumen;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getFechaCaptacion(): ?\DateTimeInterface
    {
        return $this->fechaCaptacion;
    }

    public function setFechaCaptacion(?\DateTimeInterface $fechaCaptacion): self
    {
        $this->fechaCaptacion = $fechaCaptacion;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRutaArchivo()
    {
        return $this->rutaArchivo;
    }

    /**
     * @param mixed $rutaArchivo
     */
    public function setRutaArchivo($rutaArchivo): void
    {
        $this->rutaArchivo = $rutaArchivo;
    }

    /**
     * @return null|string
     */
    public function getChildType(): ?string
    {
        return $this->childType;
    }

    /**
     * @param null|string $childType
     */
    public function setChildType(?string $childType): void
    {
        $this->childType = $childType;
    }

    /**
     * @return \Autor
     */
    public function getAutor(): ?Autor
    {
        return $this->autor;
    }

    /**
     * @param \Autor $autor
     */
    public function setAutor(Autor $autor): void
    {
        $this->autor = $autor;
    }

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function getEstadoString(): string
    {
        $array=['Pendiente a aprobación','Publicación aprobada','Publicación rechazada'];
        return $array[$this->estado];
    }

    public function setEstado(int $estado): self
    {
        $this->estado = $estado;

        return $this;
    }


    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file) {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @Assert\Callback
     */
    public function validar(ExecutionContextInterface $context)
    {
        if (null==$this->getAutor()) {
            $context->setNode($context, 'autor', null, 'data.autor');
            $context->addViolation('Seleccione un autor');
        }
    }

}
