<?php

namespace dokuwiki\plugin\fsdiscordnotifier\helper;

use InvalidArgumentException;

/**
 * Context of the edit.
 *
 * Things extracted from $INFO.
 *
 * @property ?string $username
 */
class Context
{
    public function __get($name)
    {
        $method = "get$name";
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Invalid property: $name");
        }

        return $this->{$method}();
    }

    public function getUsername(): ?string
    {
        global $INFO, $USERINFO;

        // https://github.com/michitux/dokuwiki-plugin-move/issues/235
        $userinfo = $INFO['userinfo'] ?? $USERINFO;

        return $userinfo['name'] ?? null;
    }
}
