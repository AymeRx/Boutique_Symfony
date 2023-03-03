<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BoutiqueService;

class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique_index')]
    public function index(BoutiqueService $boutiqueService): Response
    {
        $categories = $boutiqueService->findAllCategories();
        return $this->render('boutique/index.html.twig', [
            'categories' => $categories,
        ]);
    }


    #[Route('/rayon/{idCategorie}', name: 'app_boutique_rayon')]
    public function rayon(BoutiqueService $boutiqueService, int $idCategorie): Response
    {
        $categorie = $boutiqueService->findCategorieById($idCategorie);
        $produits = $boutiqueService->findProduitsByCategorie($idCategorie);
        return $this->render('boutique/rayon.html.twig', [
            'categorie' => $categorie,
            'produits' => $produits,
        ]);
    }
}
