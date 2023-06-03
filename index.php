<?php

namespace x\highlight {
    function asset($content) {
        if (!\class_exists("\\Asset")) {
            return $content;
        }
        $style = \file_get_contents(__DIR__ . \D . 'engine' . \D . 'vendor' . \D . 'scrivo' . \D . 'highlight.php' . \D . 'styles' . \D . 'default.css');
        $style = \preg_replace('/\.hljs([\s.-])/', '.hlphp$1', $style);
        \Asset::set('data:text/css;base64,' . \base64_encode($style), 10);
    }
    function content($content) {
        if (!$content || false === \stripos($content, '</pre>')) {
            return $content;
        }
        return \preg_replace_callback('/<pre(\s(?:"[^"]*"|\'[^\']*\'|[^\/>]+)*)?>(\s*)<code(\s(?:"[^"]*"|\'[^\']*\'|[^\/>]+)*)?>([\s\S]*?)<\/code>(\s*)<\/pre>/i', static function ($m) {
            $out  = '<pre' . ($m[1] ?? "") . '>';
            $out .= $m[2];
            require_once __DIR__ . \D . 'engine' . \D . 'vendor' . \D . 'autoload.php';
            $highlight = new \Highlight\Highlighter;
            $highlight->setClassPrefix('hlphp-');
            $code = new \HTML(['code', $m[4], []]);
            // `<code class="asdf">…</code>`
            if (false !== \stripos($test = $m[3] ?? "", 'class=') && \preg_match('/\bclass=("[^"]+"|\'[^\']+\'|\S+)/i', $test, $mm)) {
                if ('"' === $mm[1][0] || "'" === $mm[1][0]) {
                    $class = \preg_split('/\s+/', \substr(\htmlspecialchars_decode($mm[1]), 1, -1), -1, \PREG_SPLIT_NO_EMPTY);
                } else {
                    $class = \preg_split('/\s+/', \htmlspecialchars_decode($mm[1]), -1, \PREG_SPLIT_NO_EMPTY);
                }
                try {
                    // `<code class="asdf">…</code>`
                    if (1 === \count($class)) {
                        $v = $highlight->highlight(\reset($class), \htmlspecialchars_decode($m[4]));
                    // `<code class="asdf asdf asdf">…</code>`
                    } else {
                        if ($language = \find($class, static function ($v) {
                            return 0 === \strpos($v, 'lang-') || 0 === \strpos($v, 'language-');
                        })) {
                            $v = $highlight->highlight(\preg_replace('/^lang(uage)?-/', "", $language), \htmlspecialchars_decode($m[4]));
                        } else {
                            $highlight->setAutodetectLanguages($class);
                            $v = $highlight->highlightAuto(\htmlspecialchars_decode($m[4]));
                        }
                    }
                    $class[] = 'language-' . $v->language;
                    $class[] = 'hlphp';
                    $class = \array_unique($class);
                    \sort($class);
                    $code['class'] = \implode(' ', $class);
                    $code[1] = $v->value;
                } catch (\Throwable $e) {}
            // `<code>…</code>`
            } else {
                try {
                    $class = [];
                    $highlight->setAutodetectLanguages(['css', 'html', 'javascript', 'json', 'php', 'xml', 'yaml']);
                    $v = $highlight->highlightAuto(\htmlspecialchars_decode($m[4]));
                    $class[] = 'language-' . $v->language;
                    $class[] = 'hlphp';
                    $class = \array_unique($class);
                    \sort($class);
                    $code['class'] = \implode(' ', $class);
                    $code[1] = $v->value;
                } catch (\Throwable $e) {}
            }
            $out .= $code;
            $out .= '</pre>';
            return $out;
        }, $content);
    }
    \Hook::set('content', __NAMESPACE__ . "\\asset", -1);
    \Hook::set('page.content', __NAMESPACE__ . "\\content", 2.1);
}