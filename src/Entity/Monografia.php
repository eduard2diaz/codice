<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Monografia
 *
 * @ORM\Table(name="monografia")
 * @ORM\Entity
 * @UniqueEntity("isbn")
 */
class Monografia
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="isbn", type="string", nullable=false)
     * @Assert\Isbn()
     */
    private $isbn;

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
     * @ORM\Column(name="cenda", type="string",length=200, nullable=false)
     * @Assert\Length(max=200)
     */
    private $cenda;

    /**
     * @var string|null
     *
     * @ORM\Column(name="number", type="string",length=200, nullable=false)
     * @Assert\Length(max=200)
     */
    private $number;

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

    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;

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

    public function getCenda(): ?string
    {
        return $this->cenda;
    }

    public function setCenda(?string $cenda): self
    {
        $this->cenda = $cenda;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

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
}
