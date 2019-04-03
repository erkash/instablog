<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Form\FollowType;
use App\Form\UnsubscribeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="users")
     */
    public function allUsers()
    {
        $currentUser = $this->getUser();
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        $followForm = [];

        /** @var User $user */
        foreach ($users as $user) {
            if (!$user->getFollowers()->contains($this->getUser())) {
                $followForm[$user->getId()] = $this->createForm(FollowType::class, null, [
                    'method' => 'POST',
                    'action' => $this->generateUrl('subscribe', [
                        'id' => $user->getId()
                    ])
                ])->createView();
            } else {
                $followForm[$user->getId()] = $this->createForm(UnsubscribeType::class, null, [
                    'method' => 'POST',
                    'action' => $this->generateUrl('unsubscribe', [
                        'id' => $user->getId()
                    ])
                ])->createView();
            }
        }

        return $this->render('user/users.html.twig', [
            'users'        => $users,
            'followForm'   => $followForm,
            'currentUser'  => $currentUser
        ]);
    }

    /**
     * @Route("/user/{id}", methods={"GET"}, name="profile")
     * @param User $profile
     * @return Response
     */
    public function profile(User $profile)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $followings = count($profile->getFollowings());
        $followers  = count($profile->getFollowers());

        $posts = $profile->getPhotos();

        return $this->render('user/profile.html.twig', [
            'profile'    => $profile,
            'followings' => $followings,
            'followers'  => $followers,
            'posts'      => $posts
        ]);
    }

    /**
     * @Route("/subscribe/{id}", methods={"POST"}, name="subscribe")
     * @param User $followed
     * @return RedirectResponse|Response
     */
    public function subscribe(User $followed)
    {
        $follower = $this->getUser();

        if (!$follower)
            return $this->redirectToRoute('fos_user_security_login');

        $followed->addFollower($follower);
        $follower->addFollower($followed);

        $em = $this->getDoctrine()->getManager();
        $em->persist($followed);
        $em->persist($follower);
        $em->flush();

        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/unsubscribe/{id}", methods={"POST"}, name="unsubscribe")
     * @param User $followed
     * @return RedirectResponse|Response
     */
    public function unSubscribe(User $followed)
    {
        $follower = $this->getUser();

        if (!$follower)
            return $this->redirectToRoute('fos_user_security_login');

        $followed->removeFollower($follower);
        $follower->removeFollowing($followed);

        $em = $this->getDoctrine()->getManager();
        $em->persist($followed);
        $em->persist($follower);
        $em->flush();

        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/like/{id}", methods={"POST"}, name="like")
     * @param Photo $photo
     * @return RedirectResponse|Response
     */
    public function like(Photo $photo)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user)
            return $this->redirectToRoute('fos_user_security_login');

        $photo->addLike($user);
        $user->addLike($photo);

        $em = $this->getDoctrine()->getManager();
        $em->persist($photo);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('feed');
    }

    /**
     * @Route("/dislike/{id}", methods={"POST"}, name="dislike")
     * @param Photo $photo
     * @return RedirectResponse|Response
     */
    public function dislike(Photo $photo)
    {
        $user = $this->getUser();

        if (!$user)
            return $this->redirectToRoute('fos_user_security_login');

        $photo->removeLike($user);
        $user->removeLike($photo);

        $em = $this->getDoctrine()->getManager();
        $em->persist($photo);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('feed');
    }
}
