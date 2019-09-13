<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @route("/api")
 */
class UserController extends AbstractController
{
    /**
     * User creation route
     * 
     * @Route("/users/new", methods={"POST"})
     */
    public function new(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder, RoleRepository $roleRepository, EntityManagerInterface $em)
    {
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $userRepository->findOneByEmail($email);

        if (!is_null($user)) {
            return new JsonResponse([
                'response' => 'error',
                'message' => 'Email already exists'
            ]);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);

        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);

        $user->setCreatedAt(new \DateTime());

        $roleUser = $roleRepository->findOneByName('User');
        $user->setRole($roleUser);

        $em->persist($user);
        $em->flush();

        $jsonResponse = new JsonResponse(['response' => 'success']);

        return $jsonResponse;
    }

    /**
     * User login route
     * 
     * @Route("/login_check", name="login", methods={"POST"})
     */
    public function login(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'username' => $user->getUsername(),
            'role' => $user->getRoles(),
        ]);
    }
}
