<?php

namespace Poppy\System\Classes;

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use Poppy\Framework\Exceptions\ApplicationException;

/**
 * Copyright (c) 2013, 俊杰Jerry
 * All rights reserved.
 *
 * @description: html解析器
 * @author     : 俊杰Jerry<bupt1987@gmail.com>
 * @date       : 2013-6-10
 */
class HtmlParser
{
    /**
     * @var DOMNode 接单
     */
    public $node;

    /**
     * @var array
     */
    private $_lFind = [];

    /**
     * @param DOMNode|string $node
     * @throws Exception
     */
    public function __construct($node = null)
    {
        if ($node !== null) {
            if ($node instanceof DOMNode) {
                $this->node = $node;
            }
            else {
                $dom                      = new DOMDocument();
                $dom->preserveWhiteSpace  = false;
                $dom->strictErrorChecking = false;
                if (@$dom->loadHTML($node)) {
                    $this->node = $dom;
                }
                else {
                    throw new Exception('load html error');
                }
            }
        }
    }

    /**
     * 初始化的时候可以不用传入html，后面可以多次使用
     * @param null $node 节点
     * @throws Exception
     */
    public function load($node = null)
    {
        if ($node instanceof DOMNode) {
            $this->node = $node;
        }
        else {
            $dom                      = new DOMDocument();
            $dom->preserveWhiteSpace  = false;
            $dom->strictErrorChecking = false;
            if (@$dom->loadHTML($node)) {
                $this->node = $dom;
            }
            else {
                throw new ApplicationException('load html error');
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'outertext':
                return $this->outerHtml();
            case 'innertext':
                return $this->innerHtml();
            case 'plaintext':
                return $this->getPlainText();
            case 'href':
                return $this->getAttr('href');
            case 'src':
                return $this->getAttr('src');
            default:
                return null;
        }
    }

    /**
     * 深度优先查询
     *
     * @param string $selector 选择器
     * @param number $idx      找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
     * @return self|self[]|bool
     * @throws Exception
     */
    public function find($selector, $idx = null)
    {
        if (empty($this->node->childNodes)) {
            return false;
        }
        $selectors = $this->parseSelector($selector);
        if (($count = count($selectors)) === 0) {
            return false;
        }
        for ($c = 0; $c < $count; $c++) {
            if (($level = count($selectors [$c])) === 0) {
                return false;
            }
            $this->search($this->node, $idx, $selectors [$c], $level);
        }
        $found        = $this->_lFind;
        $this->_lFind = [];
        if ($idx !== null) {
            if ($idx < 0) {
                $idx = count($found) + $idx;
            }
            if (isset($found[$idx])) {
                return $found[$idx];
            }

            return false;
        }

        return $found;
    }

    /**
     * 返回文本信息
     *
     * @return string
     */
    public function getPlainText(): string
    {
        return $this->text($this->node);
    }

    /**
     * 获取innerHtml
     * @return string
     */
    public function innerHtml()
    {
        $innerHTML = '';
        $children  = $this->node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $this->node->ownerDocument->saveHTML($child) ?: '';
        }

        return $innerHTML;
    }

    /**
     * 获取outerHtml
     * @return string|bool
     */
    public function outerHtml()
    {
        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($this->node, true));

        return $doc->saveHTML($doc);
    }

    /**
     * 获取html的元属值
     *
     * @param string $name 属性
     * @return string|null
     */
    public function getAttr($name)
    {
        $oAttr = $this->node->attributes->getNamedItem($name);
        if (isset($oAttr)) {
            return $oAttr->nodeValue;
        }

        return null;
    }

    /**
     * 匹配
     *
     * @param string $exp     表达式
     * @param string $pattern 规则
     * @param string $value   值
     * @return bool|number
     */
    private function match($exp, $pattern, $value)
    {
        $pattern = strtolower($pattern);
        $value   = strtolower($value);
        switch ($exp) {
            case '=':
                return $value === $pattern;
            case '!=':
                return $value !== $pattern;
            case '^=':
                return preg_match('/^' . preg_quote($pattern, '/') . '/', $value);
            case '$=':
                return preg_match('/' . preg_quote($pattern, '/') . '$/', $value);
            case '*=':
                if ($pattern [0] == '/') {
                    return preg_match($pattern, $value);
                }

                return preg_match('/' . $pattern . '/i', $value);
        }

        return false;
    }

    /**
     * 分析查询语句
     *
     * @param string $selector_string 选择器
     * @return array
     */
    private function parseSelector($selector_string)
    {
        $pattern = '/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)["\']?(.*?)["\']?)?\])?([\/, ]+)/is';
        preg_match_all($pattern, trim($selector_string) . ' ', $matches, PREG_SET_ORDER);
        $selectors = [];
        $result    = [];
        foreach ($matches as $m) {
            $m [0] = trim($m [0]);
            if ($m [0] === '' || $m [0] === '/' || $m [0] === '//')
                continue;
            if ($m [1] === 'tbody')
                continue;
            [$tag, $key, $val, $exp, $no_key] = [$m [1], null, null, '=', false];
            if (!empty($m [2])) {
                $key = 'id';
                $val = $m [2];
            }
            if (!empty($m [3])) {
                $key = 'class';
                $val = $m [3];
            }
            if (!empty($m [4])) {
                $key = $m [4];
            }
            if (!empty($m [5])) {
                $exp = $m [5];
            }
            if (!empty($m [6])) {
                $val = $m [6];
            }
            // convert to lowercase
            $tag = strtolower($tag);
            $key = strtolower($key);
            // elements that do NOT have the specified attribute
            if (isset($key [0]) && $key [0] === '!') {
                $key    = substr($key, 1);
                $no_key = true;
            }
            $result [] = [$tag, $key, $val, $exp, $no_key];
            if (trim($m [7]) === ',') {
                $selectors [] = $result;
                $result       = [];
            }
        }
        if (count($result) > 0) {
            $selectors [] = $result;
        }

        return $selectors;
    }

    /**
     * 深度查询
     *
     * @param DOMNode $search       查询项目
     * @param int     $idx          索引
     * @param string  $selectors    选择器
     * @param int     $level        级别
     * @param int     $search_level 搜索级别
     * @return bool
     * @throws Exception
     */
    private function search(&$search, $idx, $selectors, $level, $search_level = 0)
    {
        if ($search_level >= $level) {
            $rs = $this->seek($search, $selectors, $level - 1);
            if ($rs !== false && $idx !== null) {
                if ($idx == count($this->_lFind)) {
                    $this->_lFind[] = new self($rs);

                    return true;
                }

                $this->_lFind[] = new self($rs);
            }
            elseif ($rs !== false) {
                $this->_lFind[] = new self($rs);
            }
        }
        if (!empty($search->childNodes)) {
            foreach ($search->childNodes as $val) {
                if ($this->search($val, $idx, $selectors, $level, $search_level + 1)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 获取tidy_node文本
     *
     * @param DOMNode $node 节点名称
     * @return string
     */
    private function text(&$node)
    {
        return $node->textContent;
    }

    /**
     * 匹配节点,由于采取的倒序查找，所以时间复杂度为n+m*l n为总节点数，m为匹配最后一个规则的个数，l为规则的深度,
     * @codeCoverageIgnore
     * @param DOMNode $search    选择容器
     * @param array   $selectors 选择器
     * @param int     $current   当前起始
     * @return bool|DOMNode
     */
    private function seek($search, $selectors, $current)
    {
        if (!($search instanceof DOMElement)) {
            return false;
        }
        [$tag, $key, $val, $exp, $no_key] = $selectors [$current];
        $pass = true;
        if ($tag === '*' && !$key) {
            exit('tag为*时，key不能为空');
        }
        if ($tag && $tag != $search->tagName && $tag !== '*') {
            $pass = false;
        }
        if ($pass && $key) {
            if ($no_key) {
                if ($search->hasAttribute($key)) {
                    $pass = false;
                }
            }
            else {
                if ($key != 'plaintext' && !$search->hasAttribute($key)) {
                    $pass = false;
                }
            }
        }
        if ($pass && $key && $val && $val !== '*') {
            if ($key == 'plaintext') {
                $nodeKeyValue = $this->text($search);
            }
            else {
                $nodeKeyValue = $search->getAttribute($key);
            }
            $check = $this->match($exp, $val, $nodeKeyValue);
            if (!$check && strcasecmp($key, 'class') === 0) {
                foreach (explode(' ', $search->getAttribute($key)) as $k) {
                    if (!empty($k)) {
                        $check = $this->match($exp, $val, $k);
                        if ($check) {
                            break;
                        }
                    }
                }
            }
            if (!$check) {
                $pass = false;
            }
        }
        if ($pass) {
            $current--;
            if ($current < 0) {
                return $search;
            }
            elseif ($this->seek($this->getParent($search), $selectors, $current)) {
                return $search;
            }

            return false;
        }

        return false;
    }

    /**
     * 获取父亲节点
     *
     * @param DOMNode $node 节点名称
     * @return DOMNode
     */
    private function getParent($node)
    {
        return $node->parentNode;
    }
}