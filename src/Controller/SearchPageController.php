<?php
namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchPageController extends AbstractController
{
    #[Route('/search', name: 'app_search_page')]
    public function index(Request $request, CardRepository $cr): Response
    {

        $query = trim($request->query->get('q', ''));

        if ($query === '') {
            return $this->redirectToRoute('app_home');
        }

        $cards = $cr->createQueryBuilder('c')
                    ->where('LOWER(c.name) LIKE :q')
                    ->setParameter('q', '%' . mb_strtolower($query) . '%')
                    ->getQuery()
                    ->getResult();

        return $this->render('search_page/index.html.twig', [
            'cards' => $cards,
        ]);
    }
}
