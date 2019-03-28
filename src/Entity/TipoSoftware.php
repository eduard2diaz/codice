<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * TipoSoftware
 *
 * @ORM\Table(name="tipo_software")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","clasificacion"})
 */
class TipoSoftware
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tipo_software_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClasificacionTipoSoftware", inversedBy="tipoSoftwares")
     * @ORM\JoinColumn(nullable=false)
     */
    private $clasificacion;

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

    public function getClasificacion(): ?ClasificacionTipoSoftware
    {
        return $this->clasificacion;
    }

    public function setClasificacion(?ClasificacionTipoSoftware $clasificacion): self
    {
        $this->clasificacion = $clasificacion;

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
        if (null == $this->getClasificacion())
            $context->buildViolation('Seleccione una clasificación')
                ->atPath('clasificacion')
                ->addViolation();
    }


}
