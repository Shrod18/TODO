<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Todo;
use App\Form\TodoType;
use App\Repository\TodoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TodoController extends AbstractController
{
    /**
     * @Route("/", name="todo_index")
     */
    public function index(TodoRepository $todoRepository, Request $request): Response
    {

        $filter = $request->query->get('filter'); 
        $search = $request->query->get('search'); 

        $queryBuilder = $todoRepository->createQueryBuilder('t');

        if ($filter === 'done') {
            $queryBuilder->andWhere('t.done = :done')->setParameter('done', true);
        } elseif ($filter === 'pending') {
            $queryBuilder->andWhere('t.done = :done')->setParameter('done', false);
        }

        if ($search) {
            $queryBuilder->andWhere('t.title LIKE :search OR t.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $todos = $queryBuilder->getQuery()->getResult();


        return $this->render('todo/index.html.twig', [
            'todos' => $todos,
        ]);
    }

    /**
     * @Route("/todo/new", name="todo_new")
     */
    public function create(Request $request): Response
    {
        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($todo);
            $entityManager->flush();

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/todo/{id}/edit", name="todo_edit")
     */
    public function edit(int $id, TodoRepository $todoRepository, Request $request): Response
    {
        $todo = $todoRepository->find($id);
        if (!$todo) {
            throw new NotFoundHttpException("Tâche introuvable !");
        }

        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/update.html.twig', [
            'todo' => $todo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/todo/{id}/delete", name="todo_delete", methods={"POST", "DELETE"})
     */
    public function delete(Request $request, TodoRepository $todoRepository, int $id): Response
    {
        $todo = $todoRepository->find($id);
        if (!$todo) {
            throw $this->createNotFoundException("Tâche introuvable !");
        }

        if ($this->isCsrfTokenValid('delete' . $todo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($todo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('todo_index');
    }

}
