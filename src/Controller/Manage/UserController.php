<?php

namespace App\Controller\Manage;

use App\DataTable\UserTableType;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/team', name: 'app_users_list')]
    public function pages(DataTableFactory $dataTableFactory,Request $request): Response
    {
        $table = $dataTableFactory->createFromType(UserTableType::class);

        $table->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('user/list.html.twig', [
            'datatable' => $table,
        ]);
    }

    #[Route('/team/user/add', name: 'app_user_add')]
    #[Route('/team/user/edit/{user}', name: 'app_user_edit')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        #[MapEntity(mapping: ['user' => 'uuid'])]
        User $user = null
    ): Response
    {
        $editMode = null !== $user;
        if (null === $user) {
            $user = new User();
        }

        $form = $this->createForm(UserType::class, $user,[
            'editMode' => $editMode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$editMode) {
                $user->setCreatedAt(new \DateTimeImmutable());
                $password = $form->get('password')->getData();
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_users_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'editMode' => $editMode,
        ]);
    }
}
