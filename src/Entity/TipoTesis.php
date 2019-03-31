<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * TipoTesis
 *
 * @ORM\Table(name="tipo_tesis")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","clasificacion"})
 */
class TipoTesis
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tipo_tesis_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    private $nombre;

    /**
     * @var \ClasificacionTipoTesis
     *
     * @ORM\ManyToOne(targetEntity="ClasificacionTipoTesis", inversedBy="tipoTeses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="clasificacion", referencedColumnName="id",onDelete="Cascade")
     * })
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

    public function getClasificacion(): ?ClasificacionTipoTesis
    {
        return $this->clasificacion;
    }

    public function setClasificacion(?ClasificacionTipoTesis $clasificacion): self
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
