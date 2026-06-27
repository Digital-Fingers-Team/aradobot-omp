<?php

/**
 * @file BookbotSsoHandler.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class BookbotSsoHandler
 *
 * @brief Validates a bookbot SSO token and opens an OMP session for the user.
 */

namespace APP\plugins\generic\bookbotSso;

use APP\facades\Repo;
use APP\handler\Handler;
use PKP\config\Config;
use PKP\core\PKPRequest;
use PKP\security\Role;
use PKP\security\Validation;

class BookbotSsoHandler extends Handler
{
    protected $plugin;

    public function __construct(BookbotSsoPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * GET /<context>/bbsso/login?token=<payload>.<sig>
     *
     * The token is base64url(JSON{uid,exp}) "." base64url(HMAC-SHA256(payload)),
     * signed with the secret shared with bookbot ([bookbot] sso_secret in config).
     */
    public function login($args, $request)
    {
        if (Validation::isLoggedIn()) {
            $request->redirect(null, 'submissions');
        }

        $secret = Config::getVar('bookbot', 'sso_secret');
        $token = (string) $request->getUserVar('token');
        $userId = $secret ? $this->verifyToken($token, $secret) : null;

        if ($userId === null) {
            $this->fail($request);
        }

        $user = Repo::user()->get($userId);
        if (!$user) {
            $this->fail($request);
        }

        $reason = null;
        if (!Validation::registerUserSession($user, $reason)) {
            $this->fail($request);
        }

        // Grant the Author role so the user can start submissions and enter the
        // editorial workflow (self-registration only yields the Reader role).
        $this->ensureAuthorRole($user, $request);

        // Land the author on their submissions dashboard.
        $request->redirect(null, 'submissions');
    }

    /**
     * Idempotently assign the context's Author user group to the user.
     */
    private function ensureAuthorRole($user, PKPRequest $request): void
    {
        $context = $request->getContext();
        if (!$context) {
            return;
        }
        $authorGroup = Repo::userGroup()
            ->getByRoleIds([Role::ROLE_ID_AUTHOR], $context->getId())
            ->first();
        if ($authorGroup && !Repo::userGroup()->userInGroup($user->getId(), $authorGroup->id)) {
            Repo::userGroup()->assignUserToGroup($user->getId(), $authorGroup->id);
        }
    }

    /**
     * Verify the HMAC signature + expiry. Returns the user id or null.
     */
    private function verifyToken(string $token, string $secret): ?int
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }
        [$payloadB64, $sig] = $parts;

        $expected = $this->b64url(hash_hmac('sha256', $payloadB64, $secret, true));
        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $payload = json_decode($this->b64urlDecode($payloadB64), true);
        if (!is_array($payload) || empty($payload['uid']) || empty($payload['exp'])) {
            return null;
        }
        if ((int) $payload['exp'] < time()) {
            return null;
        }
        return (int) $payload['uid'];
    }

    private function b64url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function b64urlDecode(string $s): string
    {
        return (string) base64_decode(strtr($s, '-_', '+/'));
    }

    private function fail(PKPRequest $request): void
    {
        // Bounce to the normal login page rather than leaking detail.
        $request->redirect(null, 'login');
    }
}
