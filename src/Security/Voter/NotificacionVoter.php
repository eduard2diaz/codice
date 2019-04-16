<?php

namespace App\Security\Voter;

use App\Entity\Notificacion;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificacionVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'DELETE']) && $subject instanceof Notificacion;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
            case 'DELETE':
                return $subject->getDestinatario()->getId() == $token->getUser()->getId();
                break;
        }

        return false;
    }
}
