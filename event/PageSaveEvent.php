<?php

namespace dokuwiki\plugin\slacknotifier\event;

use InvalidArgumentException;

/**
 * @property string $changeType
 * @property string|null $summary
 * @property string $id
 * @property int|false $oldRevision
 * @property int $newRevision
 * @link https://www.dokuwiki.org/devel:event:common_wikipage_save
 */
class PageSaveEvent extends BaseEvent
{
    const TYPE_RENAME = 'rename';

    private const EVENT_TYPE = [
        DOKU_CHANGE_TYPE_EDIT => 'edit',
        DOKU_CHANGE_TYPE_MINOR_EDIT => 'edit minor',
        DOKU_CHANGE_TYPE_CREATE => 'create',
        DOKU_CHANGE_TYPE_DELETE => 'delete',
        self::TYPE_RENAME => 'rename',
    ];

    public function getEventType(): ?string
    {
        return self::EVENT_TYPE[$this->changeType] ?? null;
    }

    /**
     * Root namespace of the page.
     * Handle case of page being in root namespace
     */
    public function getNamespace(): string
    {
        $pos = strpos($this->id, ':');
        if($pos === false)
        {
            return ('');
        }

        return explode(':', $this->id, 2)[0];
    }

    public function isCreate(): bool
    {
        return $this->changeType === DOKU_CHANGE_TYPE_CREATE;
    }

    public function isDelete(): bool
    {
        return $this->changeType === DOKU_CHANGE_TYPE_DELETE;
    }

    public function convertToRename(PageSaveEvent $deleteEvent)
    {
        // Sanity check
        if (
            !$this->isCreate() ||
            !$deleteEvent->isDelete() ||
            $this->summary !== $deleteEvent->summary
        ) {
            throw new InvalidArgumentException("Unexpected event");
        }

        $this->changeType = self::TYPE_RENAME;
        $this->oldRevision = $deleteEvent->oldRevision;
    }
}
