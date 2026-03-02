<?php

namespace App\Controller;

use App\Entity\Merch;
use App\Repository\MerchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/merch')]
class MerchAdminController extends AbstractController
{
    #[Route('/', name: 'admin_merch_index')]
    public function index(Request $request, MerchRepository $merchRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search', '');
        $query = $merchRepository->createQueryBuilder('m');

        if ($search) {
            $query->where('m.name LIKE :search OR m.type LIKE :search')
                  ->setParameter('search', '%'.$search.'%');
        }

        $merchItems = $query->getQuery()->getResult();

        return $this->render('admin/Merch/index.html.twig', [
            'merchItems' => $merchItems,
            'search' => $search
        ]);
    }

    #[Route('/new', name: 'admin_merch_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $merch = new Merch();
            $merch->setName($request->request->get('name'));
            $merch->setPrice((int)$request->request->get('price'));
            $merch->setStock((int)$request->request->get('stock'));
            $merch->setType($request->request->get('type'));
            $merch->setDescription($request->request->get('description'));
            $merch->setImageUrl($request->request->get('imageUrl'));

            $em->persist($merch);
            $em->flush();

            $this->addFlash('success', 'Merch ajout├® avec succ├¿s.');
            return $this->redirectToRoute('admin_merch_index');
        }

        return $this->render('admin/Merch/new.html.twig');
    }

    #[Route('/stats', name: 'admin_merch_stats', methods: ['GET'])]
    public function stats(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/Merch/merch_stats.html.twig');
    }

    #[Route('/delete/{id}', name: 'admin_merch_delete', methods: ['POST'])]
    public function delete(Merch $merch, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($merch);
        $em->flush();

        return $this->redirectToRoute('admin_merch_index');
    }

    #[Route('/edit/{id}', name: 'admin_merch_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Merch $merch, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $merch->setName($request->request->get('name'));
            $merch->setPrice((int)$request->request->get('price'));
            $merch->setStock((int)$request->request->get('stock'));
            $merch->setType($request->request->get('type'));
            $merch->setDescription($request->request->get('description'));
            $merch->setImageUrl($request->request->get('imageUrl'));

            $em->flush();
            $this->addFlash('success', 'Merch modifi├® avec succ├¿s.');
            return $this->redirectToRoute('admin_merch_index');
        }

        return $this->render('admin/Merch/edit.html.twig', [
            'merch' => $merch
        ]);
    }
}
