<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Frank Schiebel <frank@linuxmuster.net>
 */
class helper_plugin_shorturl extends DokuWiki_Plugin
{
    /** @var string */
    protected $configtocache = '';

    /** @var string */
    protected $savedir = '';

    /**
     * Constructor gets default preferences and language strings
     */
    public function __construct()
    {
        global $conf;

        $this->configtocache = $this->getConf('saveconftocachedir');

        if ($this->configtocache) {
            if ($conf['savedir'] !== './data') {
                $this->savedir = rtrim($conf['savedir'], '/') . '/cache';
            } else {
                $this->savedir = DOKU_INC . rtrim($conf['savedir'], '/') . '/cache';
            }
        } else {
            $this->savedir = __DIR__;
        }
    }

    /**
     * Introspection
     *
     * @return array
     */
    public function getMethods()
    {
        $result = [];
        $result[] = [
            'name'   => 'autoGenerateShortUrl',
            'desc'   => 'returns the short url if exists, otherwise create the short url',
            'return' => ['shortID' => 'string'],
        ];
        $result[] = [
            'name'   => 'shorturlPrintLink',
            'desc'   => 'returns a link to the short url if it exists, otherwise a link to create the short url',
            'return' => ['html' => 'string'],
        ];
        return $result;
    }

    /**
     * returns shortID for pageID
     * creates and saves forwarding to shortID if not
     *
     * @param string $pageID
     * @return string
     */
    public function autoGenerateShortUrl($pageID)
    {
        $redirects = confToHash($this->savedir . '/shorturl.conf');
        if (in_array($pageID, $redirects, true)) {
            $shortID = array_search($pageID, $redirects, true);
        } else {
            $shortID = $this->generateShortUrl($pageID);
        }
        return $shortID;
    }

    /**
     * generates short page id from current page id
     *
     * @author   Frank Schiebel <frank@linuxmuster.net>
     * @param    string $pageID
     * @return   string shortid
     * @url      http://www.snippetit.com/2009/04/php-short-url-algorithm-implementation/
     */
    protected function generateShortUrl($pageID)
    {
        $output = [];
        $base32 = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
            'y', 'z', '0', '1', '2', '3', '4', '5',
        ];

        $hex = md5($pageID);
        $hexLen = strlen($hex);
        $subHexLen = (int) ($hexLen / 8);

        for ($i = 0; $i < $subHexLen; $i++) {
            $subHex = substr($hex, $i * 8, 8);
            // PHP 8 safe: hexdec no longer needs 0x prefix
            $int = hexdec($subHex);
            $out = '';

            for ($j = 0; $j < 6; $j++) {
                $val = 0x0000001F & $int;
                $out .= $base32[$val];
                $int = $int >> 5;
            }

            $output[] = $out;
        }

        // save redirect to file
        $redirects = confToHash($this->savedir . '/shorturl.conf');
        // check for duplicates in database and select alternative shorty when needed
        $shorturl = $output[0];
        $count = count($output);
        for ($j = 0; $j < $count - 1; $j++) {
            if (!empty($redirects[$shorturl]) && $redirects[$shorturl] !== $pageID) {
                $shorturl = $output[$j + 1];
            } else {
                break;
            }
        }

        $redirects[$shorturl] = $pageID;
        $filecontents = '';
        foreach ($redirects as $short => $long) {
            $filecontents .= $short . '          ' . $long . "\n";
        }
        io_saveFile($this->savedir . '/shorturl.conf', $filecontents);

        return $shorturl;
    }

    /**
     * if a short id exists in db: get it
     *
     * @author   Frank Schiebel <frank@linuxmuster.net>
     * @param    string $pageID
     * @return   string regular id
     */
    public function shorturlPrintLink($pageID)
    {
        if (file_exists($this->savedir . '/shorturl.conf')) {
            $redirects = confToHash($this->savedir . '/shorturl.conf');
        } else {
            $redirects = [];
        }

        if (in_array($pageID, $redirects, true)) {
            $shortID = array_search($pageID, $redirects, true);
            $linktext = $this->getLang('shortlinktext');
            return '<a href="' . wl($shortID, '', true) . '"> ' . $linktext . '</a>';
        }

        $linktext = $this->getLang('generateshortlink');
        return '<a href="' . wl($pageID, ['generateShortURL' => 'yes'], true) . '"> ' . $linktext . '</a>';
    }
}
