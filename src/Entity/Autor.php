<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Validator\UniqueMultipleEntity as UniqueMultipleEntityConstraint;
use App\Validator\Autor as AutorConstraint;
/**
 * Autor
 *
 * @ORM\Table(name="autor", indexes={@ORM\Index(name="IDX_31075EBAD7943D68", columns={"area"}), @ORM\Index(name="IDX_31075EBAAF54064B", columns={"grado_cientifico"}), @ORM\Index(name="IDX_31075EBAF751F7C3", columns={"institucion"}), @ORM\Index(name="IDX_31075EBA5F4745BE", columns={"ministerio"}), @ORM\Index(name="IDX_31075EBA7E5D2EFF", columns={"pais"})})
 * @ORM\Entity(repositoryClass="App\Repository\AutorRepository")
 * @UniqueMultipleEntityConstraint(field="usuario",entities={"Autor","Usuario"})
 * @UniqueMultipleEntityConstraint(field="email",entities={"Autor","Usuario"})
 * @AutorConstraint(idrol="idrol")
 */
class Autor implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="autor_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="usuario", type="string", nullable=true)
     */
    private $usuario;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="string", nullable=true)
     */
    private $password;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="ultimo_login", type="datetime", nullable=true)
     */
    private $ultimoLogin;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="ultimo_logout", type="datetime", nullable=true)
     */
    private $ultimoLogout;


    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private $phone;

    /**
     * @var \Area
     *
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="area", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $area;

    /**
     * @var \GradoCientifico
     *
     * @ORM\ManyToOne(targetEntity="GradoCientifico")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grado_cientifico", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $gradoCientifico;

    /**
     * @var \Institucion
     *
     * @ORM\ManyToOne(targetEntity="Institucion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucion", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $institucion;

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
     * @var \Pais
     *
     * @ORM\ManyToOne(targetEntity="Pais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $pais;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Rol", inversedBy="idautor")
     * @ORM\JoinTable(name="autor_rol",
     *   joinColumns={
     *     @ORM\JoinColumn(name="idautor", referencedColumnName="id",onDelete="Cascade")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="idrol", referencedColumnName="id",onDelete="Cascade")
     *   }
     * )
     */
    private $idrol;

    /**
     * @var \Autor
     *
     * @ORM\ManyToOne(targetEntity="Autor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jefe", referencedColumnName="id",onDelete="Cascade", nullable=true)
     * })
     */
    private $jefe;

    /**
     * @Assert\Image()
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rutaFoto;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Mensaje", mappedBy="iddestinatario")
     */
    private $idmensaje;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Autor", inversedBy="seguidor")
     */
    private $seguidores;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Autor", mappedBy="seguidores")
     */
    private $seguidor;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idrol = new \Doctrine\Common\Collections\ArrayCollection();
        $this->idmensaje = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setActivo(true);
        $this->seguidores = new ArrayCollection();
        $this->seguidor = new ArrayCollection();
    }

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

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(?string $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(?bool $activo): self
    {
        $this->activo = $activo;

        return $this;
    }

    public function getUltimoLogin(): ?\DateTimeInterface
    {
        return $this->ultimoLogin;
    }

    public function setUltimoLogin(?\DateTimeInterface $ultimoLogin): self
    {
        $this->ultimoLogin = $ultimoLogin;

        return $this;
    }

    public function getUltimoLogout(): ?\DateTimeInterface
    {
        return $this->ultimoLogout;
    }

    public function setUltimoLogout(?\DateTimeInterface $ultimoLogout): self
    {
        $this->ultimoLogout = $ultimoLogout;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

        return $this;
    }


    public function getGradoCientifico(): ?GradoCientifico
    {
        return $this->gradoCientifico;
    }

    public function setGradoCientifico(?GradoCientifico $gradoCientifico): self
    {
        $this->gradoCientifico = $gradoCientifico;

        return $this;
    }

    public function getInstitucion(): ?Institucion
    {
        return $this->institucion;
    }

    public function setInstitucion(?Institucion $institucion): self
    {
        $this->institucion = $institucion;

        return $this;
    }

    public function getMinisterio(): ?Ministerio
    {
        return $this->ministerio;
    }

    public function setMinisterio(?Ministerio $ministerio): self
    {
        $this->ministerio = $ministerio;

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
     * @return Collection|Rol[]
     */
    public function getIdrol(): Collection
    {
        return $this->idrol;
    }

    public function addIdrol(Rol $idrol): self
    {
        if (!$this->idrol->contains($idrol)) {
            $this->idrol[] = $idrol;
        }

        return $this;
    }

    public function removeIdrol(Rol $idrol): self
    {
        if ($this->idrol->contains($idrol)) {
            $this->idrol->removeElement($idrol);
        }

        return $this;
    }

    /**
     * Add idmensaje
     *
     * @param \App\Entity\Mensaje $idmensaje
     *
     * @return Autor
     */
    public function addIdmensaje(\App\Entity\Mensaje $idmensaje)
    {
        $this->idmensaje[] = $idmensaje;

        return $this;
    }

    /**
     * Remove idmensaje
     *
     * @param \App\Entity\Mensaje $idmensaje
     */
    public function removeIdmensaje(\App\Entity\Mensaje $idmensaje)
    {
        $this->idmensaje->removeElement($idmensaje);
    }

    /**
     * Get idmensaje
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdmensaje()
    {
        return $this->idmensaje;
    }

    /**
     * @return \Autor
     */
    public function getJefe(): ?Autor
    {
        return $this->jefe;
    }

    /**
     * @param \Autor $jefe
     */
    public function setJefe(Autor $jefe = null): void
    {
        $this->jefe = $jefe;
    }


    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return Collection|self[]
     */
    public function getSeguidores(): Collection
    {
        return $this->seguidores;
    }

    public function addSeguidores(self $seguidore): self
    {
        if (!$this->seguidores->contains($seguidore)) {
            $this->seguidores[] = $seguidore;
        }

        return $this;
    }

    public function removeSeguidores(self $seguidore): self
    {
        if ($this->seguidores->contains($seguidore)) {
            $this->seguidores->removeElement($seguidore);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSeguidor(): Collection
    {
        return $this->seguidor;
    }

    public function addSeguidor(self $seguidor): self
    {
        if (!$this->seguidor->contains($seguidor)) {
            $this->seguidor[] = $seguidor;
            $seguidor->addSeguidore($this);
        }

        return $this;
    }

    public function removeSeguidor(self $seguidor): self
    {
        if ($this->seguidor->contains($seguidor)) {
            $this->seguidor->removeElement($seguidor);
            $seguidor->removeSeguidore($this);
        }

        return $this;
    }

    public function getRutaFoto(): ?string
    {
        return $this->rutaFoto;
    }

    public function setRutaFoto(?string $rutaFoto): self
    {
        $this->rutaFoto = $rutaFoto;

        return $this;
    }

    public function Upload($ruta)
    {
        if (null === $this->file) {
            return;
        }
        $fs = new Filesystem();
        $camino = $fs->makePathRelative($ruta, __DIR__);
        $directorioDestino = __DIR__ . DIRECTORY_SEPARATOR . $camino;
        $nombreArchivoFoto = uniqid('codice-') . '-' . $this->file->getClientOriginalName();
        $this->file->move($directorioDestino . DIRECTORY_SEPARATOR, $nombreArchivoFoto);
        $this->setRutaFoto($nombreArchivoFoto);
    }

    public function actualizarFoto($directorioDestino)
    {

        if (null !== $this->getFile()) {
            $this->removeUpload($directorioDestino);
            $this->Upload($directorioDestino);
        }
    }

    public function removeUpload($directorioDestino)
    {
        $fs = new Filesystem();
        $rutaPc = $directorioDestino . DIRECTORY_SEPARATOR . $this->getRutaFoto();
        if (null != $this->getRutaFoto() && $fs->exists($rutaPc)) {
            $fs->remove($rutaPc);
        }
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $array = [];
        foreach ($this->getIdrol()->toArray() as $value)
            $array[] = $value->getNombre();
        return $array;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getUsuario();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    public function __toString()
    {
        return $this->getNombre();
    }

    public function cicloInfinito($current, Autor $usuario)
    {
        if ($usuario->getJefe() != null) {
            if ($usuario->getJefe()->getId() == $current)
                return true;
            else
                return $this->cicloInfinito($current, $usuario->getJefe());
        }
        return false;
    }

    /*
     *Funcionalidad que indica si un usuario es subordinado de un susuario pasado por parametro
     */
    public function esSubordinado(Autor $usuario): bool
    {
        if($this->getId()==$usuario->getId())
            return false;
        elseif ($this->getJefe() == $usuario)
            return true;
        elseif (null != $this->getJefe())
            return $this->getJefe()->esSubordinado($usuario);

        return false;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        $roles = $this->getRoles();
        if (null == $this->getPais()) {
            $context->setNode($context, 'pais', null, 'data.pais');
            $context->addViolation('Seleccione un país');
        } elseif (null == $this->getMinisterio()) {
            $context->setNode($context, 'ministerio', null, 'data.ministerio');
            $context->addViolation('Seleccione un ministerio');
        } elseif (null == $this->getInstitucion()) {
            $context->setNode($context, 'institucion', null, 'data.institucion');
            $context->addViolation('Seleccione un centro de trabajo');
        }elseif (null == $this->getArea()) {
            $context->setNode($context, 'area', null, 'data.area');
            $context->addViolation('Seleccione un área');
        }

        if (null == $this->getGradoCientifico()) {
            $context->setNode($context, 'gradoCientifico', null, 'data.gradoCientifico');
            $context->addViolation('Seleccione un grado científico');
        }

        if (true == $this->cicloInfinito($this->getId(), $this)) {
            $context->setNode($context, 'nombre', null, 'data.nombre');
            $context->addViolation('Compruebe el jefe seleccionado.');
        }

        if ($this->getIdrol()->isEmpty())
            $context->buildViolation('Seleccione un rol')->atPath('idrol')->addViolation();
        elseif (in_array('ROLE_ADMIN', $roles)) {
            if ($this->getJefe() != null)
                $context->buildViolation('Un administrador no puede tener jefe')
                    ->atPath('idrol')
                    ->addViolation();
        } elseif (in_array('ROLE_USER', $roles)) {
            if (in_array('ROLE_DIRECTIVO', $roles))
                $context->buildViolation('Un usuario "Trabajador" no puede ser también "Directivo"')
                    ->atPath('idrol')
                    ->addViolation();
            elseif ($this->getJefe() == null)
                $context->buildViolation('Seleccione el jefe')
                    ->atPath('idrol')
                    ->addViolation();
        }
    }


}
