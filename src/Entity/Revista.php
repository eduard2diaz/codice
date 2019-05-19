<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Revista
 *
 * @ORM\Table(name="revista")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","pais"})
 */
class Revista
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="revista_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string",length=200, nullable=false)
     * @Assert\Length(max=200)
     */
    private $nombre;

    /**
     * @ORM\Column(type="float")
     * @Assert\GreaterThanOrEqual(value=1)
     */
    private $impacto;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min=1, max=4)
     */
    private $nivel;

    /**
     * @var \Pais
     *
     * @ORM\ManyToOne(targetEntity="Pais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

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

    public function getImpacto(): ?float
    {
        return $this->impacto;
    }

    public function setImpacto(float $impacto): self
    {
        $this->impacto = $impacto;

        return $this;
    }

    public function getNivel(): ?int
    {
        return $this->nivel;
    }

    public function setNivel(int $nivel): self
    {
        $this->nivel = $nivel;

        return $this;
    }

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    public function __toString()
    {
     return $this->getNombre();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (null == $this->getPais())
            $context->buildViolation('Seleccione un país')
                ->atPath('pais')
                ->addViolation();
    }


}
