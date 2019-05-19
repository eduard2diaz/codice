<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Evento
 *
 * @ORM\Table(name="evento", indexes={@ORM\Index(name="IDX_CDFA77FA2F94C8B4", columns={"tipo_evento"}), @ORM\Index(name="IDX_CDFA77FAA96ECF59", columns={"organizador"})})
 * @ORM\Entity
 * @UniqueEntity("isbn")
 * @UniqueEntity("issn")
 */
class Evento
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
     * @ORM\Column(name="isbn", type="string", nullable=false)
     * @Assert\Isbn()
     */
    private $isbn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ciudad", type="string",length=200, nullable=false)
     * @Assert\Length(max=200)
     */
    private $ciudad;

    /**
     * @var string|null
     *
     * @ORM\Column(name="issn", type="string", nullable=false)
     * @Assert\Issn
     */
    private $issn;

    /**
     * @var \TipoEvento
     *
     * @ORM\ManyToOne(targetEntity="TipoEvento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_evento", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $tipoEvento;

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

    public function getTipoEvento(): ?TipoEvento
    {
        return $this->tipoEvento;
    }

    public function setTipoEvento(?TipoEvento $tipoEvento): self
    {
        $this->tipoEvento = $tipoEvento;

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
            $context->setNode($context, 'organizador', null, 'data.organizador');
            $context->addViolation('Seleccione el organizador');
        }

        if (null == $this->getTipoEvento()) {
            $context->setNode($context, 'tipoEvento', null, 'data.tipoEvento');
            $context->addViolation('Seleccione el tipo de evento');
        }
    }
}
