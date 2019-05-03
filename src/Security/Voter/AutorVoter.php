<?php

namespace App\Security\Voter;

use App\Entity\Autor;
use App\Entity\Usuario;
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
        return in_array($attribute, ['VIEWSTATICS','SEGUIR','EDIT', 'DELETE'])  && $subject instanceof Autor;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'VIEWSTATICS':
                return $user instanceof Autor && ($subject->getId()==$token->getUser()->getId() || ($user->getInstitucion()->getId()==$subject->getInstitucion()->getId() && ($this->decisionManager->decide($token, array('ROLE_ADMIN')) || $subject->esSubordinado($token->getUser()))));
            case 'EDIT':
                return $user instanceof Usuario || $subject->getId()==$token->getUser()->getId() || ($user->getInstitucion()->getId()==$subject->getInstitucion()->getId() && ($this->decisionManager->decide($token, array('ROLE_ADMIN')) || $subject->esSubordinado($token->getUser())));
            break;
            case 'DELETE':
                return $user instanceof Usuario || ($this->decisionManager->decide($token, array('ROLE_ADMIN')) && $user->getInstitucion()->getId()==$subject->getInstitucion()->getId() && $subject->getId()!=$token->getUser()->getId());
            break;
            case 'SEGUIR':
                return $user instanceof Autor && $subject->getId()!=$token->getUser()->getId();
            break;
        }

        return false;
    }
}
