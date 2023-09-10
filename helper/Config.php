<?php

namespace dokuwiki\plugin\slacknotifier\helper;

use dokuwiki\Extension\PluginInterface;

/**
 * A class to provide lazy access to plugin config
 *
 * @property string $namespaces
 * @property string $webhook
 * @property bool $notify_create
 * @property bool $notify_edit
 * @property bool $notify_delete
 * @property bool $notify_edit_minor
 * @property bool $show_summary
 */
class Config
{
    /** @var PluginInterface */
    private $plugin;

    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    public function __get($name)
    {
        return $this->plugin->getConf($name, null);
    }

    /**
     * Return true if $namespace is configured as valid namespace.
     */
    public function isValidNamespace(string $namespace): bool
    {
        if (!$this->namespaces) {
            return true;
        }

        $namespaces = explode(',', $this->namespaces);
        // Handle root namespace
        if ($namespace === '') {
            return in_array(':', $namespaces, true);
        }
        return in_array($namespace, $namespaces, true);
    }
}
