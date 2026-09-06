<?php

/**
 * ShortURL Plugin - Admin Component
 * based on redirect plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Frank Schiebel <frank@linuxmuster.net>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_shorturl extends DokuWiki_Admin_Plugin
{
    /**
     * Access for managers allowed
     */
    public function forAdminOnly()
    {
        return false;
    }

    /**
     * return sort order for position in admin menu
     */
    public function getMenuSort()
    {
        return 140;
    }

    /**
     * return prompt for admin menu
     */
    public function getMenuText($language)
    {
        return $this->getLang('name');
    }

    /**
     * handle user request
     */
    public function handle()
    {
        global $INPUT;

        $redirdata = $INPUT->post->str('redirdata');
        if ($redirdata !== '') {
            if (io_saveFile($this->getsavedir() . '/shorturl.conf', $redirdata)) {
                msg($this->getLang('saved'), 1);
            }
        }
    }

    /**
     * output appropriate html
     */
    public function html()
    {
        global $lang;

        echo $this->locale_xhtml('intro');
        echo '<form action="" method="post">';
        echo '<input type="hidden" name="do" value="admin" />';
        echo '<input type="hidden" name="page" value="shorturl" />';
        echo '<textarea class="edit" rows="15" cols="80" style="height: 300px" name="redirdata">';
        echo formtext(io_readFile($this->getsavedir() . '/shorturl.conf'));
        echo '</textarea><br />';
        echo '<input type="submit" value="' . $lang['btn_save'] . '" class="button" />';
        echo '</form>';
    }

    /**
     * get savedir
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
