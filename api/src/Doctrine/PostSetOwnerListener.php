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

    /**
     * Execute operation for Post entity pre persist
     * @param Post $post
     */
    public function prePersist(Post $post)
    {
        if ($post->getOwner())
        {
            return;
        }

        //Here we associate created post with authenticated user
        if ($this->security->getUser())
        {
            $post->setOwner($this->security->getUser());
        }
    }
}
