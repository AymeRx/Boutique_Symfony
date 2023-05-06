<?php

namespace App\Controller;

use App\Entity\Usager;
use App\Entity\Commande;
use App\Service\PanierService;
use App\Service\BoutiqueService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PanierController extends AbstractController
{
    #[Route('/{_locale}/panier', name: 'app_panier_index', requirements: ['_locale' => 'fr|en'])]
    public function index(PanierService $panierService): Response
    {
        $contenuPanier = $panierService->getContenu();
        $total = $panierService->getTotal();
        $nombreProduits = $panierService->getNombreProduits();

        return $this->render('panier/index.html.twig', [
            'contenuPanier' => $contenuPanier,
            'total' => $total,
            'nombreProduits' => $nombreProduits,
        ]);
    }

    #[Route('/{_locale}/panier/ajouter/{idProduit}/{quantite}', name: 'app_panier_ajouter', requirements: ['idProduit' => '\d+', 'quantite' => '\d+'])]
    public function ajouter(PanierService $panierService, ManagerRegistry $doctrine, $idProduit, $quantite): Response
    {
        $produit = $doctrine->getManager()->getRepository('App\Entity\Produit')->find($idProduit);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        $panierService->ajouterProduit($idProduit, $quantite);
        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/{_locale}/panier/enlever/{idProduit}/{quantite}', name: 'app_panier_enlever', requirements: ['idProduit' => '\d+'])]
    public function enlever(PanierService $panierService, ManagerRegistry $doctrine, $idProduit, $quantite): Response
    {
        $produit = $doctrine->getManager()->getRepository('App\Entity\Produit')->find($idProduit);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        $panierService->enleverProduit($idProduit, $quantite);
        $this->addFlash('success', 'Produit enlevé du panier');
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/{_locale}/panier/supprimer/{idProduit}', name: 'app_panier_supprimer', requirements: ['idProduit' => '\d+'])]
    public function supprimer(PanierService $panierService, ManagerRegistry $doctrine, $idProduit): Response
    {
        $produit = $doctrine->getManager()->getRepository('App\Entity\Produit')->find($idProduit);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        $panierService->supprimerProduit($idProduit);
        $this->addFlash('success', 'Produit supprimé du panier');
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/{_locale}/panier/vider', name: 'app_panier_vider')]
    public function vider(PanierService $panierService): Response
    {
        $panierService->vider();
        $this->addFlash('success', 'Panier vidé');
        return $this->redirectToRoute('app_panier_index');
    }

    public function nombreProduits(PanierService $panierService, $route, $locale, $params): Response
    {
        $nombreProduits = $panierService->getNombreProduits();
        return $this->render('navbar.html.twig', [
            'nombreProduits' => $nombreProduits,
            'locale' => $locale,
            'route' => $route,
            'params' => $params,
        ]);
    }


    // #[Route('/{_locale}/panier/commander', name: 'app_panier_commander')]
    // public function commander(PanierService $panier, ManagerRegistry $doctrine): Response
    // {
    //     //$user = $this->getUser();
    //     //Elle utilisera (temporairement) l’usager d’identifiant égal à 1 comme propriétaire de la commande 
    //     $user = $this->getDoctrine()->getRepository(Usager::class)->find(1);
    //     $commande = $panier->panierToCommande($user);        
    //     return $this->render('panier/commande.html.twig', [
    //         'usager' => $user
    //     ]);
    // }


    #[Route('/{_locale}/panier/commander', name: 'app_panier_commander')]
    public function commander(PanierService $panier, ManagerRegistry $doctrine): Response
    {
        //$user = $this->getUser();
        //Elle utilisera (temporairement) l’usager d’identifiant égal à 1 comme propriétaire de la commande 
        $user = $this->getDoctrine()->getRepository(Usager::class)->find(1);
        $commande = $panier->panierToCommande($user);        
        return $this->redirectToRoute('app_panier_commandes');
    }

    #[Route('/{_locale}/panier/commande', name: 'app_panier_commandes', requirements:['_locale'=> '%app.supported_locales%'])]
    public function afficheCommande(PanierService $panier, ManagerRegistry $doctrine): Response
    {   
        // $user = $this->getUser();
        //Elle utilisera (temporairement) l’usager d’identifiant égal à 1 comme propriétaire de la commande
        $user = $this->getDoctrine()->getRepository(Usager::class)->find(1);
        return $this->render('panier/commande.html.twig', [
            'usager' => $user
        ]); 
    }
}
