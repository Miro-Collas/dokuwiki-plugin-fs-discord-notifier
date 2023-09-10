<?php

namespace dokuwiki\plugin\fsdiscordnotifier\event;

use dokuwiki\Extension\Event;
use InvalidArgumentException;

/**
 * @property string $src_id
 * @property string $dst_id
 * @link https://www.dokuwiki.org/plugin:move#for_plugin_authors
 */
class PageMoveEvent extends BaseEvent
{
    /** @var PageSaveEvent|null */
    private $createdPageEvent;

    public function setCreatedPageEvent(PageSaveEvent $event)
    {
        $this->createdPageEvent = $event;
    }

    public function getCreatedPageEvent(): ?PageSaveEvent
    {
        return $this->createdPageEvent;
    }
}
