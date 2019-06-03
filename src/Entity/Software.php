<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Idioma", inversedBy="softwares")
     * @ORM\JoinTable(name="software_idioma",
     *   joinColumns={
     *     @ORM\JoinColumn(name="softwares", referencedColumnName="id",onDelete="Cascade")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="idioma", referencedColumnName="id",onDelete="Cascade")
     *   }
     * )
     */
    private $idioma;

    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
        $this->idioma = new ArrayCollection();
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

    public function addIdioma(Idioma $idioma): self
    {
        if (!$this->idioma->contains($idioma)) {
            $this->idioma[] = $idioma;
        }

        return $this;
    }

    public function removeIdioma(Idioma $idioma): self
    {
        if ($this->idioma->contains($idioma)) {
            $this->idioma->removeElement($idioma);
        }

        return $this;
    }

    /**
     * @return Collection|Idioma[]
     */
    public function getIdioma(): Collection
    {
        return $this->idioma;
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
        if ($this->getIdioma()->isEmpty()) {
            $context->setNode($context, 'idioma', null, 'data.idioma');
            $context->addViolation('Seleccione el/los idioma(s)');
        }
    }
}
