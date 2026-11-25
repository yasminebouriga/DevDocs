<?php

namespace App\Security\Voter;

use App\Entity\Tutorial;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class TutorialVoter extends Voter
{
    public const EDIT = 'TUTORIAL_EDIT';
    public const DELETE = 'TUTORIAL_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Tutorial;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Tutorial $tutorial */
        $tutorial = $subject;

        return $tutorial->getAuthor() === $user;
    }
}
