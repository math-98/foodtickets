<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/transaction')]
class TransactionController extends AbstractController
{
    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepository): Response
    {
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactionRepository->findAll(),
            'types' => Transaction::TYPES,
        ]);
    }

    private function handleForm(
        Request $request,
        Transaction $transaction,
        TransactionRepository $transactionRepository,
        AccountRepository $accountRepository,
        array $viewParams,
    ) {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        $transactionLineErrors = [];
        if ($form->isSubmitted()) {
            foreach ($transaction->getTransactionLines() as $kLine => $line) {
                if ($line->getAmount() < 0 && !Transaction::TYPES[$transaction->getType()]['outbound']) {
                    $message = 'Ce type de transaction ne peut pas avoir de montant négatif.';
                    $form->get('transactionLines')->get($kLine)->get('amount')->addError(new FormError($message));
                    $transactionLineErrors[$kLine] = $message;
                }
                if ($line->getAmount() > 0 && !Transaction::TYPES[$transaction->getType()]['inbound']) {
                    $message = 'Ce type de transaction ne peut pas avoir de montant positif.';
                    $form->get('transactionLines')->get($kLine)->get('amount')->addError(new FormError($message));
                    $transactionLineErrors[$kLine] = $message;
                }
            }

            if ($form->isValid()) {
                $transactionRepository->save($transaction, true);

                return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('transaction/form.html.twig', array_merge($viewParams, [
            'accounts' => $accountRepository->findAll(),
            'form' => $form,
            'transactionLineErrors' => $transactionLineErrors,
        ]));
    }

    #[Route('/new/{type}', name: 'app_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, string $type, TransactionRepository $transactionRepository, AccountRepository $accountRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $transaction = new Transaction();
        $transaction->setType($type);

        return $this->handleForm($request, $transaction, $transactionRepository, $accountRepository, [
            'title' => 'Nouvelle transaction ('.Transaction::TYPES[$type]['name'].')',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction, TransactionRepository $transactionRepository, AccountRepository $accountRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->handleForm($request, $transaction, $transactionRepository, $accountRepository, [
            'title' => 'Modifier la transaction',
            'btn_label' => 'Modifier',
        ]);
    }

    #[Route('/{id}', name: 'app_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, Transaction $transaction, TransactionRepository $transactionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->request->get('_token'))) {
            $transactionRepository->remove($transaction, true);
        }

        return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
    }
}
