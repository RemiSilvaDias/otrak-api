<?php

namespace App\Controller;

use App\Entity\Show;
use App\Entity\Type;
use App\Entity\User;
use App\Entity\Genre;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Network;
use App\Entity\Following;
use App\Repository\ShowRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\GenreRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\NetworkRepository;
use App\Repository\FollowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api")
 */
class FollowingController extends AbstractController
{
    public const STATUS_IN_DEVELOPMENT = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_ENDED = 2;

    public const TRACKING_WATCHING = 0;
    public const TRACKING_COMPLETED = 1;
    public const TRACKING_SEE_NEXT = 2;
    public const TRACKING_UPCOMING = 3;
    public const TRACKING_STOPPED = 4;

    /**
     * Route to start the tracking of a show
     * Add the show to the database if it doesn't exist
     * 
     * @Route("/followings/new/{id}/{status}/{showId}/{seasonNumber}/{episodeNumber}", requirements={"id"="\d+", "status"="\d+", "showId"="\d+", "seasonNumber"="\d+", "episodeNumber"="\d+"}, methods={"POST"})
     */
    public function new($id, $status, $showId, $seasonNumber, $episodeNumber, Request $request, UserRepository $userRepository, ShowRepository $showRepository, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository, FollowingRepository $followingRepository, TypeRepository $typeRepository, GenreRepository $genreRepository, NetworkRepository $networkRepository, EntityManagerInterface $em)
    {
        if ($id != $this->getUser()->getId()) return new JsonResponse(['message' => "You can't perform this action for another user"], 401);

        $show = $showRepository->findOneBy(['id_tvmaze' => $showId]);

        /* Add show to the database if not found */
        if (is_null($show)) {
            $showApi = ApiController::retrieveData('get', 'showComplete', $showId);
        
            $show = new Show();
            
            $show->setName($showApi->name);

            $summary = '';
            if (!is_null($showApi->officialSite)) $summary = $showApi->summary;
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

            $runtime = null;
            if (!is_null($showApi->runtime)) $runtime = $showApi->runtime;
            $show->setRuntime($runtime);

            $show->setIdTvmaze($showId);

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
                $type = $typeRepository->findOneByName($showApi->type);
                if (is_null($type)) {
                    $type = new Type();
                    $type->setName($showApi->type);
                    $type->addShow($show);
                    $em->persist($type);

                    $show->setType($type);
                } else $show->setType($type);
            }

            $network = null;
            if (!is_null($showApi->network)) {
                $networkDb = $networkRepository->findOneByName($showApi->network->name);
                if (is_null($networkDb)) {
                    $network = new Network();
                    $network->setName($showApi->network->name);
                    $network->addShow($show);
                    $em->persist($network);

                    $show->setNetwork($network);
                } else $show->setNetwork($networkDb);
            } else if (!is_null($showApi->webChannel)) {
                $networkDb = $networkRepository->findOneByName($showApi->webChannel->name);
                if (is_null($networkDb)) {
                    $network = new Network();
                    $network->setName($showApi->webChannel->name);
                    $network->addShow($show);
                    $em->persist($network);

                    $show->setNetwork($network);
                } else $show->setNetwork($networkDb);
            }

            foreach ($showApi->genres as $currentGenre) {
                $genre = $genreRepository->findOneByName($currentGenre);

                if (is_null($genre)) {
                    $genre = new Genre();
                    $genre->setName($currentGenre);
                    $genre->addShow($show);
                    $em->persist($genre);

                    $show->addGenre($genre);
                } else $show->addGenre($genre);
            }

            $em->persist($show);

            $seasonIndex = 1;
            foreach ($showApi->_embedded->seasons as $currentSeason) {
                $season = new Season();

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

                $em->persist($season);

                $show->addSeason($season);

                foreach ($showApi->_embedded->episodes as $currentEpisode) {
                    if ($currentEpisode->season == $seasonIndex) {
                        $episode = new Episode();

                        $episode->setName($currentEpisode->name);
                        $episode->setNumber($currentEpisode->number);
                        $episode->setRuntime($currentEpisode->runtime);

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

                        $season->addEpisode($episode);

                        $em->persist($episode);
                    }
                }

                $seasonIndex++;
            }

            $em->flush();
        }

        $user = $userRepository->find($id);

        $showTracking = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $show, 'season' => null, 'episode' => null]);

        // Add the tracking of the show
        if (is_null($showTracking)) {
            $following = new Following();

            $following->setUser($user);

            $following->setStartDate(new \DateTime());
            $following->setStatus($status);

            $following->setTvShow($show);

            $em->persist($following);
        }

        /* Add to the tracking the episodes specified in the request */
        if ($seasonNumber > 0 && ($status <= self::TRACKING_COMPLETED || $status == self::TRACKING_STOPPED)) {
            foreach ($show->getSeasons() as $seasonShow) {
                if ($seasonShow->getNumber() <= $seasonNumber) {
                    if($seasonShow->getNumber() == $seasonNumber) {
                        foreach ($seasonShow->getEpisodes() as $episodeShow) {
                            $checkEpisodeTrackingStatus = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $show, 'season' => $seasonShow, 'episode' => $episodeShow]);
                            
                            if (is_null($checkEpisodeTrackingStatus) && $episodeShow->getAirstamp() < new \DateTime() && $episodeShow->getNumber() <= $episodeNumber) {
                                $following = new Following();
                                
                                $following->setUser($user);
                                $following->setStartDate(new \DateTime());
                                $following->setStatus(self::TRACKING_COMPLETED);
                                $following->setTvShow($show);
                                $following->setSeason($seasonShow);
                                
                                $following->setEpisode($episodeShow);
                                $em->persist($following);
                            }
                        }
                    } else {
                        foreach ($seasonShow->getEpisodes() as $episodeShow) {
                            $checkEpisodeTrackingStatus = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $show, 'season' => $seasonShow, 'episode' => $episodeShow]);
                            
                            if (is_null($checkEpisodeTrackingStatus) && $episodeShow->getAirstamp() < new \DateTime()) {
                                $following = new Following();
                                
                                $following->setUser($user);
                                $following->setStartDate(new \DateTime());
                                $following->setStatus(self::TRACKING_COMPLETED);
                                $following->setTvShow($show);
                                $following->setSeason($seasonShow);

                                $following->setEpisode($episodeShow);
                                $em->persist($following);
                            }
                        }
                    }
                }
            }
        }

        $em->flush();

        $lastSeason = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $show], ['id' => 'DESC']);

        /* Auto setup the status of the tracking of the show related to the tracking of the episode */
        if (!is_null($lastSeason->getSeason())) {
            if ($lastSeason->getSeason()->getNumber() == $show->getSeasons()->count() && $lastSeason->getEpisode()->getNumber() == $lastSeason->getSeason()->getEpisodes()->last()->getNumber()) {
                $showTracking = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $show, 'season' => null, 'episode' => null]);

                $showTracking->setStatus(self::TRACKING_COMPLETED);
                $showTracking->setEndDate(new \DateTime());

                $em->persist($showTracking);
                $em->flush();
            } else if ($show->getStatus() == self::STATUS_RUNNING && !is_bool($show->getSeasons()->last()->getPremiereDate()) && (is_null($show->getSeasons()->last()->getPremiereDate()) || $show->getSeasons()->last()->getEpisodeCount() == 0 || $show->getSeasons()->last()->getPremiereDate() > new \DateTime() || is_null($show->getSeasons()->last()->getEpisodes()->first()->getAirstamp()) || (!is_null($show->getSeasons()->last()->getEpisodes()->first()->getAirstamp()) && $show->getSeasons()->last()->getEpisodes()->first()->getAirstamp() > new \DateTime()))) {
                $previousSeasonId = $show->getSeasons()->last()->getId() - 1;

                if ($lastSeason->getSeason()->getId() == $previousSeasonId) {
                    $showTracking = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $show, 'season' => null, 'episode' => null]);

                    $showTracking->setStatus(self::TRACKING_UPCOMING);

                    $em->persist($showTracking);
                    $em->flush();
                }
            }
        }

        $em->clear();

        $jsonResponse = new JsonResponse([
            'message' => 'success'
        ], 200);
        
        return $jsonResponse;
    }
}
