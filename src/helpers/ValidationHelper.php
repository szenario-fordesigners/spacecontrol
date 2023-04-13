<?php

namespace szenario\craftspacecontrol\helpers;
use Craft;
class ValidationHelper
{
    // EMAIL VALIDATION
    public static function validateEmailAddresses($emails) {
        $validEmails = [];

        foreach($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $email;
            }
        }

        return $validEmails;
    }
}