<?php

namespace App\Controller;

use App\Entity\Show;
use App\Repository\ShowRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api")
 */
class ShowController extends AbstractController
{
    private $showHandler;
    private $showRepository;

    public function __construct(ShowRepository $showRepository)
    {
        // $this->showHandler = $showHandler;
        $this->showRepository = $showRepository;
    }
    
    /**
     * @Route(
     *      name="get_show",
     *      path="/shows/{id}",
     *      requirements={"id"="\d+"},
     *      methods={"GET"},
     *      defaults={
     *          "_api_resource_class"=Show::class
     * })
     */
    public function __invoke(Show $data)
    {
        // $this->showHandler->handle($data);
        return new JsonResponse($this->showRepository->findOneByIdTvmaze($data));
    }
}
