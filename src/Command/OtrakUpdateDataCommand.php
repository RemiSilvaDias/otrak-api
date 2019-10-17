<?php

namespace App\Command;

use App\Entity\Type;
use App\Entity\Genre;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Network;
use App\Controller\ApiController;
use App\Repository\EpisodeRepository;
use App\Repository\GenreRepository;
use App\Repository\NetworkRepository;
use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OtrakUpdateDataCommand extends Command
{
    public const STATUS_IN_DEVELOPMENT = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_ENDED = 2;

    public const TRACKING_WATCHING = 0;
    public const TRACKING_COMPLETED = 1;
    public const TRACKING_SEE_NEXT = 2;
    public const TRACKING_UPCOMING = 3;
    public const TRACKING_STOPPED = 4;
    
    protected static $defaultName = 'otrak:update:data';

    private $showRepository;
    private $genreRepository;
    private $typeRepository;
    private $networkRepository;
    private $seasonRepository;
    private $episodeRepository;
    private $em;

    public function __construct(EntityManagerInterface $em, ShowRepository $showRepository, GenreRepository $genreRepository, TypeRepository $typeRepository, NetworkRepository $networkRepository, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository)
    {
        $this->showRepository = $showRepository;
        $this->genreRepository = $genreRepository;
        $this->typeRepository = $typeRepository;
        $this->networkRepository = $networkRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update the shows registered in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $shows = $this->showRepository->findAll();

        foreach ($shows as $show) {
            $showApi = ApiController::retrieveData('get', 'showComplete', $show->getIdTvmaze());

            if ($show->getApiUpdate() != $showApi->updated) {
                $io->note(sprintf($show->getName() . ' is getting updated...'));

                $summary = '';
                if (!is_null($showApi->summary)) $summary = $showApi->summary;
                $show->setSummary($summary);

                $poster = '';
                if (!is_null($showApi->image)) {
                    $poster = \str_replace('http://', 'https://', $showApi->image->original);
                }
                $show->setPoster($poster);

                $website = null;
                if (!is_null($showApi->officialSite)) $website = $showApi->officialSite;
                $show->setWebsite($website);

                $rating = 0;
                if (!is_null($showApi->rating)) $rating = $showApi->rating->average;
                $show->setRating($rating);

                $language = '';
                if (!is_null($showApi->language)) $language = $showApi->language;
                $show->setLanguage($language);

                $runtime = 0;
                if (!is_null($showApi->runtime)) $runtime = $showApi->runtime;
                $show->setRuntime($runtime);

                $tvdb = null;
                $imdb = null;
                if (!is_null($showApi->externals)) {
                    if (!is_null($showApi->externals->thetvdb)) $tvdb = $showApi->externals->thetvdb;
                    $show->setIdTvdb($tvdb);

                    if (!is_null($showApi->externals->imdb)) $imdb = $showApi->externals->imdb;
                    $show->setIdImdb($imdb);
                }

                $premiered = null;
                if (!is_null($showApi->premiered)) $premiered = $showApi->premiered;
                $show->setPremiered($premiered);

                $show->setApiUpdate($showApi->updated);

                $show->setStatus(self::STATUS_ENDED);

                switch ($showApi->status) {
                    case 'In Development':
                        $show->setStatus(self::STATUS_IN_DEVELOPMENT);
                        break;

                    case 'Running':
                        $show->setStatus(self::STATUS_RUNNING);
                        break;
                }

                if (!is_null($showApi->type)) {
                    $type = $this->typeRepository->findOneByName($showApi->type);
                    if (is_null($type)) {
                        $type = new Type();
                        $type->setName($showApi->type);
                        $type->addShow($show);
                        $this->em->persist($type);

                        $show->setType($type);
                    } else $show->setType($type);
                }

                $network = null;
                if (!is_null($showApi->network)) {
                    $networkDb = $this->networkRepository->findOneByName($showApi->network->name);
                    if (is_null($networkDb)) {
                        $network = new Network();
                        $network->setName($showApi->network->name);
                        $network->addShow($show);
                        $this->em->persist($network);

                        $show->setNetwork($network);
                    } else $show->setNetwork($networkDb);
                } else if (!is_null($showApi->webChannel)) {
                    $networkDb = $this->networkRepository->findOneByName($showApi->webChannel->name);
                    if (is_null($networkDb)) {
                        $network = new Network();
                        $network->setName($showApi->webChannel->name);
                        $network->addShow($show);
                        $this->em->persist($network);

                        $show->setNetwork($network);
                    } else $show->setNetwork($networkDb);
                }

                foreach ($showApi->genres as $currentGenre) {
                    $genre = $this->genreRepository->findOneByName($currentGenre);

                    if (is_null($genre)) {
                        $genre = new Genre();
                        $genre->setName($currentGenre);
                        $genre->addShow($show);
                        $this->em->persist($genre);

                        $show->addGenre($genre);
                    } else $show->addGenre($genre);
                }

                $this->em->persist($show);

                $seasonIndex = 1;
                foreach ($showApi->_embedded->seasons as $currentSeason) {
                    $season = $this->seasonRepository->findSeasonByShow($show, $currentSeason->number);

                    if (is_null($season)) $season = new Season();

                    $season->setNumber($currentSeason->number);
                    $seasonIndex = $currentSeason->number;

                    $seasonPoster = '';
                    if (!is_null($currentSeason->image)) {
                        $seasonPoster = \str_replace('http://', 'https://', $currentSeason->image->original);
                    }
                    $season->setPoster($seasonPoster);

                    $seasonEpisodeCount = 0;
                    if (!is_null($currentSeason->episodeOrder)) $seasonEpisodeCount = $currentSeason->episodeOrder;
                    $season->setEpisodeCount($seasonEpisodeCount);

                    $seasonStartDate = null;
                    if (!is_null($currentSeason->premiereDate)) $seasonStartDate = new \DateTime($currentSeason->premiereDate);
                    $season->setPremiereDate($seasonStartDate);

                    $seasonEndDate = new \DateTime($currentSeason->endDate);
                    $season->setEndDate($seasonEndDate);

                    $season->setTvShow($show);

                    $this->em->persist($season);

                    foreach ($showApi->_embedded->episodes as $currentEpisode) {
                        if ($currentEpisode->season == $seasonIndex) {
                            $episode = $this->episodeRepository->findEpisodeBySeason($season, $currentEpisode->number);
                            
                            if (is_null($episode)) $episode = new Episode();

                            $episode->setName($currentEpisode->name);
                            $episode->setNumber($currentEpisode->number);

                            $runtime = 0;
                            if (!is_null($currentEpisode->runtime)) $runtime = $currentEpisode->runtime;
                            $episode->setRuntime($runtime);

                            $episodeSummary = '';
                            if (!is_null($currentEpisode->summary)) $episodeSummary = $currentEpisode->summary;
                            $episode->setSummary($episodeSummary);

                            $episodeAirstamp = new \DateTime($currentEpisode->airstamp);
                            $episode->setAirstamp($episodeAirstamp);

                            $episodeImage = '';
                            if (!is_null($currentEpisode->image)) {
                                $episodeImage = \str_replace('http://', 'https://', $currentEpisode->image->original);
                            }
                            $episode->setImage($episodeImage);

                            $episode->setSeason($season);

                            $this->em->persist($episode);
                        }
                    }

                    $seasonIndex++;
                }

                $this->em->flush();
                $io->note(sprintf($show->getName() . ' has been updated'));
            }
        }

        $io->success('All shows have been updated.');
    }
}
