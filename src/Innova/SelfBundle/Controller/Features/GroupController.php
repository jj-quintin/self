<?php

namespace Innova\SelfBundle\Controller\Features;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Innova\SelfBundle\Entity\Group;
use Innova\SelfBundle\Form\Type\GroupType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Group controller.
 *
 * @Route("admin/editor/groups")
 * @ParamConverter("group", isOptional="true", class="InnovaSelfBundle:Group",  options={"id" = "groupId"})
 * @ParamConverter("session", isOptional="true", class="InnovaSelfBundle:Session", options={"id" = "sessionId"})
 * @ParamConverter("user", isOptional="true", class="InnovaSelfBundle:User", options={"id" = "userId"})
 */
class GroupController extends Controller
{
    /**
     *
     * @Route("/", name="editor_groups")
     * @Method("GET")
     * @Template("InnovaSelfBundle:Features:Group/list.html.twig")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->get('security.context')->getToken()->getUser();

        if ($this->get("self.right.manager")->checkRight("right.listgroup", $currentUser)) {
            $groups = $em->getRepository("InnovaSelfBundle:Group")->findAll();
        } else {
            $groups = $this->getDoctrine()->getManager()->getRepository('InnovaSelfBundle:Group')->findAuthorized($currentUser);
        }

        return array("groups" => $groups);
    }

    /**
     *
     * @Route("/{groupId}/display", name="editor_group_display")
     * @Method({"GET", "POST"})
     * @Template("InnovaSelfBundle:Features:Group/display.html.twig")
     */
    public function displayAction(Group $group)
    {
        return array('group' => $group);
    }

    /**
     *
     * @Route("/create", name="editor_group_create")
     * @Method({"GET", "POST"})
     * @Template("InnovaSelfBundle:Features:Group/new.html.twig")
     */
    public function newAction(Request $request)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();

        if ($this->get("self.right.manager")->checkRight("right.creategroup", $currentUser)) {
            $em = $this->getDoctrine()->getManager();
            $group = new Group();
            $group->setName('Nouveau groupe');

            $form = $this->handleForm($group, $request);
            if (!$form) {
                $this->get("session")->getFlashBag()->set('info', "Le groupe a bien été créé");

                return $this->redirect($this->generateUrl('editor_groups', array()));
            }

            return array('form' => $form->createView(), 'group' => $group);
        }

        return;
    }

    /**
     *
     * @Route("/{groupId}/remove", name="editor_group_delete", options = {"expose"=true})
     * @Method("DELETE")
     * @Template("InnovaSelfBundle:Features:Group/list.html.twig")
     */
    public function deleteAction(Group $group)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();

        if ($this->get("self.right.manager")->checkRight("right.deletegroup", $currentUser, $group)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();

            $this->get("session")->getFlashBag()->set('info', "Le groupe a bien été supprimé");

            return $this->redirect($this->generateUrl('editor_groups'));
        }

        return;
    }

    /**
     *
     * @Route("/{groupId}/edit", name="editor_group_edit")
     * @Method({"GET", "POST"})
     * @Template("InnovaSelfBundle:Features:Group/new.html.twig")
     */
    public function editAction(Group $group, Request $request)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();

        if ($this->get("self.right.manager")->checkRight("right.editgroup", $currentUser, $group)) {
            $form = $this->handleForm($group, $request);

            if (!$form) {
                $this->get("session")->getFlashBag()->set('info', "Le groupe a bien été modifié");

                return $this->redirect($this->generateUrl('editor_group_edit', array('groupId' => $group->getId())));
            }

            return array('form' => $form->createView(), 'group' => $group);
        }

        return;
    }

    /**
     *
     * @Route("/{groupId}/import", name="editor_group_import_user")
     * @Method({"GET", "POST"})
     * @Template("InnovaSelfBundle:Features:Group/import.html.twig")
     */
    public function importUserAction(Group $group, Request $request)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();

        if ($this->get("self.right.manager")->checkRight("right.csvimportgroup", $currentUser, $group)) {
            $defaultData = array();
            $form = $this->createFormBuilder($defaultData)
                                            ->add('file', 'file')
                                            ->add('submit', 'submit', array('label' => 'generic.save', 'attr' => array('class' => 'btn btn-default btn-primary')))
                                            ->getForm();
            if ($request->isMethod('POST')) {
                $form->bind($request);
                $data = $form->getData();
                $fileName = $data["file"]->getClientOriginalName();
                $path = $this->get('kernel')->getRootDir()."/data/importCsv/";
                $completePath = $path."/".$fileName;
                $data["file"]->move($path, $fileName);

                $this->get("self.user.manager")->importCsv($group, $completePath);

                return $this->redirect($this->generateUrl('editor_group_edit', array('groupId' => $group->getId())));
            }

            return array('group' => $group, 'form' => $form->createView());
        }

        return;
    }

    /**
     * Handles session form
     */
    private function handleForm(Group $group, $request)
    {
        $form = $this->get('form.factory')->createBuilder(new GroupType(), $group)->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($group);
                $em->flush();

                return;
            }
        }

        return $form;
    }
}