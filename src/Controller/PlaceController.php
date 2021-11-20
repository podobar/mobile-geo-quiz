<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\UserAnswer;
use App\Repository\AnswerRepository;
use App\Repository\PlaceRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserAnswerRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlaceController extends AbstractController
{
    /**
     * @Route("/quiz/{id}", name="app_place_quiz", methods={"GET","POST"})
     */
    public function quiz(int $id, Request $request, PlaceRepository $placeRepository, QuestionRepository $questionRepository, AnswerRepository $answerRepository, UserAnswerRepository $userAnswerRepository, TranslatorInterface $translator): Response
    {
        $place = $placeRepository->findOneById($id);
        $form = $this->getForm($id, $questionRepository, $answerRepository);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $points = 0;
            foreach ($data as $question => $answer) {
                $question = $questionRepository->findOneById(substr($question, 8));
                if ($question->getCorrectAnswer()->getId() == $answer->getId()) {
                    $alreadyAnswered = $userAnswerRepository->findOneBy(['user' => $this->getUser(), 'question' => $question]);
                    if (!$alreadyAnswered) {
                        $entityManager = $this->getDoctrine()->getManager();
                        $userAnswer = new UserAnswer;
                        $userAnswer->setUser($this->getUser());
                        $userAnswer->setQuestion($question);
                        $entityManager->persist($userAnswer);
                        $entityManager->flush();

                        $points++;
                    }
                }
            }

            $this->addFlash('result', $translator->trans('score', ['points' => $points], 'messages'));

            return $this->redirectToRoute('app_place_quiz_result', ['id' => $id]);
        }

        return $this->render('place/quiz.html.twig', [
            'place' => $place,
        ]);
    }

    /**
     * @Route("/quiz_ajax/{id}", name="app_place_quiz_ajax", methods={"GET"})
     */
    public function quizAjax(int $id, PlaceRepository $placeRepository, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        $place = $placeRepository->findOneById($id);

        return $this->render('place/quiz_ajax.html.twig', [
            'form' => $this->getForm($id, $questionRepository, $answerRepository)->createView(),
        ]);
    }

    /**
     * @Route("/quiz_result/{id}", name="app_place_quiz_result", methods={"GET"})
     */
    public function quizResult(int $id, PlaceRepository $placeRepository): Response
    {
        $place = $placeRepository->findOneById($id);

        return $this->render('place/quiz_result.html.twig', [
            'place' => $place,
        ]);
    }

    private function getForm(int $placeId, QuestionRepository $questionRepository, AnswerRepository $answerRepository) {
        $form = $this->createFormBuilder(null, ['translation_domain' => false]);

        $questions = $questionRepository->findByPlace($placeId);
        foreach ($questions as $question) {
            /*$answers = $answerRepository->findByQuestion($question->getId());
            $answerChoices = [];
            foreach ($answers as $answer) {
                $answerChoices[$answer->getName()] = ($question->getCorrectAnswer()->getId() == $answer->getId());
            }*/

            $form
                ->add('question'.$question->getId(), EntityType::class, [
                    //'choices' => $answerChoices,
                    'choice_label' => 'name',
                    'class' => Answer::class,
                    'expanded' => true,
                    'label' => $question->getName(),
                    'label_attr' => [
                        'class' => 'font-weight-bold radio-custom',
                    ],
                    'query_builder' => function (EntityRepository $er) use ($question) {
                        return $er
                            ->createQueryBuilder('a')
                            ->where('a.question = :id')
                            ->setParameter('id', $question->getId())
                        ;
                    },
                ])
            ;
        }

        return $form->getForm();
    }
}
