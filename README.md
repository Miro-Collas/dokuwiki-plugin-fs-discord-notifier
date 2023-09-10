# DokuWiki Discord Notifier

A DokuWiki plugin that notifies a Discord channel room of wiki edits.

## Install

Download from the repo, install manually into Dokuwiki.

## Configure

1. Create an Incoming Webhook on discord: https://docs.gitlab.com/ee/user/project/integrations/discord_notifications.html
2. Enter the webhook into the fsdiscordnotifier configuration section in DokuWiki's Configuration Settings
3. If you wish to include the root namespace for notifications, then specify a : (colon) in the namespaces field

## Credits
- [zteeed](https://github.com/zteeed0 for [discord-notifier](https://github.com/zteeed/dokuwiki-discord-notifier), which is what our team initially used, and from which some code was borrowed.
- [glensc](https://github.com/glensc) for [slacknotifier](https://github.com/glensc/dokuwiki-plugin-slacknotifier), on which this is heavily based. (I had to mirror the repo, as I had already made a fork for the purpose of submitting a PR.)
- [PanteraPolnocy](https://github.com/PanteraPolnocy) for help on code which was over my head.
