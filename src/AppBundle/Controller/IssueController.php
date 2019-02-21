<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Issue;
use AppBundle\Form\Issue\IssueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/issue")
 */
class IssueController extends Controller
{
    /**
     * @Route("/list", name="issue_list")
     */
    public function listAction()
    {
        $issues = $this->getDoctrine()->getRepository(Issue::class)->findAll();
        
        return $this->render('issue/index.html.twig', [
            'issues' => $issues,
        ]);
    }

    /**
     * @Route("/add", name="add_issue")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $issue = new Issue();

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

            return $this->redirectToRoute('issue_list');
        }

        return $this->render('issue/edit.html.twig', [
            'form' => $form->createView(),
            'formType' => 'create',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_issue")
     * @param Request $request
     * @param $issue
     * @ParamConverter("issue", class="AppBundle:Issue")
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Issue $issue)
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

            return $this->redirectToRoute('issue_list');
        }

        return $this->render('issue/edit.html.twig', [
            'issue' => $issue,
            'form' => $form->createView(),
            'formType' => 'edit',
        ]);
    }

    /**
     * @Route("/details/{id}", name="issue_details")
     * @param $id
     * @return Response
     */
    public function detailsAction($id)
    {
        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($id);

        return $this->render('issue/details.html.twig', [
            'issue' => $issue,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete_issue")
     * @param $issue
     * @ParamConverter("issue", class="AppBundle:Issue")
     * @return RedirectResponse
     */
    public function deleteAction(Issue $issue)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($issue);
        $em->flush();

        $this->addFlash(
            'notice',
            'Issue Removed'
        );

        return $this->redirectToRoute('issue_list');
    }

    // todo Create a Sortable & Responsive Grid With Muuri
    // https://www.youtube.com/watch?v=PDG-GqmUZss
}
