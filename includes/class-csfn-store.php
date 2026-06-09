<?php

/**
 * Per-request collector of footnotes.
 *
 * [fn] shortcodes add items here during the_content; the renderer reads them
 * back to build the "Sources" list. Links are de-duplicated by URL (the same
 * URL cited twice shares one number; notes are kept distinct.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Store
{
    /** @var CSFN_Store|null */
    private static $instance = null;

    /** @var array[] List of footnote items in order of first appearance. */
    private $items = [];

    /** @var array<string,int> Map of URL => item number, for de-duplication. */
    private $url_map = [];

    /** @var bool Whether [fn_sources] was used for manual placement. */
    private $manual_used = false;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add a footnote and return its item array (with assigned number).
     *
     * @param array $args { url, title, note }
     * @return array|null Null when there is nothing to add.
     */
    public function add($args)
    {
        $url   = isset($args['url']) ? trim($args['url']) : '';
        $title = isset($args['title']) ? trim($args['title']) : '';
        $note  = isset($args['note']) ? trim($args['note']) : '';

        // De-duplicate link sources by URL.
        if ('' !== $url && isset($this->url_map[$url])) {
            return $this->items[$this->url_map[$url] - 1];
        }

        if ('' !== $url) {
            $domain = CSFN_Favicon::domain($url);
            $item   = [
                'n'       => count($this->items) + 1,
                'type'    => 'link',
                'url'     => $url,
                'title'   => '' !== $title ? $title : $domain,
                'domain'  => $domain,
                'note'    => $note,
                'favicon' => CSFN_Favicon::url($url),
            ];
            $this->url_map[$url] = $item['n'];
        } elseif ('' !== $note) {
            $item = [
                'n'       => count($this->items) + 1,
                'type'    => 'note',
                'url'     => '',
                'title'   => $title,
                'domain'  => '',
                'note'    => $note,
                'favicon' => '',
            ];
        } else {
            return null;
        }

        $this->items[] = $item;

        return $item;
    }

    public function all()
    {
        return $this->items;
    }

    public function has_items()
    {
        return !empty($this->items);
    }

    public function mark_manual()
    {
        $this->manual_used = true;
    }

    public function used_manual()
    {
        return $this->manual_used;
    }

    public function reset()
    {
        $this->items       = [];
        $this->url_map     = [];
        $this->manual_used = false;
    }
}
