<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity("nombre")
 */
class ClasificacionTipoSoftware
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{2,})*$/")
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TipoSoftware", mappedBy="clasificacion")
     */
    private $tipoSoftwares;

    public function __construct()
    {
        $this->tipoSoftwares = new ArrayCollection();
    }

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

    /**
     * @return Collection|TipoSoftware[]
     */
    public function getTipoSoftwares(): Collection
    {
        return $this->tipoSoftwares;
    }

    public function addTipoSoftware(TipoSoftware $tipoSoftware): self
    {
        if (!$this->tipoSoftwares->contains($tipoSoftware)) {
            $this->tipoSoftwares[] = $tipoSoftware;
            $tipoSoftware->setClasificacion($this);
        }

        return $this;
    }

    public function removeTipoSoftware(TipoSoftware $tipoSoftware): self
    {
        if ($this->tipoSoftwares->contains($tipoSoftware)) {
            $this->tipoSoftwares->removeElement($tipoSoftware);
            // set the owning side to null (unless already changed)
            if ($tipoSoftware->getClasificacion() === $this) {
                $tipoSoftware->setClasificacion(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getNombre();
    }
}
