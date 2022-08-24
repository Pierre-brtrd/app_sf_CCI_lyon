<?php

namespace App\Controller\Frontend;

use App\Data\SearchData;
use App\Entity\Article;
use App\Entity\Comments;
use App\Form\CommentsType;
use App\Form\SearchType;
use App\Repository\ArticleRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/liste', name: 'article.index')]
    public function index(ArticleRepository $repo, Request $request): Response
    {
        $data = new SearchData();

        $form = $this->createForm(SearchType::class, $data);
        $form->handleRequest($request);

        $articles = $repo->findSearch($data);

        return $this->renderForm('Frontend/Article/index.html.twig', [
            'articles' => $articles,
            'form' => $form
        ]);
    }

    #[Route('/details/{slug}', name: 'article.show')]
    public function show(
        ?Article $article,
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        CommentsRepository $repoComment
    ): Response {
        if (!$article instanceof Article) {
            $this->addFlash('error', 'Article non trouvé');

            return $this->redirectToRoute('home');
        }

        $comments = $repoComment->findActiveByArticle($article->getId());

        // On instancie le commentaire vide
        $comment = new Comments();

        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($security->getUser())
                ->setArticle($article)
                ->setActive(true);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été posté avec succès');

            return $this->redirectToRoute('article.show', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ], 301);
        }

        return $this->renderForm('frontend/article/show.html.twig', [
            'article' => $article,
            'form' => $form,
            'comments' => $comments,
        ]);
    }
}
