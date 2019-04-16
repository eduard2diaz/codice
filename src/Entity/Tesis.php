<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Tesis
 *
 * @ORM\Table(name="tesis", indexes={@ORM\Index(name="IDX_ED0C9042F751F7C3", columns={"institucion"}), @ORM\Index(name="IDX_ED0C9042EBB00A8A", columns={"tipo_tesis"})})
 * @ORM\Entity
 */
class Tesis
{
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
     * @var \Institucion
     *
     * @ORM\ManyToOne(targetEntity="Institucion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucion", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $institucion;

    /**
     * @var \TipoTesis
     *
     * @ORM\ManyToOne(targetEntity="TipoTesis")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_tesis", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $tipoTesis;

    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
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

    public function getInstitucion(): ?Institucion
    {
        return $this->institucion;
    }

    public function setInstitucion(?Institucion $institucion): self
    {
        $this->institucion = $institucion;

        return $this;
    }

    public function getTipoTesis(): ?TipoTesis
    {
        return $this->tipoTesis;
    }

    public function setTipoTesis(?TipoTesis $tipoTesis): self
    {
        $this->tipoTesis = $tipoTesis;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null == $this->getInstitucion()) {
            $context->setNode($context, 'institucion', null, 'data.institucion');
            $context->addViolation('Seleccione la institución');
        }

        if (null == $this->getTipoTesis()) {
            $context->setNode($context, 'tipoTesis', null, 'data.tipoTesis');
            $context->addViolation('Seleccione el tipo de tesis');
        }
    }


}
