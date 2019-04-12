<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Encuentro
 *
 * @ORM\Table(name="encuentro", indexes={@ORM\Index(name="IDX_CDFA77FA2F94C8B4", columns={"tipo_encuentro"}), @ORM\Index(name="IDX_CDFA77FAA96ECF59", columns={"organizador"})})
 * @ORM\Entity
 */
class Encuentro
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
     * @var string|null
     *
     * @ORM\Column(name="isbn", type="string", nullable=true)
     */
    private $isbn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ciudad", type="string", nullable=true)
     */
    private $ciudad;

    /**
     * @var string|null
     *
     * @ORM\Column(name="issn", type="string", nullable=true)
     */
    private $issn;

    /**
     * @var \TipoEncuentro
     *
     * @ORM\ManyToOne(targetEntity="TipoEncuentro")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_encuentro", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $tipoEncuentro;

    /**
     * @var \Organizador
     *
     * @ORM\ManyToOne(targetEntity="Organizador")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organizador", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $organizador;

    /**
     * @var \Pais
     *
     * @ORM\ManyToOne(targetEntity="Pais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

    public function __construct()
    {
        $this->setId(new Publicacion());
        $this->getId()->setChildType(get_class($this));
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

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(?string $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getIssn(): ?string
    {
        return $this->issn;
    }

    public function setIssn(?string $issn): self
    {
        $this->issn = $issn;

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

    public function getTipoEncuentro(): ?TipoEncuentro
    {
        return $this->tipoEncuentro;
    }

    public function setTipoEncuentro(?TipoEncuentro $tipoEncuentro): self
    {
        $this->tipoEncuentro = $tipoEncuentro;

        return $this;
    }

    public function getOrganizador(): ?Organizador
    {
        return $this->organizador;
    }

    public function setOrganizador(?Organizador $organizador): self
    {
        $this->organizador = $organizador;

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

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (null==$this->getPais()) {
            $context->setNode($context, 'pais', null, 'data.pais');
            $context->addViolation('Seleccione un país');
        }

        if (null == $this->getOrganizador()) {
            $context->setNode($context, 'area', null, 'data.organizador');
            $context->addViolation('Seleccione el organizador');
        }

        if (null == $this->getTipoEncuentro()) {
            $context->setNode($context, 'area', null, 'data.tipoEncuentro');
            $context->addViolation('Seleccione el tipo de encuentro');
        }
    }
}
