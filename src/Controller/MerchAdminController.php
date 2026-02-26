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

        return $this->render('admin/merch/index.html.twig', [
            'merchItems' => $merchItems,
            'search' => $search
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_merch_delete', methods: ['POST'])]
    public function delete(Merch $merch, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($merch);
        $em->flush();

        return $this->redirectToRoute('app_shop', ['type' => 'merch']);
    }

    #[Route('/edit/{id}', name: 'admin_merch_edit', methods: ['GET','POST'])]
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
            // redirect to public shop filtered by merch
            return $this->redirectToRoute('app_shop', ['type' => 'merch']);
        }

        return $this->render('admin/merch/edit.html.twig', [
            'merch' => $merch
        ]);
    }
}