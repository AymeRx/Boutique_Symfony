<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BoutiqueService;
use Doctrine\Persistence\ManagerRegistry;
use PharIo\Manifest\Requirement;

class BoutiqueController extends AbstractController
{
    #[Route(
        path:'/{_locale}/boutique', 
        name: 'app_boutique_index',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
        )]
    public function index(ManagerRegistry $doctrine ): Response
    {
        $categories = $doctrine->getManager()->getRepository('App\Entity\Categorie')->findAll();
        return $this->render('boutique/index.html.twig', [
            'categories' => $categories,
        ]);
    }


    #[Route(
        path: '/{_locale}/rayon/{idCategorie}', 
        name: 'app_boutique_rayon',
        requirements: ['locale' => '%app.supported_locales%']
        )]
    public function rayon(ManagerRegistry $doctrine, int $idCategorie): Response
    {
        $categorie = $doctrine->getManager()->getRepository('App\Entity\Categorie')->find($idCategorie);
        $produits = $doctrine->getManager()->getRepository('App\Entity\Produit')->findBy(['categorie' => $idCategorie]);
        return $this->render('boutique/rayon.html.twig', [
            'categorie' => $categorie,
            'produits' => $produits,
        ]);
    }

    #[Route(
        path: '/{_locale}/boutique/recherche/{recherche}', 
        name: 'app_boutique_recherche',
        requirements: ['locale' => '%app.supported_locales%', 'recherche' => ".+"],
        defaults: ['recherche' => '']
        )]
    // public function recherche(BoutiqueService $boutiqueService, string $recherche): Response
    // {
    //     $produits = $boutiqueService->findProduitsByLibelleOrTexte($recherche);
    //     return $this->render('boutique/recherche.html.twig', [
    //         'produits' => $produits,
    //         'recherche' => $recherche,
    //     ]);
    // }
        public function recherche(ManagerRegistry $doctrine, string $recherche): Response
    {
        $produits = $doctrine->getManager()->getRepository('App\Entity\Produit')->findByLibelleOrTexte($recherche);
        return $this->render('boutique/recherche.html.twig', [
            'produits' => $produits,
            'recherche' => $recherche,
        ]);
    }
}
