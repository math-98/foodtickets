<?php

namespace App\Security\Voter;

use App\Entity\Transaction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionVoter extends Voter
{
    public const DELETE = 'DELETE';
    public const EDIT = 'EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::DELETE, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Transaction) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = $subject;

        return match ($attribute) {
            self::DELETE, self::EDIT => $transaction->getUser() === $user,
            default => throw new \LogicException('This code should not be reached!'),
        };
    }
}
