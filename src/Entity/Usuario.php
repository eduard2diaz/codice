<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Usuario implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $rutaFoto;

    /**
     * @ORM\Column(type="boolean")
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
     * @Assert\Image()
     */
    private $file;

    /**
     * Usuario constructor.
     */
    public function __construct()
    {
        $this->setActivo(true);
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password=null): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRutaFoto(): ?string
    {
        return $this->rutaFoto;
    }

    public function setRutaFoto(string $rutaFoto): self
    {
        $this->rutaFoto = $rutaFoto;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
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
        return ['ROLE_SUPERADMIN'];
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
        // TODO: Implement eraseCredentials() method.
    }
}
