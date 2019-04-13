<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\PhotoLike;
use App\Entity\User;
use App\Repository\PhotoLikeRepository;
use Doctrine\Common\Persistence\ObjectManager;
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
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('user/users.html.twig', [
            'users' => $users
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
     * @Route("/subscribe/{id}", name="subscribe")
     * @param User $user
     * @param ObjectManager $manager
     * @return RedirectResponse|Response
     */
    public function subscribe(User $user, ObjectManager $manager)
    {
        $follower = $this->getUser();

        if (!$follower)
            return $this->redirectToRoute('fos_user_security_login');

        if ($user->isFollowedByUser($follower)) {
            $user->removeFollower($follower);
            $manager->persist($user);
            $manager->flush();

            return $this->json(['code' => 200,], 200);
        }

        $user->addFollower($follower);

        $manager->persist($user);
        $manager->flush();

        return $this->json(['code' => 200,], 200);
    }

    /**
     * @Route("/photo/{id}/like", name="like")
     * @param Photo $photo
     * @param ObjectManager $manager
     * @param PhotoLikeRepository $likeRepo
     * @return Response
     */
    public function like(Photo $photo, ObjectManager $manager, PhotoLikeRepository $likeRepo)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user)
            return $this->redirectToRoute('fos_user_security_login');

        if ($photo->isLikedByUser($user)) {
            $like = $likeRepo->findOneBy([
                'user'  => $user,
                'photo' => $photo
            ]);

            $manager->remove($like);
            $manager->flush();

            return $this->json([
               'code'    => 200,
               'message' => 'like успешно удален',
                'likes'  => $likeRepo->count(['photo' => $photo])
            ], 200);
        }

        $like = new PhotoLike();
        $like->setPhoto($photo)
             ->setUser($user);

        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code'    => 200,
            'message' => 'like успешно добавлен',
            'likes'   => $likeRepo->count(['photo' => $photo])
        ], 200);
    }
}
