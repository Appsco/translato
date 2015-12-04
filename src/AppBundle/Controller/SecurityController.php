<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class SecurityController extends Controller
{
    /**
     * @Route("/login/start", name="login")
     */
    public function appscoStartAction()
    {

    }

    /**
     * @Route("/login/callback", name="login_check")
     */
    public function appscoCallbackAction()
    {

    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {

    }

    /**
     * @Route("/login/out", name="logout_done")
     */
    public function logoutDoneAction()
    {
        return $this->render('security/logout_done.html.twig');
    }

    /**
     * @Route("/login/failure", name="failure")
     */
    public function failureAction(Request $request)
    {
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
            $request->getSession()->remove(Security::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'security/failure.html.twig',
            array(
                'error' => $error ? $error->getMessage() : null,
            )
        );
    }
}
