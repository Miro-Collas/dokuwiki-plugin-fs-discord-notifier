<?php
/**
 * DokuWiki Plugin Slack Notifier (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Logger;
use dokuwiki\plugin\slacknotifier\event\PageMoveEvent;
use dokuwiki\plugin\slacknotifier\event\PageSaveEvent;
use dokuwiki\plugin\slacknotifier\helper\Config;
use dokuwiki\plugin\slacknotifier\helper\Context;
use dokuwiki\plugin\slacknotifier\helper\Formatter;

class action_plugin_slacknotifier extends ActionPlugin
{
    /** @var Event[] */
    private $changes = [];
    /** @var PageMoveEvent[] */
    private $created = [];
    /** @var PageMoveEvent[] */
    private $deleted = [];
    private $inRename = false;
    /** @var Config */
    private $config;

    public function __construct() {
        $this->config = new Config($this);
    }

    public function register(EventHandler $controller): void
    {
        if (!$this->config->webhook) {
            return;
        }

        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'AFTER', $this, 'handleSave');
        $controller->register_hook('PLUGIN_MOVE_PAGE_RENAME', 'BEFORE', $this, 'handleRenameBefore', 'BEFORE');
        $controller->register_hook('PLUGIN_MOVE_PAGE_RENAME', 'AFTER', $this, 'handleRenameAfter', 'AFTER');
    }

    public function handleRenameBefore(Event $rawEvent): void
    {
        $this->inRename = true;
        $event = new PageMoveEvent($rawEvent);
        $this->created[$event->dst_id] = $event;
        $this->deleted[$event->src_id] = $event;
    }

    public function handleRenameAfter(): void
    {
        if (!$this->inRename) {
            // Sanity check
            throw new RuntimeException('Logic error: in rename is false after rename');
        }
        $this->inRename = false;

        foreach ($this->getEvents() as $event) {
            $this->processEvent($event);
        }
    }

    public function handleSave(Event $event): void
    {
        if ($this->inRename) {
            $this->changes[] = $event;
            return;
        }

        $this->processEvent(new PageSaveEvent($event));
    }

    /**
     * @return PageSaveEvent[]
     */
    private function getEvents(): array
    {
        $events = [];
        foreach ($this->changes as $rawEvent) {
            $event = new PageSaveEvent($rawEvent);
            $pageId = $event->id;

            if ($event->isCreate() && isset($this->created[$pageId])) {
                $moveEvent = $this->created[$pageId];
                $moveEvent->setCreatedPageEvent($event);
                unset($this->created[$pageId]);
            } elseif ($event->isDelete() && isset($this->deleted[$pageId])) {
                $moveEvent = $this->deleted[$pageId];
                $createdEvent = $moveEvent->getCreatedPageEvent();
                $createdEvent->convertToRename($event);
                unset($this->deleted[$pageId]);
                // Skip delete event itself
                continue;
            }

            $events[] = $event;
        }
        $this->changes = [];

        return $events;
    }

    private function processEvent(PageSaveEvent $event): void
    {
        if (!$this->config->isValidNamespace($event->getNamespace())) {
            return;
        }

        if (!$this->isValidEvent($event->getEventType())) {
            return;
        }

        $formatter = new Formatter($this->config);
        $formatted = $formatter->format($event, new Context());
        $this->submitPayload($this->config->webhook, $formatted);
    }

    private function isValidEvent(?string $eventType): bool
    {
        if ($eventType === 'create' && $this->config->notify_create) {
            return true;
        } elseif ($eventType === 'edit' && $this->config->notify_edit) {
            return true;
        } elseif ($eventType === 'edit minor' && $this->config->notify_edit && $this->config->notify_edit_minor) {
            return true;
        } elseif ($eventType === 'delete' && $this->config->notify_delete) {
            return true;
        } elseif ($eventType === 'rename' && $this->config->notify_create && $this->config->notify_delete) {
            return true;
        }

        return false;
    }

    private function submitPayload(string $url, array $payload): void
    {
        $http = new DokuHTTPClient();
        $http->headers['Content-Type'] = 'application/json';
        // we do single ops here, no need for keep-alive
        $http->keep_alive = false;

        $result = $http->post($url, ['payload' => json_encode($payload)]);
        if ($result !== 'ok') {
            $ctx = [
                'resp_body' => $http->resp_body,
                'result' => $result,
                'http_error' => $http->error,
            ];
            Logger::error('Error posting to Slack', $ctx, __FILE__, __LINE__);
        }
    }
}
