<?php

namespace App\Security\Voter;

use App\Entity\BalanceAnual;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BalanceAnualVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['VIEW', 'EDIT','DELETE','DOWNLOAD'])
            && $subject instanceof BalanceAnual;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'VIEW':
            case 'EDIT':
            case 'DELETE':
            case 'DOWNLOAD':
                return $token->getUser()->getInstitucion()->getId()==$subject->getInstitucion()->getId();
                break;
        }

        return false;
    }
}
