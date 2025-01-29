<?php namespace x\highlight;

function get() {
    if (!\class_exists("\\Asset")) {
        return;
    }
    \extract($GLOBALS, \EXTR_SKIP);
    $prefix = $state->x->highlight->c ?? 'hljs';
    $skin = $state->x->highlight->skin->name ?? 'default';
    if (\is_file($file = __DIR__ . \D . 'engine' . \D . 'vendor' . \D . 'scrivo' . \D . 'highlight.php' . \D . 'styles' . \D . $skin . '.css')) {
        $style = \preg_replace('/\.hljs\b/', '.' . $prefix, \file_get_contents($file));
        // Remove the hard-coded `padding` value from the original skin file
        $style = \preg_replace_callback('/\.' . \x($prefix) . '(\s*)\{(\s*)([^}]+?)(\s*)\}/', static function ($m) use ($prefix) {
            $parts = \explode("\n", $m[3]);
            foreach ($parts as &$part) {
                if (0 === \strpos(\trim($part), 'padding:')) {
                    $part = \preg_replace('/^(\s*)padding:/', '$1/* padding:', $part) . ' */';
                }
            }
            unset($part);
            return '.' . $prefix . $m[1] . '{' . $m[2] . \implode("\n", $parts) . $m[4] . '}';
        }, $style);
        \Asset::set('data:text/css;base64,' . \base64_encode($style), 20.1);
    }
}

function page__content($content) {
    if (!$content || false === \stripos($content, '</pre>')) {
        return $content;
    }
    \extract($GLOBALS, \EXTR_SKIP);
    $prefix = $state->x->highlight->c ?? 'hljs';
    return \preg_replace_callback('/<pre(\s(?:"[^"]*"|\'[^\']*\'|[^\/>])*)?>(\s*)<code(\s(?:"[^"]*"|\'[^\']*\'|[^\/>])*)?>([\s\S]*?)<\/code>(\s*)<\/pre>/i', static function ($m) use ($prefix) {
        $out  = '<pre' . ($m[1] ?? "") . '>';
        $out .= $m[2];
        require_once __DIR__ . \D . 'engine' . \D . 'vendor' . \D . 'autoload.php';
        $highlight = new \Highlight\Highlighter;
        $highlight->setClassPrefix($prefix . '-');
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
                    // Prioritize class name(s) with `lang-` and `language-` prefix
                    if ($language = \find($class, static function ($v) {
                        return 0 === \strpos($v, 'lang-') || 0 === \strpos($v, 'language-');
                    })) {
                        $v = $highlight->highlight(\explode('-', $language, 2)[1], \htmlspecialchars_decode($m[4]));
                    } else {
                        $highlight->setAutodetectLanguages($class);
                        $v = $highlight->highlightAuto(\htmlspecialchars_decode($m[4]));
                    }
                }
                $class[] = 'language-' . $v->language;
                $class[] = $prefix;
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
                $class[] = $prefix;
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

\Hook::set('get', __NAMESPACE__ . "\\get", -1);
\Hook::set('page.content', __NAMESPACE__ . "\\page__content", 2.1);