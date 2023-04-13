<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use craft\mail\Message;
use craft\helpers\App;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class SpaceControlChecker extends \craft\queue\BaseJob
{
    public function execute($queue): void
    {
        // 1. get current disk usage
        // 2. get current disk limit
        // 3. compare
        // 4. if limit is reached:
        // 5. get mailTimeThreshold
        // 6. get lastSent
        // 7. compare
        // 8. if lastSent is smaller than time() - mailTimeThreshold, send mail
        // 9. set lastSent to time()

        $diskUsageAbsolute = disk_total_space("/") - disk_free_space("/");
        $diskUsagePercent = $diskUsageAbsolute / disk_total_space("/") * 100;
        $diskLimits = SettingsHelper::getDiskLimitPercent();

        // if we are below the usage limit, do not do anything
        if ($diskUsagePercent < $diskLimits['diskLimitPercent']) {
            return;
        }

        // if lastSent is close to now than specified in mailTimeThreshold, do not do anything
        if (SettingsHelper::getLastSent() > time() - SettingsHelper::getMailTimeThreshold()) {
            return;
        }

        $domain = explode('//', App::env('PRIMARY_SITE_URL'))[1];

        // send the notification mail to admins if any mails are specified
        if (count(SettingsHelper::getAdminRecipientsArray())) {
            $message = new Message();

            $message->setFrom('spacecontrol@' . $domain);
            $message->setTo(SettingsHelper::getAdminRecipientsArray());
            $message->setSubject('Oh Hai admin');
            $message->setTextBody('Hello from the queue system! 👋' . 'ADMINS: ' . count(SettingsHelper::getAdminRecipientsArray()) . ' ' . 'CLIENTS: ' . count(SettingsHelper::getClientRecipientsArray()));

            Craft::$app->getMailer()->send($message);
        }


        // send the notification mail to clients if any mails are specified
        if (count(SettingsHelper::getClientRecipientsArray())) {
            $message = new Message();

            $message->setFrom('spacecontrol@' . $domain);
            $message->setTo(SettingsHelper::getClientRecipientsArray());
            $message->setSubject('Oh Hai client');
            $message->setTextBody('Hello from the queue system! 👋' . " " . $diskUsagePercent . ": " . $diskLimits['diskLimitPercent'] . ' | ' . SettingsHelper::getAdminRecipientsArray()[0]);

            Craft::$app->getMailer()->send($message);
        }

        // set lastSent to now
        SettingsHelper::setLastSent(time());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Spacecontrol');
    }
}
