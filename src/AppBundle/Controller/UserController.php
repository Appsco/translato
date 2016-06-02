<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
}
