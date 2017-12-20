<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ResetPassword;
use AppBundle\Entity\User;
use AppBundle\Form\User\RecoveryPasswordType;
use AppBundle\Form\User\ResetPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
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
}