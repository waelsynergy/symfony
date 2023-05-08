<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;

#[Route('/project', name: 'app_project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'project_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
    // #[Route('/page/{page<[1-9]\d{0,8}>}', name: 'project_index_paginated', methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function index( ProjectRepository $projects,int $page=1): Response
    {
        $latestProjects = $projects->findLatest($page);
        return $this->render('project/index.html.twig', [
            'projects' => $latestProjects,
        ]);
    }
    #[Route('/', name: 'project_search', defaults: ['page' => '1', '_format' => 'html'], methods: ['POST'])]
    public function search(Request $request, ProjectRepository $projects,int $page=1): Response
    {
        $query = $request->request->get('q', '');
        $status = $request->request->get('status', null);
        $name = $request->request->get('name', null);
        // dd($query,$status,$name);
        $latestProjects = $projects->findBySearchQuery($query,$status,$name);
        return $this->render('project/index.html.twig', [
            'projects' => $latestProjects,
        ]);
    }

}
