<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Software
 *
 * @ORM\Table(name="software", indexes={@ORM\Index(name="IDX_77D068CFA1153206", columns={"tipo_software"})})
 * @ORM\Entity
 */
class Software
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="numero", type="string", nullable=true)
     */
    private $numero;

    /**
     * @var string|null
     *
     * @ORM\Column(name="idioma", type="string", nullable=true)
     */
    private $idioma;

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
     * @var \TipoSoftware
     *
     * @ORM\ManyToOne(targetEntity="TipoSoftware")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_software", referencedColumnName="id")
     * })
     */
    private $tipoSoftware;

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getIdioma(): ?string
    {
        return $this->idioma;
    }

    public function setIdioma(?string $idioma): self
    {
        $this->idioma = $idioma;

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

    public function getTipoSoftware(): ?TipoSoftware
    {
        return $this->tipoSoftware;
    }

    public function setTipoSoftware(?TipoSoftware $tipoSoftware): self
    {
        $this->tipoSoftware = $tipoSoftware;

        return $this;
    }


}
