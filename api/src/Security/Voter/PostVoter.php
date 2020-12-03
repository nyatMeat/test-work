<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Check access to the post
 * Class PostVoter
 * @package App\Security\Voter
 */
class PostVoter extends Voter
{

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['EDIT', 'VIEW', 'ADD'])
            && $subject instanceof Post;
    }

    /**
     * @param string $attribute
     * @param Post $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface)
        {
            return false;
        }


        // ... (check conditions and return true to grant permission) ...
        switch ($attribute)
        {
            case 'EDIT':
                if ($subject->getOwner() === $user)
                {
                    return true;
                }
                if ($this->security->isGranted("ROLE_ADMIN"))
                {
                    return true;
                }
                return false;

            case 'VIEW':
                // logic to determine if the user can VIEW
                // return true or false
                return true;
            case "ADD":
                return$this->security->isGranted('ROLE_USER');
        }

        throw new \DomainException('Invalid attribute: ' . $attribute);
    }
}
