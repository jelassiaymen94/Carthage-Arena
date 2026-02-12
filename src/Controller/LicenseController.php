<?php

namespace App\Controller;

use App\Entity\License;
use App\Repository\LicenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/licenses')]
#[IsGranted('ROLE_ADMIN')]
class LicenseController extends AbstractController
{
    #[Route('', name: 'app_admin_licenses')]
    public function index(LicenseRepository $licenseRepository): Response
    {
        $licenses = $licenseRepository->findAllOrderedByDate();
        $stats = [
            'total' => $licenseRepository->countTotal(),
            'used' => $licenseRepository->countUsed(),
            'available' => $licenseRepository->countAvailable(),
        ];

        return $this->render('admin/licenses.html.twig', [
            'licenses' => $licenses,
            'stats' => $stats,
        ]);
    }

    #[Route('/generate', name: 'app_admin_licenses_generate', methods: ['POST'])]
    public function generate(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $count = (int) $request->request->get('count', 1);
        
        if ($count < 1 || $count > 100) {
            $this->addFlash('error', 'Le nombre de licences doit être entre 1 et 100.');
            return $this->redirectToRoute('app_admin_licenses');
        }

        $generated = [];
        for ($i = 0; $i < $count; $i++) {
            $license = new License();
            $license->setLicenseCode($this->generateLicenseCode());
            
            $entityManager->persist($license);
            $generated[] = $license->getLicenseCode();
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf(
            '%d licence(s) générée(s) avec succès: %s',
            $count,
            implode(', ', $generated)
        ));

        return $this->redirectToRoute('app_admin_licenses');
    }

    /**
     * Generate a unique license code in format: ARB-YYYY-XXXX
     */
    private function generateLicenseCode(): string
    {
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(2))); // 4 random hex chars
        
        return sprintf('ARB-%s-%s', $year, $random);
    }
}
