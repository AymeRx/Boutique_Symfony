<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Usager;

// Service pour manipuler le panier et le stocker en session
class PanierService
{
    ////////////////////////////////////////////////////////////////////////////
    private $session;   // Le service session
    private $boutique;  // Le service boutique
    private $panier;    // Tableau associatif, la clé est un idProduit, la valeur associée est une quantité
    //   donc $this->panier[$idProduit] = quantité du produit dont l'id = $idProduit
    const PANIER_SESSION = 'panier'; // Le nom de la variable de session pour faire persister $this->panier

    // Constructeur du service
    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        // Récupération des services session et BoutiqueService
        $this->boutique = $doctrine;
        $this->session = $requestStack->getSession();
        // Récupération du panier en session s'il existe, init. à vide sinon
        $this->panier = $this->session->get(self::PANIER_SESSION, []);
    }

    // Renvoie le montant total du panier
    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->boutique->getManager()->getRepository('App\Entity\Produit')->find($idProduit);
            $total += $produit->getPrix() * $quantite;
        }
        return $total;
    }

    // Renvoie le nombre de produits dans le panier
    public function getNombreProduits(): int
    {
        $nombre = 0;
        foreach ($this->panier as $idProduit => $quantite) {
            $nombre += $quantite;
        }
        return $nombre;
    }

    // Ajouter au panier le produit $idProduit en quantite $quantite 
    public function ajouterProduit(int $idProduit, int $quantite = 1): void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] += $quantite;
        } else {
            $this->panier[$idProduit] = $quantite;
        }
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Enlever du panier le produit $idProduit en quantite $quantite 
    public function enleverProduit(int $idProduit, int $quantite = 1): void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] -= $quantite;
            if ($this->panier[$idProduit] <= 0) {
                unset($this->panier[$idProduit]);
            }
        }
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Supprimer le produit $idProduit du panier
    public function supprimerProduit(int $idProduit): void
    {
        if (isset($this->panier[$idProduit])) {
            unset($this->panier[$idProduit]);
        }
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Vider complètement le panier
    public function vider(): void
    {
        $this->panier = [];
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Renvoie le contenu du panier dans le but de l'afficher
    //   => un tableau d'éléments [ "produit" => un objet produit, "quantite" => sa quantite ]
    public function getContenu(): array
    {
        $contenu = [];
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->boutique->getManager()->getRepository('App\Entity\Produit')->find($idProduit);
            $contenu[] = ["produit" => $produit, "quantite" => $quantite];
        }
        return $contenu;
    }

    public function panierToCommande(Usager $usager): ?Commande
    {
        $produits = $this->getContenu();

        if (count($produits) == 0) {
            return null;
        }

        // Creation de la commande
        $commande = new Commande();
        $commande->setUsager($usager);
        $commande->setDateCreation(new \DateTime('now'));
        $commande->setValidation(false);
        $this->boutique->getManager()->persist($commande);
        foreach ($produits as $produit) {
            $ligneCommande = new LigneCommande();
            $ligneCommande->setProduit($produit['produit']);
            $ligneCommande->setQuantite($produit['quantite']);
            $ligneCommande->setPrix($produit['quantite'] * $produit['produit']->getPrix());
            $ligneCommande->setCommande($commande);
            $this->boutique->getManager()->persist($ligneCommande);
        }
        $this->boutique->getManager()->flush();
        $this->vider();

        return $commande;
    }
}
