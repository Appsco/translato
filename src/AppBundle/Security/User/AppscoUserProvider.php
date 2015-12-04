<?php

namespace AppBundle\Security\User;

use AppBundle\Model\Account;
use AppBundle\Services\Store;
use Appsco\Dashboard\ApiBundle\Security\Core\Authentication\Token\AppscoToken;
use Appsco\Dashboard\ApiBundle\Security\Core\User\AppscoUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AppscoUserProvider implements AppscoUserProviderInterface
{
    /** @var Store */
    private $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @param \Appsco\Dashboard\ApiBundle\Security\Core\Authentication\Token\AppscoToken $token
     *
     * @return UserInterface
     */
    public function create(AppscoToken $token)
    {
        return new Account($token->getUsername(), $this->store->load($token->getUsername()));
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        return new Account($username, $this->store->load($username));
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return new Account($user->getUsername(), $this->store->load($user->getUsername()));
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'AppBundle\Model\Account');
    }
}
