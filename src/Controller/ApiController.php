<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\AccountRepository;
use App\Repository\ContractIncomeRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_api')]
class ApiController extends AbstractController
{
    #[Route('/list/count', name: '_list_count', methods: ['GET'])]
    public function listCount(Request $request, TransactionRepository $transactionRepository): Response
    {
        $transactionsReq = $transactionRepository
            ->createQueryBuilder('t')
            ->select('t.name, COUNT(t.id) as count')
            ->where('t.user = :userId')
            ->andWhere('t.type = :type')
            ->setParameters([
                'userId' => $this->getUser()->getId(),
                'type' => 'expense',
            ])
            ->groupBy('t.name');
        if ($request->query->has('start')) {
            $transactionsReq
                ->andWhere('t.date >= :start')
                ->setParameter('start', $request->query->get('start'));
        }
        if ($request->query->has('end')) {
            $transactionsReq
                ->andWhere('t.date <= :end')
                ->setParameter('end', $request->query->get('end'));
        }
        $transactions = $transactionsReq
            ->getQuery()
            ->getArrayResult();

        if ($request->query->has('pct_limit')) {
            $total = array_sum(array_column($transactions, 'count'));
            $transactions = array_filter($transactions, function ($transaction) use ($total, $request) {
                return $transaction['count'] / $total * 100 >= $request->query->get('pct_limit');
            });

            $subTotal = array_sum(array_column($transactions, 'count'));
            if ($subTotal < $total) {
                $transactions[] = [
                    'name' => 'Autres',
                    'count' => $total - $subTotal,
                ];
            }
        }

        usort($transactions, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return new JsonResponse($transactions);
    }

    #[Route('/list/spent', name: '_list_spent', methods: ['GET'])]
    public function listSpent(Request $request, TransactionRepository $transactionRepository): Response
    {
        $transactionsReq = $transactionRepository
            ->createQueryBuilder('t')
            ->where('t.user = :userId')
            ->andWhere('t.type = :type')
            ->setParameters([
                'userId' => $this->getUser()->getId(),
                'type' => 'expense',
            ]);
        if ($request->query->has('start')) {
            $transactionsReq
                ->andWhere('t.date >= :start')
                ->setParameter('start', $request->query->get('start'));
        }
        if ($request->query->has('end')) {
            $transactionsReq
                ->andWhere('t.date <= :end')
                ->setParameter('end', $request->query->get('end'));
        }
        /** @var Transaction[] $transactions */
        $transactions = $transactionsReq
            ->getQuery()
            ->getResult();

        $spent = [];
        foreach ($transactions as $transaction) {
            $spent[$transaction->getName()] = ($spent[$transaction->getName()] ?? 0) - $transaction->getAmount();
        }

        if ($request->query->has('pct_limit')) {
            $total = array_sum($spent);
            $transactions = array_filter($spent, function ($value) use ($total, $request) {
                return $value / $total * 100 >= $request->query->get('pct_limit');
            });

            $subTotal = array_sum($spent);
            if ($subTotal < $total) {
                $transactions[] = [
                    'name' => 'Autres',
                    'count' => $total - $subTotal,
                ];
            }
        }

        $transactions = array_map(function ($name, $amount) {
            return [
                'name' => $name,
                'spent' => number_format($amount, 2, thousands_separator: ''),
            ];
        }, array_keys($spent), $spent);
        usort($transactions, function ($a, $b) {
            return $b['spent'] <=> $a['spent'];
        });

        return new JsonResponse($transactions);
    }

    #[Route('/graph', name: '_list_earned', methods: ['GET'])]
    public function graph(
        Request $request,
        AccountRepository $accountRepository,
        ContractIncomeRepository $contractIncomeRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $data = [];
        $accounts = [];

        // Transactions
        $transactions = $transactionRepository->findUserTransactions($this->getUser());
        foreach ($transactions as $transaction) {
            $row = $data[$transaction->getDate()->format('Y-m-d H:i:s')] ?? [];

            foreach ($transaction->getTransactionLines() as $line) {
                $value = $line->getAmount() * ($line->getAccount()->getIndividualPrice() ?? 1);

                if (!array_key_exists($line->getAccount()->getId(), $accounts)) {
                    $accounts[$line->getAccount()->getId()] = [
                        'enabled' => false,
                        'sum' => 0,
                    ];
                }

                if ($request->query->has('start') && $transaction->getDate() < new \DateTime($request->query->get('start'))) {
                    $accounts[$line->getAccount()->getId()]['sum'] += $value;
                } elseif (!$request->query->has('end') || $transaction->getDate() <= new \DateTime($request->query->get('end'))) {
                    if (!array_key_exists($line->getAccount()->getId(), $row)) {
                        $row[$line->getAccount()->getId()] = 0;
                    }

                    $accounts[$line->getAccount()->getId()]['enabled'] = true;
                    $row[$line->getAccount()->getId()] += $value;
                }
            }

            if (count($row)) {
                $data[$transaction->getDate()->format('Y-m-d H:i:s')] = $row;
            }
        }

        // Incomes
        $incomes = $contractIncomeRepository->findUserIncomes($this->getUser());
        foreach ($incomes as $income) {
            $value = $income->getReceived() * ($income->getContract()->getAccount()->getIndividualPrice() ?? 1);

            if (!array_key_exists($income->getContract()->getAccount()->getId(), $accounts)) {
                $accounts[$income->getContract()->getAccount()->getId()] = [
                    'enabled' => false,
                    'sum' => 0,
                ];
            }

            if ($request->query->has('start') && $income->getPeriod() < new \DateTime($request->query->get('start'))) {
                $accounts[$income->getContract()->getAccount()->getId()]['sum'] += $value;
            } elseif (!$request->query->has('end') || $income->getPeriod() <= new \DateTime($request->query->get('end'))) {
                $accounts[$income->getContract()->getAccount()->getId()]['enabled'] = true;

                $row = $data[$income->getPeriod()->format('Y-m-d H:i:s')] ?? [];
                if (!array_key_exists($income->getContract()->getAccount()->getId(), $row)) {
                    $row[$income->getContract()->getAccount()->getId()] = 0;
                }

                $row[$income->getContract()->getAccount()->getId()] += $value;
                $data[$income->getPeriod()->format('Y-m-d H:i:s')] = $row;
            }
        }

        // Filter accounts not touched during period
        $accounts = array_filter($accounts, function ($account) {
            return $account['enabled'];
        });

        // Generate CSV
        $csv = fopen('php://temp', 'w');
        fputcsv($csv, array_merge(
            ['Date'],
            array_map(function ($accountId) use ($accountRepository) {
                $account = $accountRepository->find($accountId);

                return $account->getName();
            }, array_keys($accounts))
        ));

        // Fill CSV
        ksort($data);
        foreach ($data as $date => $row) {
            foreach ($row as $accountId => $value) {
                $accounts[$accountId]['sum'] += $value;
            }

            fputcsv($csv, array_merge(
                [$date],
                array_map(function ($account) {
                    return number_format($account['sum'], 2);
                }, $accounts)
            ));
        }
        rewind($csv);

        return new Response(stream_get_contents($csv), headers: [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="graph.csv"',
        ]);
    }
}
