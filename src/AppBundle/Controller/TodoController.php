<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todo;
use AppBundle\Form\Issue\IssueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TodoController extends Controller
{
    /**
     * @Route("/todo", name="todo_index")
     */
    public function todoAction()
    {
        $todos = $this->getDoctrine()->getRepository('AppBundle:Todo')->findAll();
        
        return $this->render('todo/index.html.twig', [
            'todos' => $todos,
        ]);
    }

    /**
     * @Route("/issue/create", name="create_issue")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $issue = new Todo();

        $form = $this->createForm(IssueType::class, $issue);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issue->setCreateDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($issue);
            $em->flush();

            $this->addFlash(
                'notice',
                'Issue Created'
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/issue/edit/{id}", name="edit_issue")
     * @param Request $request
     * @param $issue
     * @ParamConverter("issue", class="AppBundle:Todo")
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Todo $issue)
    {
        $form = $this->createForm(IssueType::class, $issue);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->flush();

            $this->addFlash(
                'notice',
                'Issue Updated'
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/edit.html.twig', [
            'todo' => $issue,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/todo/details/{id}", name="todo_details")
     * @param $id
     * @return Response
     */
    public function detailsAction($id)
    {
        $todo = $this->getDoctrine()->getRepository('AppBundle:Todo')->find($id);

        return $this->render('todo/details.html.twig', [
            'todo' => $todo,
        ]);
    }

    /**
     * @Route("/issue/delete/{id}", name="delete_issue")
     * @param $issue
     * @ParamConverter("issue", class="AppBundle:Todo")
     * @return RedirectResponse
     */
    public function deleteAction(Todo $issue)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($issue);
        $em->flush();

        $this->addFlash(
            'notice',
            'Issue Removed'
        );

        return $this->redirectToRoute('todo_index');
    }

    // todo Create a Sortable & Responsive Grid With Muuri
    // https://www.youtube.com/watch?v=PDG-GqmUZss
}
