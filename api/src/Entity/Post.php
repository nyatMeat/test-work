<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Traits\Timestampable;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\PostRepository;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ApiResource(
 *     attributes={
 *          "pagination_items_per_page"=30,
 *          "order"={"createdAt": "DESC"}
 *     },
 *     normalizationContext={"groups"={"post:read"}},
 *     denormalizationContext={"groups"={"post:write"}},
 *     collectionOperations={
 *      "get",
 *      "post" = {
 *              "security" = "is_granted('ROLE_USER')",
 *              "security_message" = "Only authenticated user can add posts"
 *          }
 *     },
 *     itemOperations={
 *      "get",
 *      "put" = {
 *            "security" = "is_granted('EDIT', previous_object)"
 *          },
 *      "delete" = {
 *           "security" = "is_granted('EDIT', previous_object)"
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiFilter(OrderFilter::class, properties={"createdAt", "updatedAt", "title"})
 * @ApiFilter(SearchFilter::class, properties={"title": "partial", "body": "partial"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "updatedAt"})
 * @ApiFilter(PropertyFilter::class)
 * @ORM\EntityListeners({"App\Doctrine\PostSetOwnerListener"})
 */
class Post
{
    private const SHORT_BODY_LENGTH = 200; //The length for short body field

    use Timestampable;
    /**
     * @Groups("post:read")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="255")
     * @Groups({"post:read", "post:write"})
     * The title of the post
     * @ORM\Column(type="string", length=255)
     */
    private $title;


    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="4096")
     * @Groups({"post:read", "post:write"})
     * The string body of the post
     * @ORM\Column(type="string", length=4096)
     */
    private $body;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Human readable time diff when post was created
     * @Groups({"post:read"})
     * @return string
     */
    public function getCreatedAtReadable()
    {
        return Carbon::instance($this->createdAt)->diffForHumans();
    }

    /**
     * Human readable time diff when post was updated last time
     * @Groups({"post:read"})
     * @return string
     */
    public function getUpdatedAtReadable()
    {
        return Carbon::instance($this->updatedAt)->diffForHumans();
    }

    /**
     * Part of body
     * @Groups({"post:read"})
     * @return string
     */
    public function getShortBody()
    {
        if (strlen($this->body) < self::SHORT_BODY_LENGTH)
        {
            return $this->body;
        }

        return substr($this->body, 0, self::SHORT_BODY_LENGTH) . '...';
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @Groups("post:read")
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @Groups("post:read")
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }


}
