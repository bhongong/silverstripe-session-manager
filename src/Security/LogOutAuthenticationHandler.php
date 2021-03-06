<?php

namespace Kinglozzer\SessionManager\Security;

use Kinglozzer\SessionManager\Model\LoginSession;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\AuthenticationHandler;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;

/**
 * This is separate to LogInAuthenticationHandler so that it can be registered with
 * Injector and called *before* the other AuthenticationHandler::logOut() implementations
 */
class LogOutAuthenticationHandler implements AuthenticationHandler
{
    public function authenticateRequest(HTTPRequest $request)
    {
    }

    public function logIn(Member $member, $persistent = false, HTTPRequest $request = null)
    {
    }

    public function logOut(HTTPRequest $request = null)
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $member = Security::getCurrentUser();

        if (RememberLoginHash::config()->get('logout_across_devices')) {
            foreach ($member->LoginSessions() as $session) {
                $session->delete();
            }
        } else {
            $loginSessionID = $request->getSession()->get($loginHandler->getSessionVariable());
            $loginSession = LoginSession::get()->byID($loginSessionID);
            if ($loginSession && $loginSession->canDelete($member)) {
                $loginSession->delete();
            }
        }

        $request->getSession()->clear($loginHandler->getSessionVariable());
    }
}
