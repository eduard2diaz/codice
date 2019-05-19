<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Area
 *
 * @ORM\Table(name="area")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","padre","institucion","ministerio","pais"},ignoreNull=false)
 */
class Area
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="area_id_seq", allocationSize=1, initialValue=1)
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
     * @var \Area
     *
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="padre", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $padre;

    /**
     * @var \Pais
     *
     * @ORM\ManyToOne(targetEntity="Pais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

    /**
     * @var \Ministerio
     *
     * @ORM\ManyToOne(targetEntity="Ministerio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ministerio", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $ministerio;

    /**
     * @var \Institucion
     *
     * @ORM\ManyToOne(targetEntity="Institucion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucion", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $institucion;

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

    /**
     * @return \Area
     */
    public function getPadre(): ?Area
    {
        return $this->padre;
    }

    /**
     * @param \Area $padre
     */
    public function setPadre(Area $padre=null): void
    {
        $this->padre = $padre;
    }

    /**
     * @return \Pais
     */
    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    /**
     * @param \Pais $pais
     */
    public function setPais(Pais $pais): void
    {
        $this->pais = $pais;
    }

    /**
     * @return \Ministerio
     */
    public function getMinisterio(): ?Ministerio
    {
        return $this->ministerio;
    }

    /**
     * @param \Ministerio $ministerio
     */
    public function setMinisterio(Ministerio $ministerio): void
    {
        $this->ministerio = $ministerio;
    }

    /**
     * @return \Institucion
     */
    public function getInstitucion(): ?Institucion
    {
        return $this->institucion;
    }

    /**
     * @param \Institucion $institucion
     */
    public function setInstitucion(Institucion $institucion): void
    {
        $this->institucion = $institucion;
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

        if($this->getPais()==null)
            $context->buildViolation('Seleccione un país')->atPath('pais')->addViolation();
        if($this->getMinisterio()==null)
            $context->buildViolation('Seleccione un ministerio')->atPath('ministerio')->addViolation();
        if($this->getInstitucion()==null)
            $context->buildViolation('Seleccione una institución')->atPath('institucion')->addViolation();


        if (null != $this->getPadre())
            if ($this->getPadre()->getId() == $this->getId())
                $context->buildViolation('Un área no puede ser padre de si misma')
                    ->atPath('padre')
                    ->addViolation();
            else {
                $hijo = $this->cicloInfinito($this->getId(), $this->getPadre());
                if (null != $hijo)
                    $context->buildViolation('Referencia circular: Esta área es padre de ' . $hijo)
                        ->atPath('padre')
                        ->addViolation();
            }
    }

    private function cicloInfinito($current, Area $padre)
    {
        if ($padre->getPadre() != null) {
            if ($padre->getPadre()->getId() == $current)
                return $padre->getNombre();
            else
                return $this->cicloInfinito($current, $padre->getPadre());
        }
        return null;
    }


}
