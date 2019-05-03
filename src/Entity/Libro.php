<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Libro
 *
 * @ORM\Table(name="libro", indexes={@ORM\Index(name="IDX_5799AD2BCCF1F1BA", columns={"editorial"})})
 * @ORM\Entity
 * @UniqueEntity("isbn")
 */
class Libro
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="volumen", type="string", nullable=false)
     */
    private $volumen;

    /**
     * @var string|null
     *
     * @ORM\Column(name="numero", type="string", nullable=false)
     */
    private $numero;

    /**
     * @var string|null
     *
     * @ORM\Column(name="serie", type="string", nullable=false)
     */
    private $serie;

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
     * @ORM\Column(name="isbn", type="string", nullable=false)
     * @Assert\Isbn()
     */
    private $isbn;

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

    /**
     * @var \Editorial
     *
     * @ORM\ManyToOne(targetEntity="Editorial")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="editorial", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $editorial;

    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
    }

    public function getVolumen(): ?string
    {
        return $this->volumen;
    }

    public function setVolumen(?string $volumen): self
    {
        $this->volumen = $volumen;

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

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function setSerie(?string $serie): self
    {
        $this->serie = $serie;

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

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;

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

    public function getEditorial(): ?Editorial
    {
        return $this->editorial;
    }

    public function setEditorial(?Editorial $editor): self
    {
        $this->editorial = $editor;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null==$this->getEditorial()) {
            $context->setNode($context, 'editorial', null, 'data.editorial');
            $context->addViolation('Seleccione una editorial');
        }
    }
}
