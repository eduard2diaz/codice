<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Autor;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Notificacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var \Autor
     *
     * @ORM\ManyToOne(targetEntity="Autor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="destinatario", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $destinatario;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="text", nullable=false)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $leida;

    /**
     * Notificacion constructor.
     */
    public function __construct()
    {
        $this->leida=false;
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Autor
     */
    public function getDestinatario(): Autor
    {
        return $this->destinatario;
    }

    /**
     * @param \Autor $destinatario
     */
    public function setDestinatario(Autor $destinatario): void
    {
        $this->destinatario = $destinatario;
    }

    /**
     * @return mixed
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param mixed $fecha
     */
    public function setFecha($fecha): void
    {
        $this->fecha = $fecha;
    }

    /**
     * @return null|string
     */
    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    /**
     * @param null|string $descripcion
     */
    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }
    
     /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if($this->getDestinatario()==null)
            $context->buildViolation('Seleccione el destinatario')->atPath('destinatario')->addViolation();
    }

    public function getLeida(): ?bool
    {
        return $this->leida;
    }

    public function setLeida(bool $leida): self
    {
        $this->leida = $leida;

        return $this;
    }
}
