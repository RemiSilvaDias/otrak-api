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
use App\Repository\GenreRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\NetworkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api")
 */
class FollowingController extends AbstractController
{
    /**
     * @Route("/following/new/{id}/{status}/{showId}/{seasonNumber}/{episodeNumber}", requirements={"id"="\d+", "status"="\d+", "showId"="\d+", "seasonNumber"="\d+", "episodeNumber"="\d+"}, methods={"POST"})
     */
    public function new(User $user, $status, $showId, $seasonNumber, $episodeNumber, Request $request, ShowRepository $showRepository, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository, TypeRepository $typeRepository, GenreRepository $genreRepository, NetworkRepository $networkRepository, EntityManagerInterface $em)
    {
        $show = $showRepository->findOneByIdTvmaze($showId);

        if ($show === null) {
            $showApi = ApiController::retrieveData('get', 'show', $id);
        
            $show = new Show();
            
            $show->setName($showApi->name);
            $show->setSummary($showApi->summary);
            $show->setPoster($showApi->image->original);
            $show->setWebsite($showApi->officialSite);
            $show->setRating($showApi->rating->average);
            $show->setLanguage($showApi->language);
            $show->setRuntime($showApi->runtime);
            $show->setIdTvmaze($id);
            $show->setIdTvdb($showApi->externals->thetvdb);
            $show->setIdImdb($showApi->externals->imdb);
            $show->setApiUpdate($showApi->updated);

            $type = $typeRepository->findOneByName($showApi->type);
            if ($type === null) {
                $type = new Type();
                $type->setName($showApi->type);
                $em->persist($type);
            } else $show->setType($type);

            $network = $networkRepository->findOneByName($showApi->network->name);
            if ($network === null) {
                $network = new Network();
                $network->setName($showApi->network->name);
                $em->persist($network);
            } else $show->setNetwork($network);

            foreach ($showApi->genres as $currentGenre) {
                $genre = $genreRepository->findOneByName($currrentGenre);

                if ($genre === null) {
                    $genre = new Genre();
                    $genre->setName($currentGenre);
                    $em->persist($genre);
                    $show->addGenre($genre);
                } else $show->addGenre($genre);
            }

            $em->persist($show);

            $seasonNumber = 1;
            foreach ($showApi->_embedded->seasons as $currentSeason) {
                $season = new Season();

                $season->setNumber($currentSeason->number);
                $season->setPoster($currentSeason->image->original);
                $season->setEpisodeCount($currentSeason->episodeOrder);
                $season->setPremiereDate($currentSeason->premiereDate);
                $season->setEndDate($currentSeason->endDate);
                $season->setTvShow($show);

                $episodes = new ArrayCollection();

                foreach ($showApi->_embedded->episodes as $currentEpisode) {
                    if ($currentEpisode->season == $seasonNumber) {
                        $episode = new Episode();

                        $episode->setName($currentEpisode->name);
                        $episode->setNumber($currentEpisode->number);
                        $episode->setRuntime($currentEpisode->runtime);
                        $episode->setSummary($currentEpisode->summary);
                        $episode->setAirstamp($currentEpisode->airstamp);
                        $episode->setImage($currentEpisode->image->original);
                        $episode->setSeason($season);

                        $em->persist($episode);
                    }
                }

                $em->persist($season);

                $seasonNumber++;
            }

            $em->flush();
        }

        $following = new Following();

        $following->setStartDate(new \DateTime());
        $following->setStatus($status);
        $following->setUser($user);
        $following->setTvShow($show);

        $season = $seasonRepository->findSeasonByShow($show, $seasonNumber);
        $following->setSeason($season);
        
        $following->setEpisode($episodeRepository->findEpisode($show, $season, $episodeNumber));

        $em->persist($following);
        $em->flush();

        $jsonResponse = new JsonResponse(['response' => 'success']);
        
        return $jsonResponse;
    }
}
