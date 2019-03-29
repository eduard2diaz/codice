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
 * @UniqueEntity(fields={"nombre","padre"},ignoreNull=false)
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
     * @ORM\Column(name="nombre", type="string", nullable=true)
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

    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {

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
