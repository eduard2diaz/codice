<?php

namespace App\Security\Voter;

use App\Entity\Publicacion;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PublicacionVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['NEW','EDIT', 'DELETE'])  && $subject instanceof Publicacion;
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
            case 'NEW':
            case 'EDIT':
            case 'DELETE':
                    return $subject->getAutor()->getId()==$token->getUser()->getId() || $subject->getAutor()->esSubordinado($token->getUser())==true;
                break;
        }

        return false;
    }
}
