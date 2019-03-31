<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\Autor;


/**
 * Publicacion
 *
 * @ORM\Table(name="publicacion", indexes={@ORM\Index(name="IDX_62F2085F7E5D2EFF", columns={"pais"})})
 * @ORM\Entity
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
     * @ORM\Column(name="titulo", type="string", nullable=false)
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
     * @ORM\Column(name="keywords", type="string", nullable=false)
     */
    private $keywords;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_captacion", type="date", nullable=false)
     */
    private $fechaCaptacion;

    /**
     * @var \Pais
     *
     * @ORM\ManyToOne(targetEntity="Pais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

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
     * @Assert\File()
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rutaArchivo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="child_type", type="string", nullable=false)
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

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

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
    public function setFile($file) {
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

    public function actualizarFoto($directorioDestino) {

        if (null !== $this->getFile()) {
            $this->removeUpload($directorioDestino);
            $this->Upload($directorioDestino);
        }
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
    public function comprobarCargo(ExecutionContextInterface $context)
    {
        if (null==$this->getPais()) {
            $context->setNode($context, 'area', null, 'data.pais');
            $context->addViolation('Seleccione un país');
        }elseif (null==$this->getAutor()) {
            $context->setNode($context, 'area', null, 'data.autor');
            $context->addViolation('Seleccione un autor');
        }
    }

}
