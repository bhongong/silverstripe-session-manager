<?php

namespace Kinglozzer\SessionManager\Extensions;

use Kinglozzer\SessionManager\Forms\GridFieldRevokeLoginSessionAction;
use Kinglozzer\SessionManager\Model\LoginSession;
use SilverStripe\Control\Session;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\ORM\FieldType\DBDatetime;

class MemberExtension extends Extension
{
    private static $has_many = [
        'LoginSessions' => LoginSession::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('LoginSessions');

        $sessionLifetime = $this->getSessionLifetime();
        $maxAge = DBDatetime::now()->getTimestamp() - $sessionLifetime;
        $currentSessions = $this->owner->LoginSessions()->filterAny([
            'Persistent' => true,
            'LastAccessed:GreaterThan' => date('Y-m-d H:i:s', $maxAge)
        ]);

        $fields->addFieldToTab(
            'Root.Sessions',
            GridField::create(
                'LoginSessions',
                'Sessions',
                $currentSessions,
                GridFieldConfig_Base::create()
                    ->addComponent(GridFieldRevokeLoginSessionAction::create())
            )
        );
    }

    /**
     * @return int
     */
    protected function getSessionLifetime()
    {
        if ($lifetime = Session::config()->get('timeout')) {
            return $lifetime;
        }

        return LoginSession::config()->get('default_session_lifetime');
    }
}
