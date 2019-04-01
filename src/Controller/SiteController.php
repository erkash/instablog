<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\CommentType;
use App\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="site")
     */
    public function index()
    {
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }

    /**
     * @Route("/feed")
     */
    public function feedAction()
    {
        $user = $this->getUser();

        if (!$user)
            return $this->redirectToRoute('fos_user_security_login');

        $photos      = $this->getDoctrine()->getRepository(Photo::class)->findAll();
        $photoForm   = $this->createForm(PhotoType::class);
        $commentForm = $this->createForm(CommentType::class);

        return $this->render('site/feed.html.twig', [
            'photoForm'   => $photoForm->createView(),
            'photos'      => $photos,
            'user'        => $user,
            'commentForm' => $commentForm,
        ]);
    }

    /**
     * @Route("/add", name="add-photo", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function addPhotoAction(Request $request)
    {
        if (!$this->getUser())
            return $this->redirectToRoute('fos_user_security_login');

        $photo = new Photo();
        $form  = $this->createForm(PhotoType::class, $photo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $em->flush();
        }

        return $this->redirectToRoute('app_site_feed');
    }
}
