<?php

namespace Innova\SelfBundle\Manager\Identity;

use Innova\SelfBundle\Entity\QuestionnaireIdentity\AuthorRight;

class AuthorRightManager
{
    protected $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create($array)
    {
        $em = $this->entityManager;

        foreach ($array as $el) {
            if (!$this->findByName($el)) {
                $r = new AuthorRight();
                $r->setName($el);
                $em->persist($r);
            }
        }

        $em->flush();

        return true;
    }

    public function delete($array)
    {
        $em = $this->entityManager;

        foreach ($array as $el) {
            if ($r = $this->findByName($el)) {
                $em->remove($r);
            }
        }

        $em->flush();

        return true;
    }

    private function findByName($name)
    {
        $em = $this->entityManager;

        return $em->getRepository('InnovaSelfBundle:QuestionnaireIdentity\AuthorRight')->findOneByName($name);
    }
}
