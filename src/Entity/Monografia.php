<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Monografia
 *
 * @ORM\Table(name="monografia")
 * @ORM\Entity
 */
class Monografia
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="isbn", type="string", nullable=true)
     */
    private $isbn;

    /**
     * @var int|null
     *
     * @ORM\Column(name="paginas", type="integer", nullable=true)
     */
    private $paginas;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cenda", type="string", nullable=true)
     */
    private $cenda;

    /**
     * @var string|null
     *
     * @ORM\Column(name="number", type="string", nullable=true)
     */
    private $number;

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
