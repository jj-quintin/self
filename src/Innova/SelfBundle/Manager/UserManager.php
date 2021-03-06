<?php

namespace Innova\SelfBundle\Manager;

use Innova\SelfBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Innova\SelfBundle\Form\Type\UserType;

class UserManager
{
    protected $entityManager;
    protected $formFactory;
    protected $fosUserManager;
    protected $session;
    protected $securityContext;
    protected $translator;

    public function __construct($entityManager, $formFactory, $fosUserManager, $session, $securityContext, $translator)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->fosUserManager = $fosUserManager;
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->translator = $translator;
    }

    public function setLocale(User $user, $locale)
    {
        $user->setLocale($locale);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function deleteUser(User $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->session->getFlashBag()->set('success', "L'utilisateur a bien été supprimé.");

        return $this;
    }

    public function checkExistingUsername(User $user)
    {
        $user = $this->entityManager->getRepository('InnovaSelfBundle:User')->findAnotherByUsername($user);
        if ($user) {
            return 'Existing user with this username';
        }

        return;
    }

    public function checkExistingEmail(User $user)
    {
        $user = $this->entityManager->getRepository('InnovaSelfBundle:User')->findAnotherByEmail($user);
        if ($user) {
            return 'Existing user with this email';
        }

        return;
    }

    public function handleForm(User $user, Request $request)
    {
        $form = $this->formFactory->createBuilder(new UserType($this->entityManager), $user)->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $hasError = false;
                if ($error = $this->checkExistingUsername($user)) {
                    $form->addError(new FormError($error));
                    $hasError = true;
                }
                if ($error = $this->checkExistingEmail($user)) {
                    $form->addError(new FormError($error));
                    $hasError = true;
                }

                if ($hasError) {
                    return $form;
                }
                $user->setEnabled(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return;
            }
        }

        return $form;
    }

    public function getConnected()
    {
        $connectedUsers = array();
        $sessions = $this->getLastSessions(300);

        foreach ($sessions as $session) {
            $data = $this->getSessionData($session);

            // If this is a session belonging to an anonymous user, do nothing
            if (!array_key_exists('_security_main', $data)) {
                continue;
            }

            // Grab security data
            $data = $data['_security_main'];
            $data = unserialize($data);
            $username = $data->getUser()->getUsername();
            $user = $this->entityManager->getRepository('InnovaSelfBundle:User')->findOneByUsername($username);

            if (!in_array($user, $connectedUsers)) {
                $connectedUsers[] = $user;
            }
        }

        return $connectedUsers;
    }

    public function getAuthCount()
    {
        $connectedUsers = array();
        $sessions = $this->getLastSessions(300);
        foreach ($sessions as $session) {
            $data = $this->getSessionData($session);

            // If this is a session belonging to an anonymous user, do nothing
            if (!array_key_exists('_security_main', $data)) {
                continue;
            }

            // Grab security data
            $data = $data['_security_main'];
            $data = unserialize($data);
            $username = $data->getUser()->getUsername();

            if (!in_array($username, $connectedUsers)) {
                $connectedUsers[] = $username;
            }
        }

        return count($connectedUsers);
    }

    public function checkCourse()
    {
        $user = $this->securityContext->getToken()->getUser();
        if (!$user->getCourse()) {
            $this->session->getFlashBag()->set('warning', $this->translator->trans('warning_message_course'));
        }

        return;
    }

    private function getLastSessions($threshold)
    {
        $limit = time() - $threshold;

        $dql = 'select s from InnovaSelfBundle:PDOSession s
            where s.session_time >= ?1
            order by s.session_time desc';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter(1, $limit);
        $sessions = $query->getResult();

        return $sessions;
    }

    private function getSessionData($session)
    {
        $data = stream_get_contents($session->getSessionValue());
        $data = str_replace('_sf2_attributes|', '', $data);

        $data = unserialize($data);

        return $data;
    }
}
