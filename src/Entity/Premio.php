<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Premio
 *
 * @ORM\Table(name="premio", indexes={@ORM\Index(name="IDX_1C37ECA8BF2A6516", columns={"institucion_concede"}), @ORM\Index(name="IDX_1C37ECA8898E1AB0", columns={"tipo_premio"})})
 * @ORM\Entity
 */
class Premio
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
     *   @ORM\JoinColumn(name="institucion_concede", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $institucionConcede;

    /**
     * @var \TipoPremio
     *
     * @ORM\ManyToOne(targetEntity="TipoPremio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_premio", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $tipoPremio;

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

    public function getInstitucionConcede(): ?Institucion
    {
        return $this->institucionConcede;
    }

    public function setInstitucionConcede(?Institucion $institucionConcede): self
    {
        $this->institucionConcede = $institucionConcede;

        return $this;
    }

    public function getTipoPremio(): ?TipoPremio
    {
        return $this->tipoPremio;
    }

    public function setTipoPremio(?TipoPremio $tipoPremio): self
    {
        $this->tipoPremio = $tipoPremio;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null == $this->getInstitucionConcede()) {
            $context->setNode($context, 'institucionConcede', null, 'data.institucionConcede');
            $context->addViolation('Seleccione la institución que lo concede');
        }

        if (null == $this->getTipoPremio()) {
            $context->setNode($context, 'tipoPremio', null, 'data.tipoPremio');
            $context->addViolation('Seleccione el tipo de premio');
        }
    }
}
