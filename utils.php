<?php
function dd($var): never
{
    $iterations = 100;
    $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $iterations);

    echo "<pre>";
    echo "<b>";
    foreach ($traces as $key => $trace) {
        $i = $key + 1;
        echo "$i file: " . $trace['file'] . " at line " . $trace['line'] . "\n\n";
    }
    echo "</b>\n";
    var_dump($var);
    echo "</pre>";
    die();
}
function path(string $str): string
{
    return __DIR__ . "/" . $str . '.php';
}

function path_not_php(string $str): string
{
    return __DIR__ . "/" . $str;
}

function view(string $view, array $params = [])
{
    extract($params);
    require __DIR__ . "/views/" . $view . '.view.php';
}