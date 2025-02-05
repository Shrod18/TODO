<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_index")
     */
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $filter = $request->query->get('filter');
        $search = $request->query->get('search');

        $queryBuilder = $userRepository->createQueryBuilder('t');

        if ($search) {
            $queryBuilder->andWhere('t.firstname LIKE :search OR t.lastname LIKE :search OR t.phone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $user = $queryBuilder->getQuery()->getResult();

        return $this->render('user/index.html.twig', [
            'users' => $user,
        ]);
    }

    /**
     * @Route("/user/new", name="user_new")
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit")
     */
    public function edit(int $id, UserRepository $userRepository, Request $request): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("Utilisateur introuvable !");
        }
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        // $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/user/{id}/delete", name="user_delete", methods={"POST", "DELETE"})
     */
    public function delete(Request $request, UserRepository $userRepository, int $id): Response
    {        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable !");
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
