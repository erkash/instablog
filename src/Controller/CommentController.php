<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Photo;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/photo-{id}/new-comment", methods={"POST"})
     * @param Request $request
     * @param Photo $photo
     * @return Response
     */
    public function newAction(Request $request, Photo $photo)
    {
        $comment     = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $comment->setUser($this->getUser());
            $comment->setPhoto($photo);

            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectToRoute('app_site_feed');
    }
}
