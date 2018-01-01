<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todo;
use AppBundle\Form\Issue\IssueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @Route("/todo/edit/{id}", name="todo_edit")
     * @param Request $request
     * @param $todo
     * @ParamConverter("todo", class="AppBundle:Todo")
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Todo $todo)
    {
        $form = $this->createFormBuilder($todo)
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('category', TextType::class, ['attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('priority', ChoiceType::class, ['choices' => ['Low' => 'Low', 'Normal' => 'Normal', 'High' => 'High'], 'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('due_date', DateTimeType::class, ['attr' => ['class' => 'formcontrol', 'style' => 'margin-bottom:15px']])
            ->add('save', SubmitType::class, ['label' => 'Update Todo', 'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-bottom:15px']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get Data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $dueDate = $form['due_date']->getData();

            $now = new \DateTime('now');

            $em = $this->getDoctrine()->getManager();

            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($dueDate);
            $todo->setCreateDate($now);


            $em->flush();

            $this->addFlash(
                'notice',
                'Todo Updated'
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/edit.html.twig', [
            'todo' => $todo,
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
