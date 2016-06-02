<?php

namespace AppBundle\Controller;

use AppBundle\Model\Account;
use Appsco\Dashboard\ApiBundle\Security\Core\Authentication\Token\AppscoToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @Route("/user/list", name="user.list")
     * @Template("user/index.html.twig")
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = [];
        foreach ($this->get('user_store')->listUsers() as $username) {
            $users[$username] = $this->get('store')->load($username);
        }

        return [
            'users' => $users,
        ];
    }

    /**
     * @param $username
     *
     * @return Response
     *
     * @Route("user/su/{username}", name="user.su")
     */
    public function suAction($username)
    {
        $translations = $this->get('store')->load($username);
        $account = new Account($username, $translations);
        $appscoUser = new \Appsco\Dashboard\ApiBundle\Model\Account();
        $appscoUser->setEmail($username);

        $token = new AppscoToken($account, $account->getRoles(), $appscoUser, null, null);
        $this->get('security.token_storage')->setToken($token);

        return $this->redirectToRoute('project.list');
    }
}
