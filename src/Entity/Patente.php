<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Patente
 *
 * @ORM\Table(name="patente")
 * @ORM\Entity
 */
class Patente
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="number", type="string", nullable=false)
     *
     */
    private $number;

    /**
     * @var \Idioma
     *
     * @ORM\ManyToOne(targetEntity="Idioma")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idioma", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $idioma;

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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return \Idioma
     */
    public function getIdioma(): ?Idioma
    {
        return $this->idioma;
    }

    /**
     * @param \Idioma $idioma
     */
    public function setIdioma(?Idioma $idioma): void
    {
        $this->idioma = $idioma;
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

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null == $this->getIdioma()) {
            $context->setNode($context, 'idioma', null, 'data.idioma');
            $context->addViolation('Seleccione el idioma');
        }
    }
}
