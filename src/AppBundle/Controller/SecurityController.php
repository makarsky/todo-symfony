<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ResetPassword;
use AppBundle\Entity\User;
use AppBundle\Form\User\RecoveryPasswordType;
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