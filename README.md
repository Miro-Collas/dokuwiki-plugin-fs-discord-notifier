# DokuWiki Discord Notifier

A DokuWiki plugin that notifies a Discord channel room of wiki edits.

## Install

Download from the repo, install manually into Dokuwiki.

## Configure

1. Create an Incoming Webhook on discord: https://docs.gitlab.com/ee/user/project/integrations/discord_notifications.html
2. Enter the webhook into the fsdiscordnotifier configuration section in DokuWiki's Configuration Settings
3. If you wish to incldue the root namespace for notifications, then spacify a : (colon) in the namespaces field

## Credits
https://github.com/glensc for https://github.com/glensc/dokuwiki-plugin-slacknotifier, on which this is heavily based.
