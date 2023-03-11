<?php

namespace App\Security\Voter;

use App\Entity\Contract;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ContractVoter extends Voter
{
    public const ADD_INCOME = 'ADD_INCOME';
    public const DELETE = 'DELETE';
    public const DELETE_INCOME = 'DELETE_INCOME';
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [
            self::ADD_INCOME,
            self::DELETE,
            self::DELETE_INCOME,
            self::EDIT,
            self::VIEW,
        ])) {
            return false;
        }

        if (!$subject instanceof Contract) {
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

        /** @var Contract $contract */
        $contract = $subject;

        return match ($attribute) {
            self::ADD_INCOME, self::DELETE, self::DELETE_INCOME, self::EDIT, self::VIEW => $contract->getUser() === $user,
            default => throw new \LogicException('This code should not be reached!'),
        };
    }
}
