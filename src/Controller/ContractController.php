<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\AccountRepository;
use App\Repository\ContractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contract', name: 'app_contract')]
class ContractController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET'])]
    public function index(ContractRepository $contractRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('contract/index.html.twig', [
            'contracts' => $contractRepository->findAll(),
        ]);
    }

    private function handleForm(
        Request $request,
        Contract $contract,
        ContractRepository $contractRepository,
        AccountRepository $accountRepository,
        array $viewParams
    ): Response {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $form->get('monthly_amount')->addError(new FormError('test'));
            if ($form->isValid()) {
                if ($contract->getMonthlyAmount() && $contract->getAccount()->getIndividualPrice()) {
                    $contract->setMonthlyAmount(floor($contract->getAccount()->getIndividualPrice()));
                }
                $contractRepository->save($contract, true);

                return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('contract/form.html.twig', array_merge($viewParams, [
            'form' => $form->createView(),
            'accounts' => $accountRepository->findAll(),
        ]));
    }

    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContractRepository $contractRepository, AccountRepository $accountRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->handleForm($request, new Contract(), $contractRepository, $accountRepository, [
            'title' => 'Nouveau contrat',
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contract $contract, ContractRepository $contractRepository, AccountRepository $accountRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->handleForm($request, $contract, $contractRepository, $accountRepository, [
            'button_label' => 'Modifier',
            'title' => 'Modifier le contrat '.$contract->getName(),
        ]);
    }

    #[Route('/{id}', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Contract $contract, ContractRepository $contractRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->request->get('_token'))) {
            $contractRepository->remove($contract, true);
        }

        return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
    }
}
