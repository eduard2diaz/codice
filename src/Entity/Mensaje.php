<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Notificacion
 *
 * @ORM\Table(name="mensaje", indexes={@ORM\Index(name="IDX_729A19EC51A5ACA4", columns={"remitente"})})
 * @ORM\Entity
 */
class Mensaje
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="mensaje_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @var string|null
     *
     * @ORM\Column(name="asunto", type="string", nullable=false)
     */
    private $asunto;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var \Autor
     *
     * @ORM\ManyToOne(targetEntity="Autor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="remitente", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $remitente;

    /**
     * @var \Autor
     *
     * @ORM\ManyToOne(targetEntity="Autor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="propietario", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $propietario;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Autor", inversedBy="idmensaje")
     * @ORM\JoinTable(name="mensajeusuario",
     *   joinColumns={
     *     @ORM\JoinColumn(name="idmensaje", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="iddestinatario", referencedColumnName="id")
     *   }
     * )
     */
    private $iddestinatario;

    /**
     * @var integer
     *
     * @ORM\Column(name="bandeja", type="integer", nullable=false)
     */
    private $bandeja;

    public function __construct()
    {
        $this->fecha = new \DateTime();
        $this->asunto='';
        $this->iddestinatario = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bandeja = 1;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Notificacion
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @return null|string
     */
    public function getAsunto(): ?string
    {
        return $this->asunto;
    }

    /**
     * @param null|string $asunto
     */
    public function setAsunto(?string $asunto=null): void
    {
        $this->asunto = $asunto;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Notificacion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set remitente
     *
     * @param \App\Entity\Autor $remitente
     *
     * @return Notificacion
     */
    public function setRemitente(\App\Entity\Autor $remitente = null)
    {
        $this->remitente = $remitente;

        return $this;
    }

    /**
     * Get remitente
     *
     * @return \App\Entity\Autor
     */
    public function getRemitente()
    {
        return $this->remitente;
    }

    /**
     * Set propietario
     *
     * @param \App\Entity\Autor $propietario
     *
     * @return Notificacion
     */
    public function setPropietario(\App\Entity\Autor $propietario = null)
    {
        $this->propietario = $propietario;

        return $this;
    }

    /**
     * Get propietario
     *
     * @return \App\Entity\Autor
     */
    public function getPropietario()
    {
        return $this->propietario;
    }

    /**
     * Add iddestinatario
     *
     * @param \App\Entity\Autor $iddestinatario
     *
     * @return Mensaje
     */
    public function addIddestinatario(\App\Entity\Autor $iddestinatario)
    {
        $this->iddestinatario[] = $iddestinatario;

        return $this;
    }

    /**
     * Remove iddestinatario
     *
     * @param \App\Entity\Autor $iddestinatario
     */
    public function removeIddestinatario(\App\Entity\Autor $iddestinatario)
    {
        $this->iddestinatario->removeElement($iddestinatario);
    }

    /**
     * Get iddestinatario
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIddestinatario()
    {
        return $this->iddestinatario;
    }

    /**
     * @return int
     */
    public function getBandeja(): int
    {
        return $this->bandeja;
    }

    /**
     * @param int $bandeja
     */
    public function setBandeja(int $bandeja): void
    {
        $this->bandeja = $bandeja;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {

        if($this->getRemitente()==null)
            $context->buildViolation('Seleccione un remitente')->atPath('remitente')->addViolation();
        if($this->getPropietario()==null)
            $context->buildViolation('Seleccione un propietario')->atPath('propietario')->addViolation();
        if($this->getIddestinatario()->isEmpty())
            $context->buildViolation('Seleccione el/los destinatario(s)')->atPath('iddestinatario')->addViolation();
    }
}
