<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Todo;
use App\Form\TodoType;
use App\Repository\TodoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TodoController extends AbstractController
{
    /**
     * @Route("/", name="todo_index")
     */
    public function index(TodoRepository $todoRepository): Response
    {
        $todos = $todoRepository->findAll();
        dump($todos);
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
     * @Route("/todo/{id}/delete", name="todo_delete", methods={"DELETE"})
     */
    public function delete(Todo $todo): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($todo);
        $entityManager->flush();

        // Rediriger vers la page de la liste des tâches après la suppression
        return $this->redirectToRoute('todo_index');
    }
}
