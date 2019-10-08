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
        $username = '';
        $email = '';
        $password = '';

        $data = $request->getContent();

        if (!empty($data)) {
            $decodedData = \json_decode($data, true);

            $username = $decodedData['username'];
            $email = $decodedData['email'];
            $password = $decodedData['password'];
        }

        $user = $userRepository->findOneByEmail($email);

        if (!is_null($user)) {
            return new JsonResponse([
                'message' => 'Email already exists'
            ], 409);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);

        $user->setPlainPassword($password);

        $roleUser = $roleRepository->findOneByName('User');
        $user->setRole($roleUser);

        $em->persist($user);
        $em->flush();

        $jsonResponse = new JsonResponse([
            'message' => 'success'
        ], 200);

        return $jsonResponse;
    }

    /**
     * Get shows followed by user
     *
     * @Route("/api/users/me/followings/shows", name="shows_following", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showsFollowing(FollowingRepository $followingRepository)
    {
        $showsJson = [];
        $user = $this->getUser();

        $showsFollowing = $followingRepository->findBy(['user' => $user, 'season' => null, 'episode' => null], ['id' => 'DESC']);

        foreach ($showsFollowing as $show) {
            $latestFollow = $followingRepository->findOneBy(['user' => $show->getUser(), 'tvShow' => $show->getTvShow()], ['id' => 'DESC']);
            $latestFollowSeason = 0;
            $latestFollowEpisode = 0;
            
            if (null !== $latestFollow->getEpisode()) {
                $latestFollowSeason = $latestFollow->getEpisode()->getSeason()->getNumber();
                $latestFollowEpisode = $latestFollow->getEpisode()->getNumber();
            }

            $showJson =  $this->get('serializer')->serialize($show, 'json');
            $showJson = \json_decode($showJson);

            $showJson->latestFollowSeason = $latestFollowSeason;
            $showJson->latestFollowEpisode = $latestFollowEpisode;
            $showJson->idTvmaze = $show->getTvShow()->getIdTvmaze();

            $showsJson[] = $showJson;
        }

        return new JsonResponse($showsJson, 200);
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
        ], 200);
    }

    /**
     * Return user info
     * 
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
        ], 200);
    }
}
