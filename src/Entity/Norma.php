<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Norma
 *
 * @ORM\Table(name="norma", indexes={@ORM\Index(name="IDX_3EF6217E384ABBB6", columns={"tipo_norma"})})
 * @ORM\Entity
 */
class Norma
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="no_registro", type="string", nullable=true)
     */
    private $noRegistro;

    /**
     * @var int|null
     *
     * @ORM\Column(name="paginas", type="integer", nullable=true)
     */
    private $paginas;

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
     * @var \TipoNorma
     *
     * @ORM\ManyToOne(targetEntity="TipoNorma")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_norma", referencedColumnName="id")
     * })
     */
    private $tipoNorma;

    public function getNoRegistro(): ?string
    {
        return $this->noRegistro;
    }

    public function setNoRegistro(?string $noRegistro): self
    {
        $this->noRegistro = $noRegistro;

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

    public function getId(): ?Publicacion
    {
        return $this->id;
    }

    public function setId(?Publicacion $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTipoNorma(): ?TipoNorma
    {
        return $this->tipoNorma;
    }

    public function setTipoNorma(?TipoNorma $tipoNorma): self
    {
        $this->tipoNorma = $tipoNorma;

        return $this;
    }


}
