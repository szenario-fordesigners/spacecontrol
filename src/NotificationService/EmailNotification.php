<?php
namespace szenario\craftspacecontrol\NotificationService;

use Craft;
use craft\mail\Message;

class EmailNotification
{
    public static function sendEmailNotification($settings, $template)
    {
        $recipients = $settings->emailRecipients;

        foreach ($recipients as $recipient) {
            $email = $recipient[0];
            if (empty($email)) {
                continue;
            }

            try {
                $parsedUrl = parse_url(\craft\helpers\UrlHelper::siteUrl());
                $truncatedDomain = $parsedUrl['host'] ?? 'unknown-domain';
            } catch (\Exception $e) {
                Craft::error("Could not resolve site URL for email sender", "spacecontrol");
                return;
            }

            try {
                $mailer = Craft::$app->getMailer();
                $fromRaw = $mailer->from;

                if (is_array($fromRaw)) {
                    $fromEmail = array_key_first($fromRaw);
                } else {
                    $fromEmail = $fromRaw;
                }

                if (empty($fromEmail)) {
                    $fromEmail = 'noreply@' . $truncatedDomain;
                }

                $message = new Message();
                $message->setFrom([$fromEmail => 'SpaceControl']);
                $message->setSender($fromEmail);
                $message->setTo($email);
                $message->setSubject($template['subject']);
                $message->setTextBody($template['body']);

                $result = $mailer->send($message);

                if (!$result) {
                    Craft::error("Failed to send email to {$email}", "spacecontrol");
                } else {
                    Craft::info("Email sent to {$email}", "spacecontrol");
                }
            } catch (\Exception $e) {
                Craft::error("Failed to send email to {$email}: {$e->getMessage()}", "spacecontrol");
                continue;
            }
        }
    }
}
