<?php

namespace App\Controller;

use App\Repository\PlaceRepository;
use App\Repository\UserAnswerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    /**
     * @Route("/", name="app_map_index")
     */
    public function index(PlaceRepository $placeRepository, UserAnswerRepository $userAnswerRepository): Response
    {
        $userAnswers = $userAnswerRepository->findByUser($this->getUser());
        $completedPlaces = [];
        foreach ($userAnswers as $userAnswer) {
            $completedPlaces[] = $userAnswer->getQuestion()->getPlace()->getId();
        }

        return $this->render('map/index.html.twig', [
            'places' => $placeRepository->findAll(),
            'completedPlaces' => $completedPlaces,
        ]);
    }
}
