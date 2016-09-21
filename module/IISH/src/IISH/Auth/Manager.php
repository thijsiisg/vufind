<?php
namespace IISH\Auth;
use VuFind\Auth\Manager as VuFindManager;

/**
 * Wrapper class for handling logged-in user in session.
 *
 * Override to add support for password changes in case of MultiAuth.
 *
 * @package IISH\Auth
 */
class Manager extends VuFindManager {

    /**
     * Is new passwords currently allowed?
     *
     * @param string $authMethod optional; check this auth method rather than the one in config file
     *
     * @return bool: defaults to true
     */
    public function supportsPasswordChange($authMethod = null) {
        if ($authMethod !== null) {
            $this->setActiveAuthClass($authMethod);
        }

        $user = $this->isLoggedIn();
        if ($this->getAuth()->supportsPasswordChange() && ($user->pass_hash !== null)) {
            return isset($this->config->Authentication->change_password)
            && $this->config->Authentication->change_password;
        }

        return true;
    }
}
