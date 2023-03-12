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
                'spent' => number_format($amount, 2),
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
        $accounts = $accountRepository->findBy(['user' => $this->getUser()]);

        $transactionsReq = $transactionRepository
            ->createQueryBuilder('t')
            ->where('t.user = :userId')
            ->setParameter('userId', $this->getUser()->getId());
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
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($transactions as $k => $transaction) {
            $row = $data[$transaction->getDate()->format('Y-m-d H:i:s')] ?? end($data) ?? array_combine(
                array_column($accounts, 'id'),
                array_fill(0, count($accounts), 0)
            );

            foreach ($transaction->getTransactionLines() as $line) {
                $row[$line->getAccount()->getId()] += $line->getAmount();
            }
            $data[$transaction->getDate()->format('Y-m-d H:i:s')] = $row;
        }

        $incomes = $contractIncomeRepository->findUserIncomes($this->getUser());
        if ($request->query->has('start') || $request->query->has('end')) {
            $incomes = array_filter($incomes, function ($income) use ($request) {
                if ($request->query->has('start') && $income->getPeriod() < $request->query->get('start')) {
                    return false;
                }
                if ($request->query->has('end') && $income->getPeriod() > $request->query->get('end')) {
                    return false;
                }

                return true;
            });
        }
        foreach ($incomes as $income) {
            $row = $data[$income->getPeriod()->format('Y-m-d H:i:s')] ?? end($data) ?? array_combine(
                array_column($accounts, 'id'),
                array_fill(0, count($accounts), 0)
            );
            $row[$income->getContract()->getAccount()->getId()] += $income->getReceived();
            $data[$income->getPeriod()->format('Y-m-d H:i:s')] = $row;
        }

        ksort($data);
        $csv = fopen('php://temp/maxmemory:'.(5 * 1024 * 1024), 'r+');
        fputcsv($csv, array_merge(['Date'], array_column($accounts, 'name')));
        foreach ($data as $date => $row) {
            fputcsv($csv, array_merge([$date], $row));
        }

        return new Response(
            stream_get_contents($csv),
            Response::HTTP_OK
        );
    }
}
