<?php namespace x;

function highlight($content) {
    if (!$content || false === \strpos($content, '</pre>')) {
        return $content;
    }
    return \preg_replace_callback('/<pre(\s[^>]*)?>\s*<code(\s[^>]*)?>([\s\S]*?)<\/code>\s*<\/pre>/', function($m) {
        $languages = [];
        if (false !== \strpos($m[2], ' class=')) {
            \preg_replace_callback('/ class=([\'"])(.*?)\1 /', function($m) use(&$languages) {
                $class = ' ' . $m[2] . ' ';
                if ($c = \strstr(\trim(\strstr($class, ' lang-') ?: \strstr($class, ' language-')) . ' ', ' ', true)) {
                    // Prioritize class name with `lang-` or `language-` prefix
                    $languages[] = \explode('-', $c, 2)[1] ?? null;
                } else {
                    // Auto detection
                    $languages = \preg_split('/\s+/', \trim($class));
                }
            }, ' ' . $m[2] . ' ');
        }
        if ($languages) {
            extract($GLOBALS, \EXTR_SKIP);
            $p = $state->x->highlight->{'class'} ?? 'hl';
            $prefix = $state->x->highlight->state->{'class-prefix'} ?? $p . '-';
            $in = new \Highlight\Highlighter;
            $in->setClassPrefix($prefix);
            foreach ($state->x->highlight->state ?? [] as $k => $v) {
                $in->{'set' . \p($k)}($v);
            }
            try {
                $code = \htmlspecialchars_decode($m[3]);
                if (1 === \count($languages)) {
                    $out = $in->highlight($languages[0], $code);
                } else {
                    $in->setAutodetectLanguages($languages);
                    $out = $in->highlightAuto($code);
                }
                $m[2] = \trim(\preg_replace_callback('/ class=([\'"])(.*?)\1 /', function($m) use($out, $p, $state) {
                    $a = \explode(' ', $m[2]);
                    $a[] = $p;
                    $a[] = $out->language;
                    $a = \array_unique(\array_filter($a));
                    \sort($a);
                    return ' class=' . $m[1] . \implode(' ', $a) . $m[1] . ' ';
                }, ' ' . $m[2] . ' '));
                return '<pre' . $m[1] . '><code' . ($m[2] ? ' ' . $m[2] : "") . '>' . $out->value . '</code></pre>';
            } catch (\DomainException $e) {
                return $m[0];
            }
        }
        return $m[0];
    }, $content);
}

\Hook::set('page.content', __NAMESPACE__ . "\\highlight", 2.1);