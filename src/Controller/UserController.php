<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserUploadType;
use App\Helper\UploadFile;
use App\Repository\StatsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app_user_')]
#[IsGranted("ROLE_USER")]
final class UserController extends AbstractController
{

    private readonly UserRepository $userRepository;
    private readonly EntityManagerInterface $em;
    private LoggerInterface $logger;


    public function __construct(UserRepository $userRepository, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->logger = $logger;
    }

    #[Route('/detail/{userId}', name: 'detail', requirements: ['userId' => '\d+'])]
    public function detail(int $userId): Response
    {
        $currentUser = $this->getUser();

        $user = $this->userRepository->findUserById($userId);

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page');
        }

        if ($user->getId() !== $userId) {
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission de consulter ce profil');
        }

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }


        return $this->render('user/detail.html.twig', [
            'title' => 'Mon profil',
            'user' => $user,
        ]);
    }
    #[Route('/update/', name: 'update')]
    public function update(Request $request, UploadFile $uploadFile, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil');
        }

        $userForm = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $plainPassword = $userForm->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            // Upload profile picture
            try {
                if ($userForm->get('profilePicture')->getData()) {
                    $file = $userForm->get('profilePicture')->getData();
                    $name = $uploadFile->upload($file, $user->getPseudo(), $this->getParameter('kernel.project_dir') . '/public/uploads');
                    $user->setPhoto($name);
                }
            } catch (FileException $e) {
                $userForm->addError(new FormError($e->getMessage()));
            }

            $this->em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès');

            return $this->redirectToRoute('app_user_detail', ['userId' => $user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'title' => 'Modifier le profil',
            'userForm' => $userForm,
        ]);
    }

    #[Route('/import/', name: 'import')]
    public function import(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_event');
            }
        }

        $form = $this->createForm(UserUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csvFile')->getData();

            if ($file) {
                $handle = fopen($file->getPathname(), 'r');
                $firstLine = fgets($handle);

                if (str_starts_with($firstLine, "\u{FEFF}")) {
                    $firstLine = substr($firstLine, 3);
                }

                while (($data = fgetcsv($handle, 1000, ';')) !== false) {

                    if (count($data) < 8) {
                        continue;
                    }

                    [$pseudo, $email, $password, $name, $firstname, $phone, $site, $roles] = $data;

                    try {
                        $user = new User();
                        $user->setPseudo($pseudo);
                        $user->setEmail($email);

                        if (strlen($password) < 5) {
                            $this->addFlash('error', "Mot de passe trop court pour $pseudo");
                            continue;
                        }

                        $user->setPassword($userPasswordHasher->hashPassword($user, $password));
                        $user->setName($name);
                        $user->setFirstname($firstname);
                        $user->setPhone($phone);

                        $siteEntity = $this->em->getRepository(Site::class)->findOneBy(['name' => $site]);
                        if (!$siteEntity) {
                            $this->addFlash('error', "Le site'$site' n'existe pas en base");
                            continue;
                        }

                        $user->setSite($siteEntity);
                        $user->setRoles(explode('|', $roles));
                        $user->setIsActive(true);
                        $user->setIsVerified(true);

                        $this->em->persist($user);
                    } catch (\Exception $e) {
                        $this->addFlash('error', "Erreur lors de la création de l'utilisateur $pseudo : " . $e->getMessage());
                        continue;
                    }
                }
                fclose($handle);
                $this->em->flush();
                $this->addFlash('success', 'Utilisateurs importés avec succès');
                return $this->redirectToRoute('app_user_list');
            }
        }

        return $this->render('user/import.html.twig', [
            'form' => $form,
            'title' => 'Importer des utilisateurs',
        ]);
    }

    #[Route('/list', name: 'list')]
    public function list(): Response
    {
        if ($this->getUser()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_event');
            }
        }
        $users = $this->userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'title' => 'Liste des utilisateurs',
        ]);
    }

    #[Route('/desactivate/{userId}', name: 'desactivate', requirements: ['userId' => '\d+'])]
    public function desactivate(int $userId): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit");
        }

        $user = $this->userRepository->findUserById($userId);

        $user->setIsActive(false);

        $this->em->flush();
        $this->addFlash('success', "Le compte de l'utilisateur {$user->getFirstname()} a bien été désactivé");
        return $this->redirectToRoute("app_user_list");
    }

    #[Route('/activate/{userId}', name: 'activate', requirements: ['userId' => '\d+'])]
    public function activate(int $userId): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit");
        }

        $user = $this->userRepository->findUserById($userId);

        $user->setIsActive(true);

        $this->em->flush();
        $this->addFlash('success', "Le compte de l'utilisateur {$user->getFirstname()} a bien été réactivé");
        return $this->redirectToRoute("app_user_list");
    }

    #[Route('/delete/{userId}', name: 'delete', requirements: ['userId' => '\d+'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(int $userId): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit");
        }
        try {
            $user = $this->userRepository->findUserById($userId);

            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', "Le compte de l'utilisateur {$user->getFirstname()} a bien été supprimé");
            return $this->redirectToRoute("app_user_list");
        } catch (\RuntimeException $e) {
            $this->logger->error('Erreur SQL : ' . $e->getMessage());
            $this->addFlash('erreur', "Le compte de l'utilisateur n'a pas été supprimé");
            return $this->redirectToRoute("app_user_list");
        }
    }
}
