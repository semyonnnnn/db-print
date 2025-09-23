<?php
function dd($var): never
{
    echo "<pre>";
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