<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Software
 *
 * @ORM\Table(name="software", indexes={@ORM\Index(name="IDX_77D068CFA1153206", columns={"tipo_software"})})
 * @ORM\Entity
 */
class Software
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="numero", type="string", nullable=false)
     */
    private $numero;

    /**
     * @var string|null
     *
     * @ORM\Column(name="idioma", type="string", nullable=false)
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

    /**
     * @var \TipoSoftware
     *
     * @ORM\ManyToOne(targetEntity="TipoSoftware")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_software", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $tipoSoftware;

    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
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

    public function getIdioma(): ?string
    {
        return $this->idioma;
    }

    public function setIdioma(?string $idioma): self
    {
        $this->idioma = $idioma;

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

    public function getTipoSoftware(): ?TipoSoftware
    {
        return $this->tipoSoftware;
    }

    public function setTipoSoftware(?TipoSoftware $tipoSoftware): self
    {
        $this->tipoSoftware = $tipoSoftware;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null == $this->getTipoSoftware()) {
            $context->setNode($context, 'tipoSoftware', null, 'data.tipoSoftware');
            $context->addViolation('Seleccione el tipo de software');
        }
    }
}
