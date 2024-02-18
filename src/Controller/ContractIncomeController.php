<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\ContractIncome;
use App\Form\ContractIncomeType;
use App\Repository\ContractIncomeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contract/{id}/incomes', name: 'app_income')]
class ContractIncomeController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET'])]
    public function index(Contract $contract): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $contract);

        /**
         * @var ContractIncome[] $incomes
         */
        $incomes = [];
        foreach ($contract->getIncomes() as $income) {
            $incomes[$income->getPeriod()->format('Y-m')] = $income;
        }

        $totalPlanned = 0;
        $offsetPlanned = 0;
        $totalBilled = 0;
        $offsetBilled = 0;
        $totalReceived = 0;
        $offsetReceived = 0;

        foreach ($incomes as $income) {
            $totalPlanned += $income->getPlanned();
            $totalBilled += $income->getBilled();
            $totalReceived += $income->getReceived();
        }

        if (is_null($contract->getLastPeriod()) || date_create() < $contract->getLastPeriod()) {
            $end = date_create();

            if (count($incomes)) {
                $lastIncome = end($incomes);

                $offsetPlanned = $lastIncome->getPlanned();
                if (count($incomes) > 1) {
                    $prevIncome = prev($incomes);
                    $offsetPlanned += $prevIncome->getPlanned();
                }
                $totalPlanned -= $offsetPlanned;

                if ($contract->isBillingDelayed()) {
                    $offsetBilled = $lastIncome->getBilled();
                    $totalBilled -= $offsetBilled;
                }
                if ($contract->isReceptionDelayed()) {
                    $offsetReceived = $lastIncome->getReceived();
                    $totalReceived -= $offsetReceived;
                }
            }
        } else {
            $end = $contract->getLastPeriod();
        }

        $date = clone $contract->getStart();
        $date->modify('first day of this month');
        while ($date <= $end) {
            if (!isset($incomes[$date->format('Y-m')])) {
                $income = new ContractIncome();
                $income->setContract($contract);
                $income->setPeriod($date);
                $incomes[$date->format('Y-m')] = $income;
            }
            $date->modify('+1 month');
        }
        ksort($incomes);

        return $this->render('income/index.html.twig', [
            'contract' => $contract,
            'incomes' => $incomes,
            'totalPlanned' => $totalPlanned,
            'offsetPlanned' => $offsetPlanned,
            'totalBilled' => $totalBilled,
            'offsetBilled' => $offsetBilled,
            'totalReceived' => $totalReceived,
            'offsetReceived' => $offsetReceived,
        ]);
    }

    #[Route('/{period}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Contract $contract,
        string $period,
        ContractIncomeRepository $contractIncomeRepository
    ): Response {
        $this->denyAccessUnlessGranted('ADD_INCOME', $contract);

        $periodObj = date_create($period.'-01');
        $income = $contractIncomeRepository->getContractIncomeByMonth($contract, $periodObj);
        if (is_null($income)) {
            $income = new ContractIncome();
            $income->setContract($contract);
            $income->setPeriod($periodObj);

            $fmt = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
            $fmt->setPattern('MMMM yyyy');
            $income->setDescription('Millésime '.ucfirst($fmt->format($periodObj)));
        }

        if ($income->getIsPlanned() && is_null($income->getPlanned())) {
            $amount = $income->getContract()->getAmount();
            if ('daily' == $income->getContract()->getFrequency()) {
                $start = clone max(
                    $income->getContract()->getStart(),
                    $income->getPeriod()
                );
                $end = (clone $income->getPeriod())->modify('+1 month');
                if ($income->getContract()->getEnd() && $end > $income->getContract()->getEnd()) {
                    $end = $income->getContract()->getEnd();
                }

                $planned = 0;
                while ($start < $end) {
                    if ($start->format('N') < 6) {
                        ++$planned;
                    }
                    $start->modify('+1 day');
                }

                $amount *= $planned;
            }
            $income->setPlanned($amount);
        }

        $lastPeriodObj = (clone $periodObj)->modify('-1 month');
        $last_period = $contractIncomeRepository->getContractIncomeByMonth($contract, $lastPeriodObj);
        if (!$income->isFirstPeriod() && $last_period) {
            if (
                $income->getContract()->isReceptionDelayed()
                && $income->getIsReceived()
                && is_null($income->getReceived())
            ) {
                $income->setReceived($last_period->getPlanned());
            }
            if (
                $income->getContract()->isBillingDelayed()
                && $income->getIsBilled()
                && is_null($income->getBilled())
            ) {
                $income->setBilled($last_period->getPlanned());
            }
        }

        if (!$income->isPeriodDuringContract()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ContractIncomeType::class, $income);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$income->getIsPlanned()) {
                $income->setPlanned(0);
            }
            if (!$income->getIsBilled()) {
                $income->setBilled(0);
            }
            if (!$income->getIsReceived()) {
                $income->setReceived(0);
            }
            $contractIncomeRepository->save($income, true);

            return $this->redirectToRoute('app_income_index', ['id' => $contract->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('income/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{period}', name: '_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Contract $contract,
        string $period,
        ContractIncomeRepository $contractIncomeRepository
    ): Response {
        $this->denyAccessUnlessGranted('DELETE_INCOME', $contract);

        $income = $contractIncomeRepository->getContractIncomeByMonth($contract, date_create($period.'-01'));
        if (!$income) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete'.$contract->getId().'-'.$period, $request->request->get('_token'))) {
            $contractIncomeRepository->remove($income, true);
        }

        return $this->redirectToRoute('app_income_index', ['id' => $contract->getId()], Response::HTTP_SEE_OTHER);
    }
}
