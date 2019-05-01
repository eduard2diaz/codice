<?php

namespace App\Security\Voter;

use App\Entity\Area;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class AreaVoter extends Voter
{
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT','DELETE'])  && $subject instanceof Area;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'VIEW':
            case 'EDIT':
            case 'DELETE':
                return $this->decisionManager->decide($token, array('ROLE_SUPERADMIN')) || ($this->decisionManager->decide($token, array('ROLE_ADMIN')) && $token->getUser()->getInstitucion()->getId()==$subject->getInstitucion()->getId());
                break;
        }

        return false;
    }
}
