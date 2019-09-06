<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $roleRepository;

    public function __construct(TokenStorageInterface $tokenStorage, RoleRepository $roleRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->roleRepository = $roleRepository;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['initUser', EventPriorities::PRE_WRITE],
        ];
    }

    public function initUser(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User) {
            return;
        }

        if (Request::METHOD_PUT === $method) {
            $role = $this->roleRepository->findOneByName('User');

            $user->setCreatedAt(new \DateTime());
            $user->setRole($role);
        }

        if (Request::METHOD_POST === $method) $user->setUpdatedAt(new \DateTime());
    }
}
