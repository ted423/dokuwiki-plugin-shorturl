<?php

/**
 * ShortURL Plugin
 * based on redirect plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Frank Schiebel <frank@linuxmuster.net>
 */
class action_plugin_shorturl extends DokuWiki_Action_Plugin
{
    /**
     * register the eventhandlers
     * @inheritdoc
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook(
            'DOKUWIKI_STARTED',
            'AFTER',
            $this,
            'handle_start'
        );
    }

    /**
     * handle event
     *
     * @param Doku_Event $event
     */
    public function handle_start(Doku_Event $event)
    {
        global $ID;
        global $ACT;
        global $INPUT;

        if ($ACT !== 'show') {
            return;
        }

        $redirects = confToHash($this->getsavedir() . '/shorturl.conf');
        if (isset($redirects[$ID])) {
            if (preg_match('/^https?:\/\//', $redirects[$ID])) {
                send_redirect($redirects[$ID]);
            } else {
                if ($this->getConf('showmsg')) {
                    msg(sprintf($this->getLang('redirected'), hsc($ID)));
                }
                send_redirect(wl($redirects[$ID], '', true, '&'));
            }
            // send_redirect already exits, but keep for safety
            exit;
        }

        if ($INPUT->get->str('generateShortURL') !== '' && auth_quickaclcheck($ID) >= AUTH_READ) {
            /** @var helper_plugin_shorturl $shorturl */
            $shorturl = plugin_load('helper', 'shorturl');
            if ($shorturl) {
                $shorturl->autoGenerateShortUrl($ID);
            }
        }
    }

    /**
     * get savedir
     *
     * @return string
     */
    protected function getsavedir()
    {
        global $conf;

        if ($this->getConf('saveconftocachedir')) {
            return rtrim($conf['savedir'], '/') . '/cache';
        }

        return __DIR__;
    }
}
