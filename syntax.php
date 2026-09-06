<?php

/**
 * DokuWiki Plugin shorturl (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Frank Schiebel <frank@linuxmuster.net>
 */
class syntax_plugin_shorturl extends DokuWiki_Syntax_Plugin
{
    /** @inheritdoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritdoc */
    public function getPType()
    {
        return 'block';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 302;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\~\~SHORTURL\~\~', $mode, 'plugin_shorturl');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        return ['todo' => 'print'];
    }

    /** @inheritdoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        global $ID;

        if ($mode !== 'xhtml') {
            return false;
        }

        if (!empty($data['todo']) && $data['todo'] === 'print') {
            /** @var helper_plugin_shorturl $shorturl */
            $shorturl = plugin_load('helper', 'shorturl');
            if ($shorturl) {
                $shortID = $shorturl->autoGenerateShortUrl($ID);
                $renderer->doc .= '<a href="' . wl($shortID, '', true) . '" class="shortlinkinpage">'
                    . $this->getLang('shortlinktext') . "</a>\n";
            }
        }

        return true;
    }
}
