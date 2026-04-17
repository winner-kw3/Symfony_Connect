<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;

final class PostController extends AbstractController
{
    #[Route('/post/nouveau', name: 'post_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CacheInterface $cache
    ): Response {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post->setAuthor($this->getUser());
            $post->setCreatedAt(new \DateTimeImmutable());

            $em->persist($post);
            $em->flush();

            $cache->delete('feed_' . $this->getUser()->getId());

            $this->addFlash('success', 'Post créé avec succès !');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/delete', name: 'post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Post $post,
        Request $request,
        EntityManagerInterface $em,
        CacheInterface $cache
    ): Response {

        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        if (!$this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        $author = $post->getAuthor();

        $em->remove($post);
        $em->flush();

        $cache->delete('feed_' . $author->getId());

        return $this->redirectToRoute('app_profile', [
            'username' => $author->getUsername()
        ]);
    }
}