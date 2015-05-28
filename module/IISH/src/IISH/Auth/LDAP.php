<?php
namespace IISH\Auth;
use VuFind\Auth\LDAP as VuFindLDAP;
use VuFind\Exception\Auth as AuthException;

/**
 * LDAP authentication class.
 *
 * Override to implement workaround for LDAP connection:
 * Prevent call to ldap_start_tls().
 *
 * @package IISH\Auth
 */
class LDAP extends VuFindLDAP {

    /**
     * Communicate with LDAP and obtain user details.
     *
     * @throws AuthException
     * @return \VuFind\Db\Row\User Object representing logged-in user.
     */
    protected function bindUser() {
        // Try to connect to LDAP and die if we can't; note that some LDAP setups
        // will successfully return a resource from ldap_connect even if the server
        // is unavailable -- we need to check for bad return values again at search
        // time!
        $ldapConnection = @ldap_connect(
            $this->getSetting('host'), $this->getSetting('port')
        );
        if (!$ldapConnection) {
            throw new AuthException('authentication_error_technical');
        }

        // Set LDAP options -- use protocol version 3
        @ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);

        /*
           WORKAROUND:
        // if the host parameter is not specified as ldaps://
        // then we need to initiate TLS so we
        // can have a secure connection over the standard LDAP port.
        if (false && stripos($this->getSetting('host'), 'ldaps://') === false) {
            if (!@ldap_start_tls($ldapConnection)) {
                throw new AuthException('authentication_error_technical');
            }
        }*/

        // If bind_username and bind_password were supplied in the config file, use
        // them to access LDAP before proceeding.  In some LDAP setups, these
        // settings can be excluded in order to skip this step.
        if ($this->getSetting('bind_username') != ''
            && $this->getSetting('bind_password') != ''
        ) {
            $ldapBind = @ldap_bind(
                $ldapConnection, $this->getSetting('bind_username'),
                $this->getSetting('bind_password')
            );
            if (!$ldapBind) {
                throw new AuthException('authentication_error_technical');
            }
        }

        // Search for username
        $ldapFilter = $this->getSetting('username') . '=' . $this->username;
        $ldapSearch = @ldap_search(
            $ldapConnection, $this->getSetting('basedn'), $ldapFilter
        );
        if (!$ldapSearch) {
            throw new AuthException('authentication_error_technical');
        }

        $info = ldap_get_entries($ldapConnection, $ldapSearch);
        if ($info['count']) {
            // Validate the user credentials by attempting to bind to LDAP:
            $ldapBind = @ldap_bind(
                $ldapConnection, $info[0]['dn'], $this->password
            );
            if ($ldapBind) {
                // If the bind was successful, we can look up the full user info:
                $ldapSearch = ldap_search(
                    $ldapConnection, $this->getSetting('basedn'), $ldapFilter
                );
                $data = ldap_get_entries($ldapConnection, $ldapSearch);

                return $this->processLDAPUser($data);
            }
        }

        throw new AuthException('authentication_error_invalid');
    }
}