<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Articulo
 *
 * @ORM\Table(name="articulo", indexes={@ORM\Index(name="IDX_69E94E91A94F1646", columns={"revista"}), @ORM\Index(name="IDX_69E94E91BF2C1458", columns={"tipo_articulo"})})
 * @ORM\Entity
 * @UniqueEntity("doi")
 * @UniqueEntity("issn")
 */
class Articulo
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="volumen", type="string", nullable=false)
     */
    private $volumen;

    /**
     * @var int|null
     *
     * @ORM\Column(name="paginas", type="integer", nullable=false)
     * @Assert\Range(
     *      min = 1,
     * )
     */
    private $paginas;

    /**
     * @var string|null
     *
     * @ORM\Column(name="numero", type="string", nullable=false)
     */
    private $numero;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doi", type="string", nullable=false)
     */
    private $doi;

    /**
     * @var string|null
     *
     * @ORM\Column(name="issn", type="string", nullable=false)
     * @Assert\Issn
     */
    private $issn;

    /**
     * @var \Publicacion
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Publicacion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="id",onDelete="Cascade")
     * })
     * @Assert\Valid()
     */
    private $id;

    /**
     * @var \Revista
     *
     * @ORM\ManyToOne(targetEntity="Revista")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="revista", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $revista;

    /**
     * @var \TipoArticulo
     *
     * @ORM\ManyToOne(targetEntity="TipoArticulo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_articulo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $tipoArticulo;


    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
    }


    public function getVolumen(): ?string
    {
        return $this->volumen;
    }

    public function setVolumen(?string $volumen): self
    {
        $this->volumen = $volumen;

        return $this;
    }

    public function getPaginas(): ?int
    {
        return $this->paginas;
    }

    public function setPaginas(?int $paginas): self
    {
        $this->paginas = $paginas;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDoi(): ?string
    {
        return $this->doi;
    }

    public function setDoi(?string $doi): self
    {
        $this->doi = $doi;

        return $this;
    }

    public function getIssn(): ?string
    {
        return $this->issn;
    }

    public function setIssn(?string $issn): self
    {
        $this->issn = $issn;

        return $this;
    }

    public function getId(): ?Publicacion
    {
        return $this->id;
    }

    public function setId(?Publicacion $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRevista(): ?Revista
    {
        return $this->revista;
    }

    public function setRevista(?Revista $revista): self
    {
        $this->revista = $revista;

        return $this;
    }

    public function getTipoArticulo(): ?TipoArticulo
    {
        return $this->tipoArticulo;
    }

    public function setTipoArticulo(?TipoArticulo $tipoArticulo): self
    {
        $this->tipoArticulo = $tipoArticulo;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null == $this->getRevista()) {
            $context->setNode($context, 'revista', null, 'data.revista');
            $context->addViolation('Seleccione la revista');
        }

        if (null == $this->getTipoArticulo()) {
            $context->setNode($context, 'tipoArticulo', null, 'data.tipoArticulo');
            $context->addViolation('Seleccione el tipo de artículo');
        }
    }
}
