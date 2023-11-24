<?php
namespace szenario\craftspacecontrol\NotificationService;

use Craft;
use craft\helpers\App;
use craft\mail\Message;

class EmailNotification {
    public static function sendEmailNotification($settings, $template) {
        // get email addresses
        $recipients = $settings->emailRecipients;

        foreach ($recipients as $recipient) {
            $email = $recipient[0];
            if (empty($email)) {
                continue;
            }

            Craft::info("Email notification recipient: " . $email, "spacecontrol");

            $domain = explode('//', \craft\helpers\UrlHelper::siteUrl())[1];
            $truncatedDomain = rtrim($domain, '/') ?: $domain;

            try {
                $message = new Message();
                $message->setFrom(['no-reply@' . $truncatedDomain => 'SpaceControl']);
                $message->setSender('no-reply@' . $truncatedDomain);
                $message->setTo($email);
                $message->setSubject( $template['subject']);
                $message->setTextBody($template['body']);

                Craft::$app->getMailer()->send($message);

            } catch (\Exception $e) {
                Craft::error('Failed to send email to: ' . $email . ' - ' . $template['subject'], "spacecontrol");
                Craft::error($e->getMessage(), "spacecontrol");
                continue;
            }
        }
    }
}