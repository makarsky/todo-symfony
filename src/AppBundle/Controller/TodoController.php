<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ResetPassword;
use AppBundle\Entity\Todo;
use AppBundle\Entity\User;
use AppBundle\Form\User\RecoveryPasswordType;
use AppBundle\Form\User\ResetPasswordType;
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
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        return $this->render('auth/start.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        $helper = $this->get('security.authentication_utils');
        
        return $this->render('auth/login.html.twig', [
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/reset_password", name="reset_password")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function resetPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userRep = $em->getRepository('AppBundle:User');
        $form = $this->createForm(ResetPasswordType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $form->get('email')->getData();
            $user = $userRep->findOneBy(['email' => $email]);
            
            if (!is_null($user)) {
                $resetPassword = new ResetPassword();
                $resetPassword->setEmail($email);
                $hash = md5(uniqid(null, true));
                $resetPassword->setHashKey($hash);

                $message = \Swift_Message::newInstance()
                    ->setSubject('Password recovery')
                    ->setFrom('catalog@gmail.com')
                    ->setTo($email)
                    ->setBody('To reset you password please 
                    follow this link http://localhost:8000/password_recovery/' . $hash);
                
                $this->get('mailer')->send($message);
                $em->persist($resetPassword);
                $em->flush();
                $this->addFlash('notice', 'Instructions were sent to you email!');
            } else {
                $this->addFlash('notice', 'User with that email not found!');
                
                return $this->redirectToRoute('reset_password');
            }
        }
        
        return $this->render('auth/reset_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/password_recovery/{hash}", name="password_recovery")
     * @param Request $request
     * @param $hashKey
     * @return RedirectResponse|Response
     */
    public function passwordRecoveryAction(Request $request, $hashKey)
    {
        $em = $this->getDoctrine()->getManager();
        $userRep = $em->getRepository(User::class);
        $resetRep = $em->getRepository(ResetPassword::class);

        $forgetter = $resetRep->findOneBy(['hashKey' => $hashKey]);

        if (!is_null($forgetter)) {
            $form = $this->createForm(RecoveryPasswordType::class);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $userRep->findOneBy(['email' => $forgetter->getEmail()]);
                $encoder = $this->get('security.password_encoder');
                $user->setPassword($encoder->encodePassword(
                    $user,
                    $form->get('new_password')->getData()
                ));
                $em->remove($forgetter);
                $em->persist($user);
                $em->flush();
                $this->addFlash('notice', 'Your password has been reset successfully!');
                return $this->redirectToRoute('login');
            }
            
            return $this->render('auth/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('index');
        }
    }

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
     * @Route("/todo/create", name="todo_create")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $todo = new Todo();

        $form = $this->createFormBuilder($todo)
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('category', TextType::class, ['attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('priority', ChoiceType::class, ['choices' => ['Low' => 'Low', 'Normal' => 'Normal', 'High' => 'High'], 'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:15px']])
            ->add('due_date', DateTimeType::class, ['attr' => ['class' => 'formcontrol', 'style' => 'margin-bottom:15px']])
            ->add('save', SubmitType::class, ['label' => 'Create Todo', 'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-bottom:15px']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $dueDate = $form['due_date']->getData();

            $now = new \DateTime('now');

            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($dueDate);
            $todo->setCreateDate($now);

            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();

            $this->addFlash(
                'notice',
                'Todo Created'
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/todo/edit/{id}", name="todo_edit")
     * @param $id
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {
        $todo = $this->getDoctrine()->getRepository('AppBundle:Todo')->find($id);

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
            $todo = $em->getRepository('AppBundle:Todo')->find($id);

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
     * @Route("/todo/delete/{id}", name="todo_delete")
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository('AppBundle:Todo')->find($id);

        $em->remove($todo);
        $em->flush();

        $this->addFlash(
            'notice',
            'Todo Removed'
        );

        return $this->redirectToRoute('todo_index');
    }
}
