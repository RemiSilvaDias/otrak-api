<?php

namespace App\Command;

use App\Controller\ApiController;
use App\Repository\UserRepository;
use App\Repository\FollowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OtrakUpdateFollowingsCommand extends Command
{
    public const STATUS_IN_DEVELOPMENT = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_ENDED = 2;
    
    public const TRACKING_WATCHING = 0;
    public const TRACKING_COMPLETED = 1;
    public const TRACKING_SEE_NEXT = 2;
    public const TRACKING_UPCOMING = 3;
    public const TRACKING_STOPPED = 4;
    
    protected static $defaultName = 'otrak:update:followings';

    private $userRepository;
    private $followingRepository;
    private $em;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, FollowingRepository $followingRepository)
    {
        $this->userRepository = $userRepository;
        $this->followingRepository = $followingRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update the tracking status of the shows of the users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $userFollowings = $this->followingRepository->findBy(['user' => $user, 'season' => null, 'episode' => null]);

            foreach ($userFollowings as $following) {
                if ($following->getStatus() == self::TRACKING_UPCOMING || $following->getStatus() == self::TRACKING_COMPLETED) {
                    if ($following->getTvShow()->getStatus() == self::STATUS_RUNNING && !is_null($following->getTvShow()->getSeasons()->last()->getEpisodes()->first()->getAirstamp()) && $following->getTvShow()->getSeasons()->last()->getEpisodes()->first()->getAirstamp() < new \DateTime()) {
                        $following->setStatus(self::TRACKING_WATCHING);
                        $this->em->persist($following);
                    }
                }
            }
        }

        $this->em->flush();

        $io->success('The trackings have been updated.');
    }
}
