<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.01.17
 * Time: 20:35
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $form->add('save', SubmitType::class, [
            'label' => 'Submit',
            'attr' => [
                'class' => 'btn btn-primary',
                'style' => 'margin-bottom:15px'
            ]
        ]);

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
            return $this->redirectToRoute('login');
        }
        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}