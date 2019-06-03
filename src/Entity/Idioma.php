<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Idioma
 *
 * @ORM\Table(name="idioma")
 * @ORM\Entity
 * @UniqueEntity("nombre")
 */
class Idioma
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="idioma_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string",length=80, nullable=false)
     * @Assert\Regex("/^[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{1,})*$/")
     * @Assert\Length(max=80)
     */
    private $nombre;

    /**
     * @ORM\ManyToMany(targetEntity="Software", mappedBy="idioma")
     */
    private $softwares;

    public function __construct()
    {
        $this->softwares = new ArrayCollection();
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

    public function __toString()
    {
     return $this->getNombre();
    }

    /**
     * @return Collection|Software[]
     */
    public function getSoftwares(): Collection
    {
        return $this->softwares;
    }

    public function addSoftware(Software $software): self
    {
        if (!$this->softwares->contains($software)) {
            $this->softwares[] = $software;
            $software->addIdioma($this);
        }

        return $this;
    }

    public function removeSoftware(Software $software): self
    {
        if ($this->softwares->contains($software)) {
            $this->softwares->removeElement($software);
            $software->removeIdioma($this);
        }

        return $this;
    }


}
