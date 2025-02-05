<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category_index")
     */
    public function index(CategoryRepository $categoryRepository, Request $request): Response
    {
        $filter = $request->query->get('filter');
        $search = $request->query->get('search');

        $queryBuilder = $categoryRepository->createQueryBuilder('t');

        if ($search) {
            $queryBuilder->andWhere('t.name LIKE :search OR t.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $categories = $queryBuilder->getQuery()->getResult();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/new", name="category_new")
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/{id}/edit", name="category_edit")
     */
    public function edit(int $id, CategoryRepository $categoryRepository, Request $request): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw new NotFoundHttpException("Categorie introuvable !");
        }
        $form = $this->createForm(CategoryType::class, $category, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/category/{id}/delete", name="category_delete", methods={"POST", "DELETE"})
     */
    public function delete(Request $request, CategoryRepository $categoryRepository, int $id): Response
    {   
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException("Catégorie introuvable !");
        }
    
        foreach ($category->getTodos() as $todo) {
            $todo->setCategory(null);
        }
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush(); 
    
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('category_index');
    }
}
