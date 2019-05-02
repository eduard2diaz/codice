<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Rol
 *
 * @ORM\Table(name="rol")
 * @ORM\Entity
 */
class Rol extends Role
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="rol_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    private $nombre;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Autor", mappedBy="idrol")
     */
    private $idautor;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idautor = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * @return Collection|Autor[]
     */
    public function getIdautor(): Collection
    {
        return $this->idautor;
    }

    public function addIdautor(Autor $idautor): self
    {
        if (!$this->idautor->contains($idautor)) {
            $this->idautor[] = $idautor;
            $idautor->addIdrol($this);
        }

        return $this;
    }

    public function removeIdautor(Autor $idautor): self
    {
        if ($this->idautor->contains($idautor)) {
            $this->idautor->removeElement($idautor);
            $idautor->removeIdrol($this);
        }

        return $this;
    }

    public function __toString()
    {
        switch($this->getNombre()){
            case 'ROLE_SUPERADMIN':
                return 'Super-Administrador';
                break;
            case 'ROLE_ADMIN':
                return 'Administrador institucional';
                break;
            case 'ROLE_DIRECTIVO':
                return 'Directivo';
                break;
            case 'ROLE_USER':
                return 'Trabajador';
                break;
            case 'ROLE_GESTORBALANCE':
                return 'Gestor de Balances';
                break;
        }
        return $this->getNombre();
    }


}
