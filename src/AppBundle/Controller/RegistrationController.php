<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\User\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    /**
     * @param Request $request
     * @Route("/register", name="register")
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $encoder = $this->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword(
                $user,
                $form->get('password')->getData()
            ));
            $user->setIsActive(true);
            $user->setRole('ROLE_USER');
            $em->persist($user);
            $em->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject('Confirm Registration')
                ->setFrom('catalog@gmail.com')
                ->setTo($form->get('email')->getData())
                ->setBody('Your account was successfully registered! Please, proceed this link for its activation:');
            $this->get('mailer')->send($message);

            return $this->redirectToRoute('login');
            // todo add a flash message or new page with text: "Thank you for registration! Please check your email to activate your account."
        }
        
        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
