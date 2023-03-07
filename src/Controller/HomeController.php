<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use App\Repository\ContractIncomeRepository;
use App\Repository\ContractRepository;
use App\Repository\TransactionLineRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        AccountRepository $accountRepository,
        ContractRepository $contractRepository,
        ContractIncomeRepository $contractIncomeRepository,
        TransactionRepository $transactionRepository,
        TransactionLineRepository $transactionLineRepository,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('home/index.html.twig', [
            'accounts' => $accountRepository->findAll(),
            'contracts' => $contractRepository->findAll(),
            'incomes' => $contractIncomeRepository->findAll(),
            'transactions' => $transactionRepository->findAll(),
            'transactionLines' => $transactionLineRepository->findAll(),
        ]);
    }
}
