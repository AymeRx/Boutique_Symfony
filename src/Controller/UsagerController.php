<?php

namespace App\Controller;

use App\Entity\Usager;
use App\Form\UsagerType;
use App\Repository\UsagerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

#[Route(
    path: '/{_locale}/usager',
    requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ['_locale' => 'fr']
)]
class UsagerController extends AbstractController
{
    #[Route('/compte', name: 'app_usager_index', methods: ['GET'])]
    public function index(UsagerRepository $usagerRepository): Response
    {
        
        $user = $this->getUser();

        return $this->render('usager/index.html.twig', [
            'usagers' => $user
        ]);
    }

    #[Route('/new', name: 'app_usager_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UsagerRepository $usagerRepository, UserPasswordHasherInterface $passwordHasher ): Response
    {
        $usager = new Usager();
        $form = $this->createForm(UsagerType::class, $usager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe qui est en clair pour l’instant
            $hashedPassword = $passwordHasher->hashPassword($usager, $usager->getPassword());
            $usager->setPassword($hashedPassword);
            // Définir le rôle de l’usager qui va être créé
            $usager->setRoles(["ROLE_CLIENT"]);
            // Sauvegarder l’usager
            $usagerRepository->save($usager, true);

            return $this->redirectToRoute('app_usager_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('usager/new.html.twig', [
            'usager' => $usager,
            'form' => $form,
        ]);
    }
}
