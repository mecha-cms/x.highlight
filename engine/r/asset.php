<?php namespace x\highlight;

function asset() {
    extract($GLOBALS, \EXTR_SKIP);
    $class = $state->x->highlight->{'class'} ?? null;
    $skin = $state->x->highlight->skin ?? null;
    if ($skin) {
        try {
            $style = \HighlightUtilities\getStyleSheet($skin);
            if ($class) {
                $style = \strtr($style, ['.hljs' => '.' . $class]);
            }
            \Asset::style($style);
        } catch (\DomainException $e) {}
    }
}

\Hook::set('get', __NAMESPACE__ . "\\asset", 20.1);