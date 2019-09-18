<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\FollowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * User creation route
     * 
     * @Route("/api/users/new", methods={"POST"})
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

        $user->setPlainPassword($password);

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
     * @Route("/api/login", name="login", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function login(Request $request)
    {
        $user = $this->getUser();

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
        ]);
    }

    /**
     * @Route("/api/users/profile", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getUserInfo()
    {
        $user = $this->getUser();

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
        ]);
    }
}
