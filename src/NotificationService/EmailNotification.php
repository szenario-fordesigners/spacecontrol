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

            $domain = explode('//', App::env('PRIMARY_SITE_URL'))[1];

            try {


                $message = new Message();
                $message->setFrom('spacecontrol@' . $domain);
                $message->setTo($email);
                $message->setSubject($template['subject']);
                $message->setTextBody($template['body']);

                Craft::$app->getMailer()->send($message);

            } catch (\Exception $e) {
                Craft::error('Failed to send email to: ' . $email . ' - ' . $template['subject'], "spacecontrol");
                continue;
            }
        }
    }
}