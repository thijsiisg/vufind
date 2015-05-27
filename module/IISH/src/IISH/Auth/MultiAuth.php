<?php
namespace IISH\Auth;
use VuFind\Exception\Auth as AuthException;
use VuFind\Auth\MultiAuth as VuFindMultiAuth;

/**
 * MultiAuth Authentication plugin.
 * Override to add support for creating a new account / lost password.
 *
 * @package IISH\Auth
 */
class MultiAuth extends VuFindMultiAuth {

    /**
     * Create a new user account from the request.
     *
     * @param \Zend\Http\PhpEnvironment\Request $request Request object containing new account details.
     *
     * @throws AuthException
     * @return \VuFind\Db\Row\User New user row.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create($request) {
        $manager = $this->getPluginManager();

        // Try authentication methods until we find one that works:
        foreach ($this->methods as $method) {
            $authenticator = $manager->get($method);
            $authenticator->setConfig($this->getConfig());
            try {
                $user = $authenticator->create($request);

                // If we got this far without throwing an exception, we can break
                // out of the loop -- we created a new account!
                break;
            }
            catch (AuthException $exception) {
                // Do nothing -- just keep looping!  We'll deal with the exception
                // below if we don't find a successful login anywhere.
            }
        }

        // At this point, there are three possibilities: $user is a valid,
        // logged-in user; $exception is an Exception that we need to forward
        // along; or both variables are undefined, indicating that $this->methods
        // is empty and thus something is wrong!
        if (!isset($user)) {
            if (isset($exception)) {
                throw $exception;
            }
            else {
                throw new AuthException('authentication_error_technical');
            }
        }

        return $user;
    }

    /**
     * Update a user's password from the request.
     *
     * @param \Zend\Http\PhpEnvironment\Request $request Request object containing
     *                                                   new account details.
     *
     * @throws AuthException
     * @return \VuFind\Db\Row\User New user row.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updatePassword($request) {
        $manager = $this->getPluginManager();

        // Try authentication methods until we find one that works:
        foreach ($this->methods as $method) {
            $authenticator = $manager->get($method);
            $authenticator->setConfig($this->getConfig());
            try {
                $user = $authenticator->updatePassword($request);

                // If we got this far without throwing an exception, we can break
                // out of the loop -- we updated the password!
                break;
            }
            catch (AuthException $exception) {
                // Do nothing -- just keep looping!  We'll deal with the exception
                // below if we don't find a successful login anywhere.
            }
        }

        // At this point, there are three possibilities: $user is a valid user;
        // $exception is an Exception that we need to forward along;
        // or both variables are undefined, indicating that $this->methods
        // is empty and thus something is wrong!
        if (!isset($user)) {
            if (isset($exception)) {
                throw $exception;
            }
            else {
                throw new AuthException('authentication_error_technical');
            }
        }

        return $user;
    }

    /**
     * Does this authentication method support account creation?
     *
     * @return bool
     */
    public function supportsCreation() {
        $manager = $this->getPluginManager();

        // Try authentication methods until we find one that supports creation
        foreach ($this->methods as $method) {
            $authenticator = $manager->get($method);
            $authenticator->setConfig($this->getConfig());

            if ($authenticator->supportsCreation()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does this authentication method support password changing
     *
     * @return bool
     */
    public function supportsPasswordChange() {
        $manager = $this->getPluginManager();

        // Try authentication methods until we find one that supports password change
        foreach ($this->methods as $method) {
            $authenticator = $manager->get($method);
            $authenticator->setConfig($this->getConfig());

            if ($authenticator->supportsPasswordChange()) {
                return true;
            }
        }

        return false;
    }
}