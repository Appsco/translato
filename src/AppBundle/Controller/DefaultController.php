<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\AddNewFileType;
use AppBundle\Model\Account;
use AppBundle\Model\Project;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("default/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        /** @var Account $account */
        $account = $this->getUser();
        if ($account->getTranslations()->getProjectCount() == 0) {
            return $this->redirectToRoute('project.new');
        }

        $projectId = $request->get('project');

        if ((null == $projectId) || (null == $account->getTranslations()->getProject($projectId))) {
            /** @var Project $project */
            $project = $account->getTranslations()->getFirstProject();

            return $this->redirectToRoute('homepage', ['project'=>$project->getId()]);
        }

        $project = $account->getTranslations()->getProject($projectId);
        if (null == $project) {
            throw new \LogicException('No project');
        }

        $loadedProject = $this->get('loader')->load($project, $request->get('domain'), $request->get('lang'));
        if (null == $loadedProject) {
            return $this->redirectToRoute('project.edit', ['id'=>$projectId]);
        }

        return [
            'info' => $loadedProject,
            'sourceLanguage' => 'en',
        ];
    }

    /**
     * @Route("/project/new", name="project.new")
     * @Template("default/newProject.html.twig")
     */
    public function newProjectAction(Request $request)
    {
        $form = $this->createForm(new AddNewFileType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $this->get('store')->addFile($this->getUser(), $data['file'], $data['name']);

            return $this->redirectToRoute('homepage');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/project/{id}/edit", name="project.edit")
     */
    public function editProjectAction($id)
    {

    }
}
