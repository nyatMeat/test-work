<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\Timestampable;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *     normalizationContext={"groups"={"user:read"}},
 *     collectionOperations={
 *           "get"
 *     },
 *     itemOperations={
 *          "get" = {
 *               "normalization_context" = {
 *                  "groups"={"user:read:get_element"}
 *              }
 *          },
 *          "patch" = {
 *              "denormalization_context" ={"groups"={"user:write"}},
 *              "security" = "object == user",
 *              "security_message" = "Only owner can update his own profile"
 *          }
 *     },
 *     )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{

    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user:read","post:read"})
     */
    private $id;

    /**
     * @Groups({"user:owner:read", "admin:read"})
     * @Assert\Email()
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @Groups({"admin:read", "admin:write"})
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * User full name in system
     *
     * @Groups({"user:write", "user:read", "post:read"})
     * @Assert\NotBlank
     * @Assert\Length(max="180")
     * @var string
     * @ORM\Column(type="string", length=180)
     */
    private $fullName = '';

    /**
     * @Assert\NotBlank()
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * Posts have been written by user
     *
     * @Groups({"user:read:get_element"})
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="owner")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post))
        {
            $this->posts[] = $post;
            $post->setOwner($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post))
        {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getOwner() === $this)
            {
                $post->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return $this
     */
    public function setFullName(string $fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }


}
