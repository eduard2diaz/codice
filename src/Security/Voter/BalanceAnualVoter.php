<?php

namespace App\Security\Voter;

use App\Entity\BalanceAnual;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BalanceAnualVoter extends Voter
{
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT','DELETE','DOWNLOAD'])
            && $subject instanceof BalanceAnual;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
            case 'EDIT':
            case 'DELETE':
            case 'DOWNLOAD':
                if($this->decisionManager->decide($token, array('ROLE_ADMIN')))
                    return $token->getUser()->getInstitucion()->getId()==$subject->getInstitucion()->getId();
                else
                    return $subject->getArea()!=null && $token->getUser()->getArea()->getId()==$subject->getArea()->getId();
            break;
        }

        return false;
    }
}
