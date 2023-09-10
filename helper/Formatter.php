<?php

namespace dokuwiki\plugin\slacknotifier\helper;

use dokuwiki\plugin\slacknotifier\event\PageSaveEvent;

class Formatter
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function format(PageSaveEvent $event, Context $context): array
    {
        $actionMap = [
            'create' => 'created',
            'edit' => 'updated',
            'edit minor' => 'updated (minor edit)',
            'delete' => 'removed',
            'rename' => 'renamed',
        ];
        $eventType = $event->getEventType();
        $action = $actionMap[$eventType] ?? null;
        $username = $context->username ?: 'Anonymous';
        $page = $event->id;
        $link = $this->buildUrl($page, $event->newRevision);
        $title = "{$username} {$action} page <{$link}|{$page}>";
        if ($eventType !== 'delete') {
            $oldRev = $event->oldRevision;
            if ($oldRev) {
                $diffURL = $this->buildUrl($page, $event->newRevision, $event->oldRevision);
                $title .= " (<{$diffURL}|Compare changes>)";
            }
        }

        $formatted = ['text' => $title];
        if ($event->summary && $this->config->show_summary) {
            $formatted['attachments'] = [
                [
                    'fallback' => 'Change summary',
                    'title' => 'Summary',
                    'text' => "{$event->summary}\n- {$username}",
                ],
            ];
        }

        return $formatted;
    }
/*
    wl() - https://xref.dokuwiki.org/reference/dokuwiki/nav.html?inc/common.php.source.html#l493
*/

    private function buildUrl(string $page, int $rev, ?int $oldRev = null): ?string
    {
        $urlParameters = $oldRev ? "do=diff&rev2[0]=$oldRev&rev2[1]=$rev" : "";

        return wl($page, $urlParameters, true, '&');
    }
}
