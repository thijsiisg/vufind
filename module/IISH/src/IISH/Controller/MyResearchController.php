<?php
namespace IISH\Controller;
use VuFind\Controller\MyResearchController as VuFindMyResearchController;

/**
 * Controller for the user account area.
 *
 * Override to add support for password change in case of MultiAuth.
 *
 * @package IISH\Controller
 */
class MyResearchController extends VuFindMyResearchController {

    /**
     * Send account recovery email
     *
     * @return View object
     */
    public function recoverAction() {
        // Make sure we're configured to do this
        $method = trim($this->params()->fromQuery('auth_method'));
        if (!$this->getAuthManager()->supportsRecovery($method)) {
            $this->flashMessenger()->setNamespace('error')->addMessage('recovery_disabled');

            return $this->redirect()->toRoute('myresearch-home');
        }
        if ($this->getUser()) {
            return $this->redirect()->toRoute('myresearch-home');
        }

        // Database
        $table = $this->getTable('User');
        $user = false;

        // Check if we have a submitted form, and use the information to get the user's information
        if ($email = $this->params()->fromPost('email')) {
            $user = $table->getByEmail($email);
        }
        elseif ($username = $this->params()->fromPost('username')) {
            $user = $table->getByUsername($username, false);
        }

        $view = $this->createViewModel();
        $view->useRecaptcha = $this->recaptcha()->active('passwordRecovery');

        // If we have a submitted form
        if ($this->formWasSubmitted('submit', $view->useRecaptcha)) {
            if ($user && ($user->pass_hash !== null)) {
                $this->sendRecoveryEmail($user, $this->getConfig(), $method);
            }
            else {
                $this->flashMessenger()->setNamespace('error')->addMessage('recovery_user_not_found');
            }
        }

        return $view;
    }
}
