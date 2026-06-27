<?php

/**
 * @file BookbotSsoPlugin.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class BookbotSsoPlugin
 *
 * @brief Lets the bookbot app log a linked user straight into OMP via a
 *   short-lived HMAC-signed token, so authors never see a second login.
 */

namespace APP\plugins\generic\bookbotSso;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class BookbotSsoPlugin extends GenericPlugin
{
    public function getDisplayName()
    {
        return 'bookbot SSO';
    }

    public function getDescription()
    {
        return 'Token-based auto-login from the bookbot app (no second password prompt).';
    }

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                Hook::add('LoadHandler', $this->callbackLoadHandler(...));
            }
            return true;
        }
        return false;
    }

    /**
     * Route /<context>/bbsso/login to the SSO handler.
     */
    public function callbackLoadHandler($hookName, $args)
    {
        $page = &$args[0];
        $op = &$args[1];
        $handler = &$args[3];

        if ($page === 'bbsso' && $op === 'login') {
            $handler = new BookbotSsoHandler($this);
            return true;
        }
        return false;
    }
}
