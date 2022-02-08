<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ArticleType;
use App\Entity\Article;

use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article", name="article")
     */
    public function index(Request $request): Response
    {
        $article= new Article();
        
        $form = $this->createFormBuilder($article)//à commenter pour tester formulaire

        //    $form=$this->createForm(ArticleType::class,$article);

        ->add('title', TextType::class)//à commenter pour tester formulaire
        
        ->add('content', TextType::class)//à commenter pour tester formulaire

        ->add('cover', FileType::class, [
            'mapped' => false
        ])
        ->add('date_create', DateType::class)
    
        ->add('save', SubmitType::class, ['label' => 'Valider'])//à commenter pour tester formulaire
       ->getForm();//à commenter pour tester formulaire
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             /** @var UploadedFile $uploadedFile */
             $uploadedFile = $form['cover']->getData();
             if ($uploadedFile) {
                 $destination = $this->getParameter('kernel.project_dir').'/public/uploads/article_image';
                 $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                 $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
                 $uploadedFile->move(
                     $destination,
                     $newFilename
                 );
                 $article->setCover($newFilename);
             }
           $article = $form->getData(); //à commenter pour tester formulaire
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            echo 'Enregistré dans la base de données';
        }

        return $this->render('client/client.html.twig', ['form' => $form->createView(),]);
        }
        /**
         * @Route("article/{id}", name="article_view")
         */
        public function viewAction($id) {
            $article = $this->getDoctrine()->getRepository(Article::class);
            $article = $article->find($id);
            if (!$article) {
                throw $this->createNotFoundException(
                    'Aucun article pour l\'id: ' . $id
                );
            }
            return $this->render(
                'client/viewOneArticle.html.twig',
                array('article' => $article)
            );
    

    }
    /**
     * @Route("/articles/all", name="articles_all")
     */
    public function showAction() {

        $articles = $this->getDoctrine()->getRepository(Article::class);
        $articles = $articles->findAll();

        return $this->render(
            'client/viewArticles.html.twig',
            array('articles' => $articles)
        );
    }
    /**
     * @Route("/delete-article/{id}" , name="article_delete")
     */
    public function deleteAction($id) {

        $em = $this->getDoctrine()->getManager();
        $article = $this->getDoctrine()->getRepository(Article::class);
        $article = $article->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'There are no articles with the following id: ' . $id
            );
        }

        $em->remove($article);
        $em->flush();

        return $this->redirect($this->generateUrl('articles_all'));

    }
    /**
     * @Route("/update-article/{id}", name="article_edit")
     */
    public function updateAction(Request $request, $id) {

        $article = $this->getDoctrine()->getRepository(Article::class);
        $article = $article->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'There are no articles with the following id: ' . $id
            );
        }

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('content', TextType::class)
           
            ->add('date_create', dateType::class)
            ->add('save', SubmitType::class, array('label' => 'Editer'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $article = $form->getData();
            $em->flush();

            return $this->redirect($this->generateUrl('articles_all'));

        }

        return $this->render(
            'client/edit.html.twig',
            array('article'=>$article,
            'form' => $form->createView())
        );

    }
}


