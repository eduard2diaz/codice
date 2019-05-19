<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Institucion
 *
 * @ORM\Table(name="institucion", indexes={@ORM\Index(name="IDX_F751F7C37E5D2EFF", columns={"pais"}), @ORM\Index(name="IDX_F751F7C35F4745BE", columns={"ministerio"})})
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","pais"})
 */
class Institucion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="institucion_id_seq", allocationSize=1, initialValue=1)
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
     * @var \Pais
     *
     * @ORM\ManyToOne(targetEntity="Pais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

    /**
     * @var \Ministerio
     *
     * @ORM\ManyToOne(targetEntity="Ministerio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ministerio", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $ministerio;

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

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    public function getMinisterio(): ?Ministerio
    {
        return $this->ministerio;
    }

    public function setMinisterio(?Ministerio $ministerio): self
    {
        $this->ministerio = $ministerio;

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
        elseif (null == $this->getMinisterio())
            $context->buildViolation('Seleccione un ministerio')
                ->atPath('ministerio')
                ->addViolation();
    }


}
