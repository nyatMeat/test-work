<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ValidatorInterface
     */
    private $validator;


    public function __construct(UserRepository $userRepository, ValidatorInterface $validator,
                                UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }


    public function register(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email'], $data['password']))
        {
            return new JsonResponse(['errors' => ["Invalid data"]]);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if ($user)
        {
            return new JsonResponse(['errors' => ["User with email already exists"]], Response::HTTP_CONFLICT);
        }
        $user = (new User())
            ->setEmail($data['email']);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
        $validation = $this->validator->validate($user);
        if ($validation->count() !== 0)
        {
            $errors = [];
            /** @var ConstraintViolationInterface $value */
            foreach ($validation as $value)
            {
                $errors[] = $value->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
