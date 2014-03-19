<?php

namespace Adlpz\Minidown;

/**
 * Class Markdown
 * @package Adlpz\Minidown
 *
 * Ultra-tiny markdown converter. Accepts **bold**, *italic*, `code`, ```code-blocks```, ![images](), [links](), #headers and paragraphs.
 */
class Markdown
{
    public static function markdown($markdown)
    {
        $codes = [];
        $rules = [
            '/((?:`)+)(.*?)\1/s' => function ($m) use (&$codes) { $uid = uniqid('C0DE'); $codes[$uid] = $m[2]; return strlen($m[1]) > 1 ? "<pre><code>$uid</code></pre>" : "<code>$uid</code>"; },
            '/^(#+)\s*(.+)/m' => function($m) { $h = strlen($m[1]); return sprintf('<h%s>%s</h%s>', $h, $m[2], $h); },
            '/\!\[([^\]]+)\]\(([^\)]+)\)/' => '<img src="\2" title="\1">',
            '/\[([^\]]+)\]\(([^\)]+)\)/' => '<a href="\2">\1</a>',
            '/(\*\*|__)(.*?)\1/' => '<strong>\2</strong>',
            '/(\*|_)(.*?)\1/' => '<em>\2</em>',
        ];
        foreach ($rules as $regex => $sub) {
            $markdown = call_user_func((is_callable($sub) ? 'preg_replace_callback' : 'preg_replace'), $regex, $sub, $markdown);
        }
        $lines = array_filter(array_map('trim', explode("\n\n", $markdown)));
        $html = implode("\n", array_map(function($t){ return preg_match("/^\<(h|p|ul|li|blockquote|hr|br|pre).*/", $t) ? $t : "<p>$t</p>"; }, $lines));
        foreach ($codes as $uid => $code) {
            $html = str_replace($uid, htmlentities($code), $html);
        }
        return $html;
    }
}
