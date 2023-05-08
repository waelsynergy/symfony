<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Service\FileUploadService;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


#[Route('/admin/project')]
#[IsGranted('ROLE_ADMIN')]

class ProjectController extends AbstractController
{
    #[Route('/', name: 'admin_index', methods: ['GET'])]
    #[Route('/', name: 'admin_project_index', methods: ['GET'])]
    public function index(
        ProjectRepository $projects,
    ): Response {
        $projects = $projects->findAll();

        return $this->render('admin/project/index.html.twig', ['projects' => $projects]);
    }

    /**
     * Creates a new Project entity.
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    #[Route('/new', name: 'admin_project_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploadService $fileUploadService,
    ): Response {
        $project = new Project();
        $user = $this->getUser();
        $project->setOwner($user);

        // See https://symfony.com/doc/current/form/multiple_buttons.html
        $form = $this->createForm(ProjectType::class, $project)
            ->add('saveAndCreateNew', SubmitType::class)
        ;

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/forms.html#processing-forms
        if ($form->isSubmitted() && $form->isValid()) {
            $fileUploadService->uploadProjectImage($project, $form->get('image')->getData());
            $entityManager->persist($project);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', 'project.created_successfully');

            /** @var SubmitButton $submit */
            $submit = $form->get('saveAndCreateNew');

            if ($submit->isClicked()) {
                return $this->redirectToRoute('admin_project_new');
            }

            return $this->redirectToRoute('admin_project_index');
        }

        return $this->render('admin/project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    /**
     * Finds and displays a Project entity.
     */
    #[Route('/{id<\d+>}', name: 'admin_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {

        return $this->render('admin/project/show.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * Displays a form to edit an existing Project entity.
     */
    #[Route('/{id<\d+>}/edit', name: 'admin_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager ,
           FileUploadService $fileUploadService,
    ): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('image')->getData())
                $fileUploadService->uploadProjectImage($project, $form->get('image')->getData());
            $entityManager->flush();
            $this->addFlash('success', 'project.updated_successfully');

            return $this->redirectToRoute('admin_project_edit', ['id' => $project->getId()]);
        }

        return $this->render('admin/project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    /**
     * Deletes a Project entity.
     */
    #[Route('/{id}/delete', name: 'admin_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        /** @var string|null $token */
        $token = $request->request->get('token');

        if (!$this->isCsrfTokenValid('delete', $token)) {
            return $this->redirectToRoute('admin_project_index');
        }

        $entityManager->remove($project);
        $entityManager->flush();

        $this->addFlash('success', 'project.deleted_successfully');

        return $this->redirectToRoute('admin_project_index');
    }

}
