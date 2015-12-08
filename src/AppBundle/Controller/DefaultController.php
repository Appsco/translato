<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\CloneProjectType;
use AppBundle\Form\Type\NewProjectType;
use AppBundle\Form\Type\UploadFilesType;
use AppBundle\Model\Account;
use AppBundle\Model\Project;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("default/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $account = $this->getAccount();
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
     * @Route("/project/list", name="project.list")
     * @Template("default/projectList.html.twig")
     */
    public function projectListAction()
    {
        return [];
    }

    /**
     * @Route("/file/download/{projectId}/{domain}/{locale}", name="file.download")
     */
    public function fileDownloadAction($projectId, $domain, $locale)
    {
        $account = $this->getAccount();

        $path = $account->getTranslations()->getProject($projectId)->getFilePathName($domain, $locale);
        $pathInfo = pathinfo($path);

        $response = new BinaryFileResponse($path);
        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $pathInfo['basename']
        );
        $response->headers->set('Content-Disposition', $d);

        return $response;
    }

    /**
     * @Route("/file/view/{projectId}/{domain}/{locale}", name="file.view")
     */
    public function fileViewAction($projectId, $domain, $locale)
    {
        $account = $this->getAccount();

        $path = $account->getTranslations()->getProject($projectId)->getFilePathName($domain, $locale);

        return new BinaryFileResponse($path);
    }

    /**
     * @Route("/file/delete/{projectId}/{domain}/{locale}", name="file.delete")
     * @Template("default/fileDelete.html.twig")
     */
    public function fileDeleteAction($projectId, $domain, $locale, Request $request)
    {
        $project = $this->getAccount()->getTranslations()->getProject($projectId);
        if (null == $project) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm('form');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('store')->deleteFile($this->getAccount(), $project, $domain, $locale);

            return $this->redirectToRoute('project.list');
        }

        return [
            'form' => $form->createView(),
            'project' => $project,
            'domain' => $domain,
            'locale' => $locale,
        ];
    }

    /**
     * @Route("/project/delete/{projectId}", name="project.delete")
     * @Template("default/projectDelete.html.twig")
     */
    public function projectDeleteAction($projectId, Request $request)
    {
        $project = $this->getAccount()->getTranslations()->getProject($projectId);
        if (null == $project) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm('form');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('store')->deleteProject($this->getAccount(), $project);

            return $this->redirectToRoute('project.list');
        }

        return [
            'form' => $form->createView(),
            'project' => $project,
        ];
    }

    /**
     * @Route("/project/new", name="project.new")
     * @Template("default/newProject.html.twig")
     */
    public function projectNewAction(Request $request)
    {
        $form = $this->createForm(new NewProjectType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $project = $this->get('project_creator')->create($this->getAccount(), $data['name'], $data['files']);

            return $this->redirectToRoute('homepage', ['project'=>$project->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/project/{projectId}/file/upload", name="file.upload")
     * @Template("default/fileUpload.html.twig")
     */
    public function fileUploadAction($projectId, Request $request)
    {
        $project = $this->getAccount()->getTranslations()->getProject($projectId);
        if (null == $project) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(new UploadFilesType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $results = $this->get('file_adder')->addFiles($this->getAccount(), $project, $data['files'], $data['save']);

            return $this->render('default/fileUploadResult.html.twig', [
                'project' => $project,
                'results' => $results,
                'save' => $data['save'],
            ]);
        }

        return [
            'form' => $form->createView(),
            'project' => $project,
        ];
    }

    /**
     * @Route("/save/{projectId}/{domain}/{locale}", name="translate", options={"i18n" = false}))
     * @Method("PUT")
     */
    public function translateAction($projectId, $domain, $locale, Request $request)
    {
        $id = $request->query->get('id');
        $message = $request->request->get('message');

        $project = $this->getAccount()->getTranslations()->getProject($projectId);
        if (null == $project) {
            throw new NotFoundHttpException();
        }

        if (false == in_array($locale, $project->getLocales($domain))) {
            throw new BadRequestHttpException(sprintf('Invalid locale "%s" for domain "%s" in project "%s"', $locale, $domain, $projectId));
        }

        $format = $project->getFileFormat($domain, $locale);
        $file = $project->getFilePathName($domain, $locale);

        $this->get('jms_translation.updater')->updateTranslation($file, $format, $domain, $locale, $id, $message);

        return new Response();
    }

    /**
     * @Route("/project/clone", name="project.clone")
     * @Template("default/projectClone.html.twig")
     */
    public function cloneProject(Request $request)
    {
        $form = $this->createForm(new CloneProjectType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $project = $this->get('store')->cloneProject($this->getAccount(), $data['reference']);

            return $this->redirectToRoute('homepage', ['project'=>$project->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @return Account
     */
    private function getAccount()
    {
        return $this->getUser();
    }
}
