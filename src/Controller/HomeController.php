<?php
namespace App\Controller;

use App\Repository\BlocRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(BlocRepository $br, SetRepository $sr): Response
    {
        $blocList = $br->findAll();

        $setList = $sr->findAll();

        $blocsWithSets = [];
        foreach ($blocList as $bloc) {
            $blocsWithSets[$bloc->getId()] = [
                'bloc' => $bloc,
                'sets' => [],
            ];
        }

        foreach ($setList as $set) {
            $blocId = $set->getBloc()->getId();
            $blocsWithSets[$blocId]['sets'][] = $set;

        }

        return $this->render('home/index.html.twig', [
            'blocsWithSets' => $blocsWithSets,
            'page' => "Accueil"
        ]);
    }
}
