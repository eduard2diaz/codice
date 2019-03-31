<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Articulo
 *
 * @ORM\Table(name="articulo", indexes={@ORM\Index(name="IDX_69E94E91A94F1646", columns={"revista"}), @ORM\Index(name="IDX_69E94E91BF2C1458", columns={"tipo_articulo"})})
 * @ORM\Entity
 */
class Articulo
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="volumen", type="string", nullable=true)
     */
    private $volumen;

    /**
     * @var int|null
     *
     * @ORM\Column(name="paginas", type="integer", nullable=true)
     */
    private $paginas;

    /**
     * @var string|null
     *
     * @ORM\Column(name="numero", type="string", nullable=true)
     */
    private $numero;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doi", type="string", nullable=true)
     */
    private $doi;

    /**
     * @var string|null
     *
     * @ORM\Column(name="issn", type="string", nullable=true)
     */
    private $issn;

    /**
     * @var \Publicacion
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Publicacion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="id")
     * })
     */
    private $id;

    /**
     * @var \Revista
     *
     * @ORM\ManyToOne(targetEntity="Revista")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="revista", referencedColumnName="id")
     * })
     */
    private $revista;

    /**
     * @var \TipoArticulo
     *
     * @ORM\ManyToOne(targetEntity="TipoArticulo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_articulo", referencedColumnName="id")
     * })
     */
    private $tipoArticulo;

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


}
