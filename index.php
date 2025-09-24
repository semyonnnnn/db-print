<script>
    fetch('http://localhost:2000')
        .then(res => res.text())
        .then(data => console.log('CORS allowed', data))
        .catch(err => console.error('CORS blocked', err));

</script>

<?php

ini_set('display_errors', 1);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Global exception handler
set_exception_handler(function (Throwable $e) {
    echo "<b>Fatal error: </b>" . $e->getMessage() . PHP_EOL . "</br></br>";

    // Print stack trace, each #0, #1, ... on its own line
    foreach ($e->getTrace() as $i => $trace) {
        echo "#" . $i + 1 . " ";
        if (isset($trace['file']))
            echo $trace['file'] . ":";
        if (isset($trace['line']))
            echo $trace['line'] . " ";
        if (isset($trace['class']))
            echo $trace['class'] . ($trace['type'] ?? '');
        if (isset($trace['function']))
            echo $trace['function'] . "()";
        echo PHP_EOL . "</br>";
    }

    exit(1);
});

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . ("/utils.php");
view("main");

