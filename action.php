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

        $controller->register_hook(
            'TPL_CONTENT_DISPLAY',
            'BEFORE',
            $this,
            'handle_tpl_content_display'
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
     * Add the short url link to the page content.
     *
     * TPL_CONTENT_DISPLAY is triggered by tpl_content() which every
     * template calls inside its content area. This way the link shows up
     * inside the content panel of the current template (e.g. inside the
     * "panel panel-default" of bootstrap based templates) without any
     * manual edits in the template files.
     *
     * Depending on the "auto_display_position" option the link is
     * prepended (top: above the page title, floated to the right) or
     * appended (bottom: below the page content).
     *
     * @param Doku_Event $event
     */
    public function handle_tpl_content_display(Doku_Event $event)
    {
        global $ID, $ACT, $REV;

        if (!$this->getConf('auto_display')) {
            return;
        }

        // only on normal page views (no old revisions, exports, admin etc.)
        if ($ACT !== 'show' || $REV) {
            return;
        }

        if (!page_exists($ID)) {
            return;
        }

        if (auth_quickaclcheck($ID) < AUTH_READ) {
            return;
        }

        /** @var helper_plugin_shorturl $shorturl */
        $shorturl = plugin_load('helper', 'shorturl');
        if (!$shorturl) {
            return;
        }

        $position = $this->getConf('auto_display_position');
        $class = 'plugin-shorturl';
        if ($position === 'top') {
            $class .= ' plugin-shorturl--top';
        }

        $linkHtml = '<div class="' . $class . '">' . $shorturl->shorturlPrintLink($ID) . '</div>' . "\n";

        if ($position === 'top') {
            // put the link above the page title
            $event->data = $linkHtml . $event->data;
        } else {
            // put the link below the page content
            $event->data .= $linkHtml;
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
