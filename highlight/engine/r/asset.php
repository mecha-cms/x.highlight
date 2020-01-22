<?php namespace _\lot\x\highlight;

function asset() {
    $state = \State::get('x.highlight', true);
    if ($skin = $state['skin'] ?? null) {
        try {
            $style = \HighlightUtilities\getStyleSheet($skin);
            if ($class = $state['class'] ?? null) {
                $style = \strtr($style, [
                    '.hljs' => '.' . $class
                ]);
            }
            \Asset::style($style);
        } catch (\DomainException $e) {}
    }
}

\Hook::set('get', __NAMESPACE__ . "\\asset", 20.1);
