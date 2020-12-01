<?php


namespace App\Doctrine;


use App\Entity\Post;
use Symfony\Component\Security\Core\Security;

class PostSetOwnerListener
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Post $post)
    {
        if ($post->getOwner())
        {
            return;
        }
        if ($this->security->getUser())
        {
            $post->setOwner($this->security->getUser());
        }
    }
}
