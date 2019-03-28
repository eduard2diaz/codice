<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Pais
 *
 * @ORM\Table(name="pais")
 * @ORM\Entity
 * @UniqueEntity("nombre")
 * @UniqueEntity("capital")
 * @UniqueEntity("codigo")
 */
class Pais
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pais_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="capital", type="string", nullable=true)
     */
    private $capital;

    /**
     * @var int|null
     *
     * @ORM\Column(name="codigo", type="string", nullable=true)
     */
    private $codigo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Revista", mappedBy="pais")
     */
    private $revistas;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Editorial", mappedBy="pais")
     */
    private $editorials;

    public function __construct()
    {
        $this->revistas = new ArrayCollection();
        $this->editorials = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(?string $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @return Collection|Revista[]
     */
    public function getRevistas(): Collection
    {
        return $this->revistas;
    }

    public function addRevista(Revista $revista): self
    {
        if (!$this->revistas->contains($revista)) {
            $this->revistas[] = $revista;
            $revista->setPais($this);
        }

        return $this;
    }

    public function removeRevista(Revista $revista): self
    {
        if ($this->revistas->contains($revista)) {
            $this->revistas->removeElement($revista);
            // set the owning side to null (unless already changed)
            if ($revista->getPais() === $this) {
                $revista->setPais(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Editorial[]
     */
    public function getEditorials(): Collection
    {
        return $this->editorials;
    }

    public function addEditorial(Editorial $editorial): self
    {
        if (!$this->editorials->contains($editorial)) {
            $this->editorials[] = $editorial;
            $editorial->setPais($this);
        }

        return $this;
    }

    public function removeEditorial(Editorial $editorial): self
    {
        if ($this->editorials->contains($editorial)) {
            $this->editorials->removeElement($editorial);
            // set the owning side to null (unless already changed)
            if ($editorial->getPais() === $this) {
                $editorial->setPais(null);
            }
        }

        return $this;
    }

}
