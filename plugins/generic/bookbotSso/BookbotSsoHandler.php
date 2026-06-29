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
        $secret = Config::getVar('bookbot', 'sso_secret');
        $token = (string) $request->getUserVar('token');
        $payload = $secret ? $this->verifyToken($token, $secret) : null;

        if ($payload === null) {
            $this->fail($request);
        }

        $targetId = (int) $payload['uid'];

        $user = Repo::user()->get($targetId);
        if (!$user) {
            $this->fail($request);
        }

        // Always (re)bind the session to the token's user. This overwrites any
        // existing session, so switching bookbot accounts (a different email ->
        // a different OMP user) lands on the correct OMP account instead of
        // keeping whoever was logged in before.
        $reason = null;
        if (!Validation::registerUserSession($user, $reason)) {
            $this->fail($request);
        }

        // Grant roles: Author for everyone (so they can submit and enter the
        // editorial workflow); plus OMP admin roles for bookbot admins.
        $this->ensureRoles($user, $request, !empty($payload['adm']));

        // Land the user on their dashboard.
        $request->redirect(null, 'submissions');
    }

    /**
     * Idempotently assign the user's OMP roles.
     *
     * Everyone gets the context Author role. bookbot admins additionally get
     * Press Manager (this press) and Site Administrator (sitewide), mirroring a
     * full OMP admin account.
     */
    private function ensureRoles($user, PKPRequest $request, bool $isAdmin): void
    {
        $context = $request->getContext();
        if ($context) {
            $authorGroup = Repo::userGroup()
                ->getByRoleIds([Role::ROLE_ID_AUTHOR], $context->getId())
                ->first();
            $this->assignIfMissing($user->getId(), $authorGroup?->id);

            if ($isAdmin) {
                $managerGroup = Repo::userGroup()
                    ->getByRoleIds([Role::ROLE_ID_MANAGER], $context->getId())
                    ->first();
                $this->assignIfMissing($user->getId(), $managerGroup?->id);
            }
        }

        if ($isAdmin) {
            // Site Administrator groups are sitewide (no context).
            foreach (Repo::userGroup()->getArrayIdByRoleId(Role::ROLE_ID_SITE_ADMIN) as $groupId) {
                $this->assignIfMissing($user->getId(), $groupId);
            }
        }
    }

    private function assignIfMissing(int $userId, ?int $userGroupId): void
    {
        if ($userGroupId && !Repo::userGroup()->userInGroup($userId, $userGroupId)) {
            Repo::userGroup()->assignUserToGroup($userId, $userGroupId);
        }
    }

    /**
     * Verify the HMAC signature + expiry. Returns the decoded payload or null.
     */
    private function verifyToken(string $token, string $secret): ?array
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
        return $payload;
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
