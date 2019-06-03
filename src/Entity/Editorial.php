<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Tool\Util;

/**
 * Editor
 *
 * @ORM\Table(name="editorial")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","pais"})
 * @UniqueEntity("correo")
 * @UniqueEntity("direccion")
 */
class Editorial
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="editor_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\ManyToOne(targetEntity="Pais", inversedBy="editorials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     strict = true
     * )
     */
    private $correo;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max=30)
     * @Assert\Regex("/^((\+|\-)\d+)$/")
     */
    private $telefono;

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

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(?string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

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
        elseif(null!=$this->getTelefono() && !Util::esTelefonoValido($this->getTelefono(),$this->getPais()->getCodigo())) {
            $context->setNode($context, 'telefono', null, 'data.telefono');
            $context->addViolation('Este teléfono no pertenece al país indicado');
        }
    }


}
