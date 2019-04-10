<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity("nombre")
 */
class ClasificacionTipoTesis
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
     * @ORM\OneToMany(targetEntity="App\Entity\TipoTesis", mappedBy="clasificacion")
     */
    private $tipoTeses;

    public function __construct()
    {
        $this->tipoTeses = new ArrayCollection();
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
     * @return Collection|TipoTesis[]
     */
    public function getTipoTeses(): Collection
    {
        return $this->tipoTeses;
    }

    public function addTipoTese(TipoTesis $tipoTese): self
    {
        if (!$this->tipoTeses->contains($tipoTese)) {
            $this->tipoTeses[] = $tipoTese;
            $tipoTese->setClasificacion($this);
        }

        return $this;
    }

    public function removeTipoTese(TipoTesis $tipoTese): self
    {
        if ($this->tipoTeses->contains($tipoTese)) {
            $this->tipoTeses->removeElement($tipoTese);
            // set the owning side to null (unless already changed)
            if ($tipoTese->getClasificacion() === $this) {
                $tipoTese->setClasificacion(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getNombre();
    }
}
