<?php

use App\Kernel;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    $kernel->boot();

    $container = $kernel->getContainer();
    $em = $container->get('doctrine')->getManager();
    $userRepo = $em->getRepository(User::class);

    $user = $userRepo->findOneBy(['email' => 'admin@example.com']);

    if ($user) {
        $user->setRoles(['ROLE_ADMIN']);
        $em->persist($user);
        $em->flush();
        echo "User 'admin@example.com' promoted to ADMIN successfully.\n";
    } else {
        echo "User 'admin@example.com' not found.\n";
    }
};
