<?php

namespace App\Controller;

use App\Repository\UserAnswerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
    /**
     * @Route("/ranking", name="app_ranking_index")
     */
    public function index(UserAnswerRepository $userAnswerRepository): Response
    {
        return $this->render('ranking/index.html.twig', [
            'ranking' => $userAnswerRepository->findRanking(),
        ]);
    }
}
