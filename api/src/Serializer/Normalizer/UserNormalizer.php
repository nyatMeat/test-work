<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_NORMALIZER_ALREADY_CALLED';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param User $object
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $isOwner = $this->isOwner($object);

        if ($isOwner) {
            //If user is the same we adding serialization group in normalization context
            $context['groups'][] = 'user:owner:read';
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // avoid recursion: only call once per object
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }

    /**
     * Check is auth user is the same as user requested from api
     * @param User $user
     * @return bool
     */
    private function isOwner(User $user): bool
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->security->getUser();

        if (!$authenticatedUser) {
            return false;
        }

        return $authenticatedUser->getEmail() === $user->getEmail();
    }


    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
