<?php

namespace App\Security\Voter;

use App\Entity\Autor;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class AutorVoter extends Voter
{
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['EDIT', 'DELETE'])  && $subject instanceof Autor;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'EDIT':
                return $subject->getId()==$token->getUser()->getId() || $this->decisionManager->decide($token, array('ROLE_ADMIN'));
            break;
            case 'DELETE':
                return $subject->getId()!=$token->getUser()->getId() || $this->decisionManager->decide($token, array('ROLE_ADMIN'));
            break;
            case 'SEGUIR':
                return $subject->getId()!=$token->getUser()->getId();
            break;
        }

        return false;
    }
}
