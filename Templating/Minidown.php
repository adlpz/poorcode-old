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
        $rules = [
            '/^(#+)\s*(.+)/m' => function($m) { $h = strlen($m[1]); return sprintf('<h%s>%s</h%s>', $h, $m[2], $h); },
            '/\!\[([^\]]+)\]\(([^\)]+)\)/' => '<img src="\2" title="\1">',
            '/\[([^\]]+)\]\(([^\)]+)\)/' => '<a href="\2">\1</a>',
            '/(\*\*|__)(.*?)\1/' => '<strong>\2</strong>',
            '/(\*|_)(.*?)\1/' => '<em>\2</em>',
            '/((?:`)+)(.*?)\1/s' => function ($m) { $s = str_replace("\n", "\0", '<code>'.$m[2].'</code>'); return strlen($m[1]) > 1 ? "<pre>$s</pre>" : $s; },
        ];
        foreach ($rules as $regex => $sub) {
            $markdown = call_user_func((is_callable($sub) ? 'preg_replace_callback' : 'preg_replace'), $regex, $sub, $markdown);
        }
        $lines = array_filter(array_map('trim', explode("\n\n", $markdown)));
        $html = implode("\n", array_map(function($t){ return preg_match("/^\<(h|p|ul|li|blockquote|hr|br|pre).*/", $t) ? $t : "<p>$t</p>"; }, $lines));
        return str_replace("\0", "\n", $html);
    }
}
