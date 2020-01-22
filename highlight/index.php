<?php

require __DIR__ . DS . 'engine' . DS . 'i' . DS . '@scrivo' . DS . 'highlight.php' . DS . 'Highlight' . DS . 'Autoloader.php';
require __DIR__ . DS . 'engine' . DS . 'i' . DS . '@scrivo' . DS . 'highlight.php' . DS . 'HighlightUtilities' . DS . 'functions.php';

spl_autoload_register("\\Highlight\\Autoloader::load");

require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'asset.php';
require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'hook.php';
