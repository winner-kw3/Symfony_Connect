<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Form\PostType;


final class PostController extends AbstractController
{
    #[Route('/post/nouveau', name: 'post_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post ->setAuthor($this->getUser());
            $post->setCreatedAt(new \DateTimeImmutable());
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Post créé avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/post/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        if (!$this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('app_profile', [
            'username' => $post->getAuthor()->getUsername()
        ]);
    }


}
