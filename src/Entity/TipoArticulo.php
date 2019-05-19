<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * TipoArticulo
 *
 * @ORM\Table(name="tipo_articulo", indexes={@ORM\Index(name="IDX_BF2C14588C0E9BD3", columns={"grupo"})})
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","grupo"})
 */
class TipoArticulo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tipo_articulo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string",length=80, nullable=false)
     * @Assert\Regex("/^[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{1,})*$/")
     * @Assert\Length(max=80)
     */
    private $nombre;

    /**
     * @var \GrupoArticulo
     *
     * @ORM\ManyToOne(targetEntity="GrupoArticulo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grupo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $grupo;

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

    public function getGrupo(): ?GrupoArticulo
    {
        return $this->grupo;
    }

    public function setGrupo(?GrupoArticulo $grupo): self
    {
        $this->grupo = $grupo;

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
        if (null == $this->getGrupo())
            $context->buildViolation('Seleccione un grupo')
                ->atPath('grupo')
                ->addViolation();
    }


}
