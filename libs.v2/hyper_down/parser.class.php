<?php

namespace hyper_down;

/**
 * Parser
 *
 * @copyright Copyright (c) 2012 SegmentFault Team. (http://segmentfault.com)
 * @author Joyqi <joyqi@segmentfault.com>
 * @license BSD License
 */
class parser
{
    /**
     * _whiteList
     *
     * @var string
     */
    public $_common_white_list = 'kbd|b|i|strong|em|sup|sub|br|code|del|a|hr|small';

    /**
     * _specialWhiteList
     *
     * @var mixed
     * @access private
     */
    public $_special_white_list = array(
        'table'  =>  'table|tbody|thead|tfoot|tr|td|th'
    );

    /**
     * _footnotes
     *
     * @var array
     */
    public $_footnotes;

    /**
     * _blocks
     *
     * @var array
     */
    private $_blocks;

    /**
     * _current
     *
     * @var string
     */
    private $_current;

    /**
     * _pos
     *
     * @var int
     */
    private $_pos;

    /**
     * _definitions
     *
     * @var array
     */
    public $_definitions;

    /**
     * @var array
     */
    private $_hooks = array();

    /**
     * @var array
     */
    private $_holders;

    /**
     * @var string
     */
    private $_uniqid;

    /**
     * @var int
     */
    private $_id;

    /**
     * @var bool
     */
    private $_html = false;

    /**
     * makeHtml
     *
     * @param mixed $text
     * @return string
     */
    public function make_html($text)
    {
        $this->_footnotes = array();
        $this->_definitions = array();
        $this->_holders = array();
        $this->_uniqid = md5(uniqid());
        $this->_id = 0;

        $text = $this->init_text($text);
        $html = $this->parse($text);
        $html = $this->make_footnotes($html);

        return $this->call('makeHtml', $html);
    }

    /**
     * @param $html
     */
    public function enable_html($html = true)
    {
        $this->_html = $html;
    }

    /**
     * @param $type
     * @param $callback
     */
    public function hook($type, $callback)
    {
        $this->_hooks[$type][] = $callback;
    }

    /**
     * @param $str
     * @return string
     */
    public function make_holder($str)
    {
        $key = "\r" . $this->_uniqid . $this->_id . "\r";
        $this->_id ++;
        $this->_holders[$key] = $str;

        return $key;
    }

    /**
     * @param $text
     * @return mixed
     */
    private function init_text($text)
    {
        $text = str_replace(array("\t", "\r"),  array('    ', ''),  $text);
        return $text;
    }

    /**
     * @param $html
     * @return string
     */
    private function make_footnotes($html)
    {
        if (count($this->_footnotes) > 0)
        {
            $html .= '<div class="footnotes"><hr><ol>';
            $index = 1;

            while ($val = array_shift($this->_footnotes))
            {
                if (is_string($val))
                {
                    $val .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                } else
                {
                    $val[count($val) - 1] .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                    $val = count($val) > 1 ? $this->parse(implode("\n", $val)) : $this->parse_inline($val[0]);
                }

                $html .= "<li id=\"fn-{$index}\">{$val}</li>";
                $index ++;
            }

            $html .= '</ol></div>';
        }

        return $html;
    }

    /**
     * parse
     *
     * @param string $text
     * @param bool $inline
     * @return string
     */
    private function parse($text, $inline = false)
    {
        $blocks = $this->parse_block($text, $lines);
        $html = '';

        foreach ($blocks as $block)
        {
            list ($type, $start, $end, $value) = $block;
            $extract = array_slice($lines, $start, $end - $start + 1);
            $method  = 'parse' . ucfirst($type);

            $extract = $this->call('before' . ucfirst($method), $extract, $value);
            $result  = $this->{$method}($extract, $value);
            $result  = $this->call('after' . ucfirst($method), $result, $value);

            $html   .= $result;
        }

        // inline mode for single normal block
        if ($inline && count($blocks) == 1 && $blocks[0][0] == 'normal')
        {
            // remove p tag
            $html = preg_replace("/^\s*<p>(.*)<\/p>\s*$/", "\\1", $html);
        }

        return $html;
    }

    /**
     * @param $text
     * @param $clearHolders
     * @return string
     */
    private function release_holder($text, $clearHolders = true)
    {
        $deep = 0;
        while (strpos($text, "\r") !== false && $deep < 10)
        {
            $text = str_replace(array_keys($this->_holders), array_values($this->_holders), $text);
            $deep ++;
        }

        if ($clearHolders)
        {
            $this->_holders = array();
        }

        return $text;
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     */
    public function call($type, $value)
    {
        if (empty($this->_hooks[$type]))
        {
            return $value;
        }

        $args = func_get_args();
        $args = array_slice($args, 1);

        foreach ($this->_hooks[$type] as $callback)
        {
            $value   = call_user_func_array($callback, $args);
            $args[0] = $value;
        }

        return $value;
    }

    /**
     * parseInline
     *
     * @param string $text
     * @param string $whiteList
     * @param bool $clearHolders
     * @param bool $enableAutoLink
     * @return string
     */
    public function parse_inline($text, $whiteList = '', $clearHolders = true, $enableAutoLink = true)
    {
        $self = $this;
        $text = $this->call('beforeParseInline', $text); 

        // code
        $text = preg_replace_callback(
            "/(^|[^\\\])(`+)(.+?)\\2/",
            function ($matches) use ($self)
            {
                return  $matches[1] . $self->make_holder(
                    '<code>' . htmlspecialchars($matches[3]) . '</code>'
                );
            },
            $text
        );

        // mathjax
        $text = preg_replace_callback(
            "/(^|[^\\\])(\\$+)(.+?)\\2/",
            function ($matches) use ($self)
            {
                return  $matches[1] . $self->make_holder(
                    $matches[2] . htmlspecialchars($matches[3]) . $matches[2]
                );
            },
            $text
        );

        // escape
        $text = preg_replace_callback(
            "/\\\(.)/u",
            function ($matches) use ($self)
            {
                $escaped = htmlspecialchars($matches[1]);
                $escaped = str_replace('$', '&dollar;', $escaped);
                return  $self->make_holder($escaped);
            },
            $text
        );

        // link
        $text = preg_replace_callback(
            "/<(https?:\/\/.+)>/i",
            function ($matches) use ($self)
            {
                $url = $self->clean_url($matches[1]);
                $link = $self->call('parseLink', $matches[1]);

                return $self->make_holder(
                    "<a href=\"{$url}\">{$link}</a>"
                );
            },
            $text
        );

        // encode unsafe tags
        $text = preg_replace_callback(
            "/<(\/?)([a-z0-9-]+)(\s+[^>]*)?>/i",
            function ($matches) use ($self, $whiteList)
            {
                if (false !== stripos(
                    '|' . $self->_common_white_list . '|' . $whiteList . '|', '|' . $matches[2] . '|'
                ))
                {
                    return $self->make_holder($matches[0]);
                } else
                {
                    return htmlspecialchars($matches[0]);
                }
            },
            $text
        );

        $text = str_replace(array('<', '>'),  array('&lt;', '&gt;'),  $text);

        // footnote
        $text = preg_replace_callback(
            "/\[\^((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
            function ($matches) use ($self)
            {
                $id = array_search($matches[1], $self->_footnotes);

                if (false === $id)
                {
                    $id = count($self->_footnotes) + 1;
                    $self->_footnotes[$id] = $self->parse_inline($matches[1], '', false);
                }

                return $self->make_holder(
                    "<sup id=\"fnref-{$id}\"><a href=\"#fn-{$id}\" class=\"footnote-ref\">{$id}</a></sup>"
                );
            },
            $text
        );

        // image
        $text = preg_replace_callback(
            "/!\[((?:[^\]]|\\\\\]|\\\\\[)*?)\]\(((?:[^\)]|\\\\\)|\\\\\()+?)\)/",
            function ($matches) use ($self)
            {
                $escaped = htmlspecialchars($self->escape_bracket($matches[1]));
                $url = $self->escape_bracket($matches[2]);
                $url = $self->clean_url($url);
                return $self->make_holder(
                    "<img src=\"{$url}\" alt=\"{$escaped}\" title=\"{$escaped}\">"
                );
            },
            $text
        );

        $text = preg_replace_callback(
            "/!\[((?:[^\]]|\\\\\]|\\\\\[)*?)\]\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
            function ($matches) use ($self)
            {
                $escaped = htmlspecialchars($self->escape_bracket($matches[1]));

                $result = isset( $self->_definitions[$matches[2]] ) ?
                    "<img src=\"{$self->_definitions[$matches[2]]}\" alt=\"{$escaped}\" title=\"{$escaped}\">"
                    : $escaped;

                return $self->make_holder($result);
            },
            $text
        );

        // link
        $text = preg_replace_callback(
            "/\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]\(((?:[^\)]|\\\\\)|\\\\\()+?)\)/",
            function ($matches) use ($self)
            {
                $escaped = $self->parse_inline(
                    $self->escape_bracket($matches[1]),  '',  false, false
                );
                $url = $self->escape_bracket($matches[2]);
                $url = $self->clean_url($url);
                return $self->make_holder("<a href=\"{$url}\">{$escaped}</a>");
            },
            $text
        );

        $text = preg_replace_callback(
            "/\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
            function ($matches) use ($self) {
                $escaped = $self->parse_inline(
                    $self->escape_bracket($matches[1]),  '',  false
                );
                $result = isset( $self->_definitions[$matches[2]] ) ?
                    "<a href=\"{$self->_definitions[$matches[2]]}\">{$escaped}</a>"
                    : $escaped;

                return $self->make_holder($result);
            },
            $text
        ); 

        // strong and em and some fuck
        $text = $this->parse_inline_callback($text);
        $text = preg_replace(
            "/<([_a-z0-9-\.\+]+@[^@]+\.[a-z]{2,})>/i",
            "<a href=\"mailto:\\1\">\\1</a>",
            $text
        );

        // autolink url
        if ($enableAutoLink) {
            $text = preg_replace_callback(
                "/(^|[^\"])((https?):[x80-xff_a-z0-9-\.\/%#!@\?\+=~\|\,&\(\)]+)($|[^\"])/i",
                function ($matches) use ($self) {
                    $link = $self->call('parseLink', $matches[2]);
                    return "{$matches[1]}<a href=\"{$matches[2]}\">{$link}</a>{$matches[4]}";
                },
                $text
            );
        }

        $text = $this->call('afterParseInlineBeforeRelease', $text);
        $text = $this->release_holder($text, $clearHolders);

        $text = $this->call('afterParseInline', $text);

        return $text;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function parse_inline_callback($text)
    {
        $self = $this;

        $text = preg_replace_callback(
            "/(\*{3})(.+?)\\1/",
            function ($matches) use ($self) {
                return  '<strong><em>' .
                    $self->parse_inline_callback($matches[2]) .
                    '</em></strong>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\*{2})(.+?)\\1/",
            function ($matches) use ($self) {
                return  '<strong>' .
                    $self->parse_inline_callback($matches[2]) .
                    '</strong>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\*)(.+?)\\1/",
            function ($matches) use ($self) {
                return  '<em>' .
                    $self->parse_inline_callback($matches[2]) .
                    '</em>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_{3})(.+?)\\2(\s+|$)/",
            function ($matches) use ($self) {
                return  $matches[1] . '<strong><em>' .
                    $self->parse_inline_callback($matches[3]) .
                    '</em></strong>' . $matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_{2})(.+?)\\2(\s+|$)/",
            function ($matches) use ($self) {
                return  $matches[1] . '<strong>' .
                    $self->parse_inline_callback($matches[3]) .
                    '</strong>' . $matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_)(.+?)\\2(\s+|$)/",
            function ($matches) use ($self) {
                return  $matches[1] . '<em>' .
                    $self->parse_inline_callback($matches[3]) .
                    '</em>' . $matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(~{2})(.+?)\\1/",
            function ($matches) use ($self) {
                return  '<del>' .
                    $self->parse_inline_callback($matches[2]) .
                    '</del>';
            },
            $text
        );

        return $text;
    }

    /**
     * parseBlock
     *
     * @param string $text
     * @param array $lines
     * @return array
     */
    private function parse_block($text, &$lines)
    {
        $lines = explode("\n", $text);
        $this->_blocks = array();
        $this->_current = 'normal';
        $this->_pos = -1;
        $special = implode("|", array_keys($this->_special_white_list));
        $emptyCount = 0;

        // analyze by line
        foreach ($lines as $key => $line) {
            $block = $this->get_block();

            // code block is special
            if (preg_match("/^(\s*)(~{3,}|`{3,})([^`~]*)$/i", $line, $matches)) {
                if ($this->is_block('code')) {
                    $isAfterList = $block[3][2];

                    if ($isAfterList) {
                        $this->combine_block()
                            ->set_block($key);
                    } else {
                        $this->set_block($key)
                            ->end_block();
                    }
                } else {
                    $isAfterList = false;

                    if ($this->is_block('list')) {
                        $space = $block[3];

                        $isAfterList = ($space > 0 && strlen($matches[1]) >= $space)
                            || strlen($matches[1]) > $space;
                    }

                    $this->start_block('code', $key, array(
                        $matches[1],  $matches[3],  $isAfterList
                    ));
                }

                continue;
            } else if ($this->is_block('code')) {
                $this->set_block($key);
                continue;
            }

            // super html mode
            if ($this->_html) {
                if (preg_match("/^(\s*)!!!(\s*)$/", $line, $matches)) {
                    if ($this->is_block('shtml')) {
                        $this->set_block($key)->end_block();
                    } else {
                        $this->start_block('shtml', $key);
                    }

                    continue;
                } else if ($this->is_block('shtml')) {
                    $this->set_block($key);
                    continue;
                }
            }

            // mathjax mode
            if (preg_match("/^(\s*)\\$\\$(\s*)$/", $line, $matches)) {
                if ($this->is_block('math')) {
                    $this->set_block($key)->end_block();
                } else {
                    $this->start_block('math', $key);
                }

                continue;
            } else if ($this->is_block('math')) {
                $this->set_block($key);
                continue;
            }

            // html block is special too
            if (preg_match("/^\s*<({$special})(\s+[^>]*)?>/i", $line, $matches)) {
                $tag = strtolower($matches[1]);
                if (!$this->is_block('html', $tag) && !$this->is_block('pre')) {
                    $this->start_block('html', $key, $tag);
                }

                continue;
            } else if (preg_match("/<\/({$special})>\s*$/i", $line, $matches)) {
                $tag = strtolower($matches[1]);

                if ($this->is_block('html', $tag)) {
                    $this->set_block($key)
                        ->end_block();
                }

                continue;
            } else if ($this->is_block('html')) {
                $this->set_block($key);
                continue;
            }

            switch (true) {
                // pre block
                case preg_match("/^ {4}/", $line):
                    $emptyCount = 0;

                    if ($this->is_block('pre') || $this->is_block('list')) {
                        $this->set_block($key);
                    } else if ($this->is_block('normal')) {
                        $this->start_block('pre', $key);
                    }
                    break;

                // list
                case preg_match("/^(\s*)((?:[0-9a-z]+\.)|\-|\+|\*)\s+/", $line, $matches):
                    $space = strlen($matches[1]);
                    $emptyCount = 0;

                    // opened
                    if ($this->is_block('list')) {
                        $this->set_block($key, $space);
                    } else {
                        $this->start_block('list', $key, $space);
                    }
                    break; 

                // footnote
                case preg_match("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", $line, $matches):
                    $space = strlen($matches[0]) - 1;
                    $this->start_block('footnote', $key, array(
                        $space,  $matches[1]
                    ));
                    break;

                // definition
                case preg_match("/^\s*\[((?:[^\]]|\\]|\\[)+?)\]:\s*(.+)$/", $line, $matches):
                    $this->_definitions[$matches[1]] = $this->clean_url($matches[2]);
                    $this->start_block('definition', $key)
                        ->end_block();
                    break;

                // block quote
                case preg_match("/^\s*>/", $line):
                    if ($this->is_block('quote')) {
                        $this->set_block($key);
                    } else {
                        $this->start_block('quote', $key);
                    }
                    break;

                // table
                case preg_match("/^((?:(?:(?:[ :]*\-[ :]*)+(?:\||\+))|(?:(?:\||\+)(?:[ :]*\-[ :]*)+)|(?:(?:[ :]*\-[ :]*)+(?:\||\+)(?:[ :]*\-[ :]*)+))+)$/", $line, $matches):
                    if ($this->is_block('table')) {
                        $block[3][0][] = $block[3][2];
                        $block[3][2] ++;
                        $this->set_block($key, $block[3]);
                    } else { 
                        $head = 0;

                        if (empty($block) ||
                            $block[0] != 'normal' ||
                            preg_match("/^\s*$/", $lines[$block[2]])) {
                            $this->start_block('table', $key);
                        } else {
                            $head = 1;
                            $this->back_block(1, 'table');
                        }

                        if ($matches[1][0] == '|') {
                            $matches[1] = substr($matches[1], 1);

                            if ($matches[1][strlen($matches[1]) - 1] == '|') {
                                $matches[1] = substr($matches[1], 0, -1);
                            }
                        }

                        $rows = preg_split("/(\+|\|)/", $matches[1]);
                        $aligns = array();
                        foreach ($rows as $row) {
                            $align = 'none';

                            if (preg_match("/^\s*(:?)\-+(:?)\s*$/", $row, $matches)) {
                                if (!empty($matches[1]) && !empty($matches[2])) {
                                    $align = 'center';
                                } else if (!empty($matches[1])) {
                                    $align = 'left';
                                } else if (!empty($matches[2])) {
                                    $align = 'right';
                                }
                            }

                            $aligns[] = $align;
                        }

                        $this->set_block($key, array(array($head), $aligns, $head + 1));
                    }
                    break;

                // single heading
                case preg_match("/^(#+)(.*)$/", $line, $matches):
                    $num = min(strlen($matches[1]), 6);
                    $this->start_block('sh', $key, $num)
                        ->end_block();
                    break;

                // multi heading
                case preg_match("/^\s*((=|-){2,})\s*$/", $line, $matches)
                    && ($block && $block[0] == "normal" && !preg_match("/^\s*$/", $lines[$block[2]])):    // check if last line isn't empty
                    if ($this->is_block('normal')) {
                        $this->back_block(1, 'mh', $matches[1][0] == '=' ? 1 : 2)
                            ->set_block($key)
                            ->end_block();
                    } else {
                        $this->start_block('normal', $key);
                    }
                    break;

                // hr
                case preg_match("/^[-\*]{3,}\s*$/", $line):
                    $this->start_block('hr', $key)
                        ->end_block();
                    break;

                // normal
                default:
                    if ($this->is_block('list')) {
                        if (preg_match("/^(\s*)/", $line)) { // empty line
                            if ($emptyCount > 0) {
                                $this->start_block('normal', $key);
                            } else {
                                $this->set_block($key);
                            }

                            $emptyCount ++;
                        } else if ($emptyCount == 0) {
                            $this->set_block($key);
                        } else {
                            $this->start_block('normal', $key);
                        }
                    } else if ($this->is_block('footnote')) {
                        preg_match("/^(\s*)/", $line, $matches);
                        if (strlen($matches[1]) >= $block[3][0]) {
                            $this->set_block($key);
                        } else {
                            $this->start_block('normal', $key);
                        }
                    } else if ($this->is_block('table')) {
                        if (false !== strpos($line, '|')) {
                            $block[3][2] ++;
                            $this->set_block($key, $block[3]);
                        } else {
                            $this->start_block('normal', $key);
                        }
                    } else if ($this->is_block('pre')) {
                        if (preg_match("/^\s*$/", $line)) {
                            if ($emptyCount > 0) {
                                $this->start_block('normal', $key);
                            } else {
                                $this->set_block($key);
                            }

                            $emptyCount ++;
                        } else {
                            $this->start_block('normal', $key);
                        }
                    } else if ($this->is_block('quote')) {
                        if (preg_match("/^(\s*)/", $line)) { // empty line
                            if ($emptyCount > 0) {
                                $this->start_block('normal', $key);
                            } else {
                                $this->set_block($key);
                            }

                            $emptyCount ++;
                        } else if ($emptyCount == 0) {
                            $this->set_block($key);
                        } else {
                            $this->start_block('normal', $key);
                        }
                    } else {
                        if (empty($block) || $block[0] != 'normal') {
                            $this->start_block('normal', $key);
                        } else {
                            $this->set_block($key);
                        }
                    }
                    break;
            }
        }

        return $this->optimize_blocks($this->_blocks, $lines);
    }

    /**
     * @param array $blocks
     * @param array $lines
     * @return array
     */
    private function optimize_blocks(array $blocks, array $lines)
    {
        $blocks = $this->call('beforeOptimizeBlocks', $blocks, $lines);

        $key = 0;
        while (isset($blocks[$key])) {
            $moved = false;

            $block = &$blocks[$key];
            $prevBlock = isset($blocks[$key - 1]) ? $blocks[$key - 1] : NULL;
            $nextBlock = isset($blocks[$key + 1]) ? $blocks[$key + 1] : NULL;

            list ($type, $from, $to) = $block;

            if ('pre' == $type) {
                $isEmpty = array_reduce($lines, function ($result, $line) {
                    return preg_match("/^\s*$/", $line) && $result;
                }, true);

                if ($isEmpty) {
                    $block[0] = $type = 'normal';
                }
            }

            if ('normal' == $type) {
                // combine two blocks
                $types = array('list', 'quote');

                if ($from == $to && preg_match("/^\s*$/", $lines[$from])
                    && !empty($prevBlock) && !empty($nextBlock)) {
                    if ($prevBlock[0] == $nextBlock[0] && in_array($prevBlock[0], $types)) {
                        // combine 3 blocks
                        $blocks[$key - 1] = array(
                            $prevBlock[0],  $prevBlock[1],  $nextBlock[2],  NULL
                        );
                        array_splice($blocks, $key, 2);

                        // do not move
                        $moved = true;
                    }
                }
            }

            if (!$moved) {
                $key ++;
            }
        }

        return $this->call('afterOptimizeBlocks', $blocks, $lines);
    }

    /**
     * parseCode
     *
     * @param array $lines
     * @param array $parts
     * @return string
     */
    private function parse_code(array $lines, array $parts)
    {
        list ($blank, $lang) = $parts;
        $lang = trim($lang);
        $count = strlen($blank);

        if (!preg_match("/^[_a-z0-9-\+\#\:\.]+$/i", $lang)) {
            $lang = NULL;
        } else {
            $parts = explode(':', $lang);
            if (count($parts) > 1) {
                list ($lang, $rel) = $parts;
                $lang = trim($lang);
                $rel = trim($rel);
            }
        }

        $lines = array_map(function ($line) use ($count) {
            return preg_replace("/^[ ]{{$count}}/", '', $line);
        }, array_slice($lines, 1, -1));
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str) ? '' :
            '<pre><code' . (!empty($lang) ? " class=\"{$lang}\"" : '')
            . (!empty($rel) ? " rel=\"{$rel}\"" : '') . '>'
            . htmlspecialchars($str) . '</code></pre>';
    }

    /**
     * parsePre
     *
     * @param array $lines
     * @return string
     */
    private function parse_pre(array $lines)
    {
        foreach ($lines as &$line) {
            $line = htmlspecialchars(substr($line, 4));
        }
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str) ? '' : '<pre><code>' . $str . '</code></pre>';
    }

    /**
     * parseShtml
     *
     * @param array $lines
     * @return string
     */
    private function parse_shtml(array $lines)
    {
        return trim(implode("\n", array_slice($lines, 1, -1)));
    }

    /**
     * parseMath
     *
     * @param array $lines
     * @return string
     */
    private function parse_math(array $lines)
    {
        return '<p>' . htmlspecialchars(implode("\n", $lines)) . '</p>';
    }

    /**
     * parseSh
     *
     * @param array $lines
     * @param int $num
     * @return string
     */
    private function parse_sh(array $lines, $num)
    {
        $line = $this->parse_inline(trim($lines[0], '# '));
        return preg_match("/^\s*$/", $line) ? '' : "<h{$num}>{$line}</h{$num}>";
    }

    /**
     * parseMh
     *
     * @param array $lines
     * @param int $num
     * @return string
     */
    private function parse_mh(array $lines, $num)
    {
        return $this->parse_sh($lines, $num);
    }

    /**
     * parseQuote
     *
     * @param array $lines
     * @return string
     */
    private function parse_quote(array $lines)
    {
        foreach ($lines as &$line) {
            $line = preg_replace("/^\s*> ?/", '', $line);
        }
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str) ? '' : '<blockquote>' . $this->parse($str) . '</blockquote>';
    }

    /**
     * parseList
     *
     * @param array $lines
     * @return string
     */
    private function parse_list(array $lines)
    {
        $html = '';
        $minSpace = 99999;
        $rows = array();

        // count levels
        foreach ($lines as $key => $line) {
            if (preg_match("/^(\s*)((?:[0-9a-z]+\.?)|\-|\+|\*)(\s+)(.*)$/", $line, $matches)) {
                $space = strlen($matches[1]);
                $type = false !== strpos('+-*', $matches[2]) ? 'ul' : 'ol';
                $minSpace = min($space, $minSpace);

                $rows[] = array($space, $type, $line, $matches[4]);
            } else {
                $rows[] = $line;
            }
        }

        $found = false;
        $secondMinSpace = 99999;
        foreach ($rows as $row) {
            if (is_array($row) && $row[0] != $minSpace) {
                $secondMinSpace = min($secondMinSpace, $row[0]);
                $found = true;
            }
        }
        $secondMinSpace = $found ? $secondMinSpace : $minSpace;

        $lastType = '';
        $leftLines = array();

        foreach ($rows as $row) {
            if (is_array($row)) {
                list ($space, $type, $line, $text) = $row;

                if ($space != $minSpace) {
                    $leftLines[] = preg_replace("/^\s{" . $secondMinSpace . "}/", '', $line);
                } else {
                    if (!empty($leftLines)) {
                        $html .= "<li>" . $this->parse(implode("\n", $leftLines), true) . "</li>";
                    }

                    if ($lastType != $type) {
                        if (!empty($lastType)) {
                            $html .= "</{$lastType}>";
                        }

                        $html .= "<{$type}>";
                    }

                    $leftLines = array($text);
                    $lastType = $type;
                }
            } else {
                $leftLines[] = preg_replace("/^\s{" . $secondMinSpace . "}/", '', $row);
            }
        }

        if (!empty($leftLines)) {
            $html .= "<li>" . $this->parse(implode("\n", $leftLines), true) . "</li></{$lastType}>";
        }

        return $html;
    }

    /**
     * @param array $lines
     * @param array $value
     * @return string
     */
    private function parse_table(array $lines, array $value)
    {
        list ($ignores, $aligns) = $value;
        $head = count($ignores) > 0 && array_sum($ignores) > 0;

        $html = '<table>';
        $body = $head ? NULL : true;
        $output = false;

        foreach ($lines as $key => $line) {
            if (in_array($key, $ignores)) {
                if ($head && $output) {
                    $head = false;
                    $body = true;
                }
                continue;
            }

            $line = trim($line);
            $output = true;

            if ($line[0] == '|') {
                $line = substr($line, 1);

                if ($line[strlen($line) - 1] == '|') {
                    $line = substr($line, 0, -1);
                }
            }


            $rows = array_map(function ($row) {
                if (preg_match("/^\s+$/", $row)) {
                    return ' ';
                } else {
                    return trim($row);
                }
            }, explode('|', $line));
            $columns = array();
            $last = -1;

            foreach ($rows as $row) {
                if (strlen($row) > 0) {
                    $last ++;
                    $columns[$last] = array(
                        isset($columns[$last]) ? $columns[$last][0] + 1 : 1,  $row
                    );
                } else if (isset($columns[$last])) {
                    $columns[$last][0] ++;
                } else {
                    $columns[0] = array(1, $row);
                }
            }

            if ($head) {
                $html .= '<thead>';
            } else if ($body) {
                $html .= '<tbody>';
            }

            $html .= '<tr>';

            foreach ($columns as $key => $column) {
                list ($num, $text) = $column;
                $tag = $head ? 'th' : 'td';

                $html .= "<{$tag}";
                if ($num > 1) {
                    $html .= " colspan=\"{$num}\"";
                }

                if (isset($aligns[$key]) && $aligns[$key] != 'none') {
                    $html .= " align=\"{$aligns[$key]}\"";
                }

                $html .= '>' . $this->parse_inline($text) . "</{$tag}>";
            }

            $html .= '</tr>';

            if ($head) {
                $html .= '</thead>';
            } else if ($body) {
                $body = false;
            }
        }

        if ($body !== NULL) {
            $html .= '</tbody>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * parseHr
     *
     * @return string
     */
    private function parse_hr()
    {
        return '<hr>';
    }

    /**
     * parseNormal
     *
     * @param array $lines
     * @return string
     */
    private function parse_normal(array $lines)
    {
        foreach ($lines as &$line) {
            $line = $this->parse_inline($line);
        }

        $str = trim(implode("\n", $lines));
        $str = preg_replace("/(\n\s*){2,}/", "</p><p>", $str);
        $str = preg_replace("/\n/", "<br>", $str);

        return preg_match("/^\s*$/", $str) ? '' : "<p>{$str}</p>";
    }

    /**
     * parseFootnote
     *
     * @param array $lines
     * @param array $value
     * @return string
     */
    private function parse_footnote(array $lines, array $value)
    {
        list($space, $note) = $value;
        $index = array_search($note, $this->_footnotes);

        if (false !== $index) {
            $lines[0] = preg_replace("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", '', $lines[0]);
            $this->_footnotes[$index] = $lines;
        }

        return '';
    }

    /**
     * parseDefine
     *
     * @return string
     */
    private function parse_definition()
    {
        return '';
    }

    /**
     * parseHtml
     *
     * @param array $lines
     * @param string $type
     * @return string
     */
    private function parse_html(array $lines, $type)
    {
        foreach ($lines as &$line) {
            $line = $this->parse_inline($line,
                isset($this->_special_white_list[$type]) ? $this->_special_white_list[$type] : '');
        }

        return implode("\n", $lines);
    }

    /**
     * @param $url
     * @return string
     */
    public function clean_url($url)
    {
        if (preg_match("/^\s*((http|https|ftp|mailto):[x80-xff_a-z0-9-\.\/%#!@\?\+=~\|\,&\(\)]+)/i", $url, $matches)) {
            return $matches[1];
        } else if (preg_match("/^\s*([x80-xff_a-z0-9-\.\/%#!@\?\+=~\|\,&]+)/i", $url, $matches)) {
            return $matches[1];
        } else {
            return '#';
        }
    }

    /**
     * @param $str
     * @return mixed
     */
    public function escape_bracket($str)
    {
        return str_replace(
            array('\[', '\]', '\(', '\)'),  array('[', ']', '(', ')'),  $str
        );
    }

    /**
     * startBlock
     *
     * @param mixed $type
     * @param mixed $start
     * @param mixed $value
     * @return $this
     */
    private function start_block($type, $start, $value = NULL)
    {
        $this->_pos ++;
        $this->_current = $type;

        $this->_blocks[$this->_pos] = array($type, $start, $start, $value);

        return $this;
    }

    /**
     * endBlock
     *
     * @return $this
     */
    private function end_block()
    {
        $this->_current = 'normal';
        return $this;
    }

    /**
     * isBlock
     *
     * @param mixed $type
     * @param mixed $value
     * @return bool
     */
    private function is_block($type, $value = NULL)
    {
        return $this->_current == $type
            && (NULL === $value ? true : $this->_blocks[$this->_pos][3] == $value);
    }

    /**
     * getBlock
     *
     * @return array
     */
    private function get_block()
    {
        return isset($this->_blocks[$this->_pos]) ? $this->_blocks[$this->_pos] : NULL;
    }

    /**
     * setBlock
     *
     * @param mixed $to
     * @param mixed $value
     * @return $this
     */
    private function set_block($to = NULL, $value = NULL)
    {
        if (NULL !== $to) {
            $this->_blocks[$this->_pos][2] = $to;
        }

        if (NULL !== $value) {
            $this->_blocks[$this->_pos][3] = $value;
        }

        return $this;
    }

    /**
     * backBlock
     *
     * @param mixed $step
     * @param mixed $type
     * @param mixed $value
     * @return $this
     */
    private function back_block($step, $type, $value = NULL)
    {
        if ($this->_pos < 0) {
            return $this->start_block($type, 0, $value);
        }

        $last = $this->_blocks[$this->_pos][2];
        $this->_blocks[$this->_pos][2] = $last - $step;

        if ($this->_blocks[$this->_pos][1] <= $this->_blocks[$this->_pos][2]) {
            $this->_pos ++;
        }

        $this->_current = $type;
        $this->_blocks[$this->_pos] = array(
            $type,  $last - $step + 1,  $last,  $value
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function combine_block()
    {
        if ($this->_pos < 1) {
            return $this;
        }

        $prev = $this->_blocks[$this->_pos - 1];
        $current = $this->_blocks[$this->_pos];

        $prev[2] = $current[2];
        $this->_blocks[$this->_pos - 1] = $prev;
        $this->_current = $prev[0];
        unset($this->_blocks[$this->_pos]);
        $this->_pos --;

        return $this;
    }
}
