<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Tag;
use App\Entity\Tutorial;
use App\Form\CommentType;
use App\Form\TutorialType;
use App\Repository\TutorialRepository;
use App\Security\Voter\TutorialVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class TutorialController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TutorialRepository $tutorialRepo): Response
    {
        // آخر 6 توريتالز
        $tutorials = $tutorialRepo->findBy([], ['createdAt' => 'DESC'], 6);
        return $this->render('tutorial/index.html.twig', ['tutorials' => $tutorials]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function byCategory(Category $category): Response
    {
        return $this->render('tutorial/list.html.twig', [
            'title' => 'Catégorie : ' . $category->getName(),
            'tutorials' => $category->getTutorials(),
        ]);
    }

    #[Route('/tag/{id}', name: 'app_tag_show')]
    public function byTag(Tag $tag): Response
    {
        return $this->render('tutorial/list.html.twig', [
            'title' => 'Tag : #' . $tag->getName(),
            'tutorials' => $tag->getTutorials(),
        ]);
    }

    #[Route('/tutorial/new', name: 'app_tutorial_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        MailerInterface $mailer
    ): Response {
        $tutorial = new Tutorial();
        $form = $this->createForm(TutorialType::class, $tutorial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tutorial->setAuthor($this->getUser());
            $tutorial->setSlug(strtolower($slugger->slug($tutorial->getTitle())) . '-' . uniqid());
            $em->persist($tutorial);
            $em->flush();

            // 2. إيميل إشعار للأدمن
            $email = (new TemplatedEmail())
                ->from('no-reply@devdocs.tn')
                ->to('admin@devdocs.tn')
                ->subject('Nouveau tutoriel publié : ' . $tutorial->getTitle())
                ->htmlTemplate('emails/new_tutorial.html.twig')
                ->context(['tutorial' => $tutorial]);
            $mailer->send($email);

            $this->addFlash('success', 'Tutoriel publié avec succès !');
            return $this->redirectToRoute('app_tutorial_show', ['slug' => $tutorial->getSlug()]);
        }

        return $this->render('tutorial/form.html.twig', ['form' => $form, 'isEdit' => false]);
    }

    #[Route('/tutorial/{slug}', name: 'app_tutorial_show')]
    public function show(Tutorial $tutorial, Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $comment->setAuthor($this->getUser());
            $comment->setTutorial($tutorial);
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Commentaire ajouté !');
            return $this->redirectToRoute('app_tutorial_show', ['slug' => $tutorial->getSlug()]);
        }

        return $this->render('tutorial/show.html.twig', [
            'tutorial' => $tutorial,
            'commentForm' => $form,
        ]);
    }

    #[Route('/tutorial/{slug}/edit', name: 'app_tutorial_edit')]
    #[IsGranted(TutorialVoter::EDIT, subject: 'tutorial')]
    public function edit(Tutorial $tutorial, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TutorialType::class, $tutorial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Tutoriel modifié avec succès !');
            return $this->redirectToRoute('app_tutorial_show', ['slug' => $tutorial->getSlug()]);
        }

        return $this->render('tutorial/form.html.twig', ['form' => $form, 'isEdit' => true]);
    }

    #[Route('/tutorial/{slug}/delete', name: 'app_tutorial_delete', methods: ['POST'])]
    #[IsGranted(TutorialVoter::DELETE, subject: 'tutorial')]
    public function delete(Tutorial $tutorial, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tutorial->getId(), $request->request->get('_token'))) {
            $em->remove($tutorial);
            $em->flush();
            $this->addFlash('success', 'Tutoriel supprimé.');
        }
        return $this->redirectToRoute('app_home');
    }
}
