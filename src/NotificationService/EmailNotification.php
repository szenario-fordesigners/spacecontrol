<?php
namespace szenario\craftspacecontrol\NotificationService;

use Craft;
use craft\helpers\App;
use craft\mail\Message;

class EmailNotification
{
    public static function sendEmailNotification($settings, $template)
    {
        // get email addresses
        $recipients = $settings->emailRecipients;

        foreach ($recipients as $recipient) {
            $email = $recipient[0];
            if (empty($email)) {
                continue;
            }

            Craft::info("Email notification recipient: " . $email, "spacecontrol");

            try {
                $domain = explode('//', \craft\helpers\UrlHelper::siteUrl())[1];
                $truncatedDomain = rtrim($domain, '/') ?: $domain;
            } catch (\Exception $e) {
                Craft::error("Could not get domain", "spacecontrol");
                return null;
            }

            try {
                $message = new Message();

                // Get the from address from Craft's mailer settings
                $mailer = Craft::$app->getMailer();
                $fromAddress = $mailer->from;

                // If no from address is configured in Craft, use a fallback
                if (empty($fromAddress)) {
                    $fromAddress = 'noreply@' . $truncatedDomain;
                }

                $message->setFrom([$fromAddress => 'SpaceControl']);
                $message->setSender($fromAddress);
                $message->setTo($email);
                $message->setSubject($template['subject']);
                $message->setTextBody($template['body']);

                $result = $mailer->send($message);

                if (!$result) {
                    Craft::error('Mailer returned false when sending email to: ' . $email, "spacecontrol");
                }

            } catch (\Exception $e) {
                Craft::error('Failed to send email to: ' . $email . ' - ' . $template['subject'], "spacecontrol");
                Craft::error($e->getMessage(), "spacecontrol");
                continue;
            }
        }
    }
}