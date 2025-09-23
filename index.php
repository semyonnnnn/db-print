<script>
    fetch('http://localhost:2000')
        .then(res => res.text())
        .then(data => console.log('CORS allowed', data))
        .catch(err => console.error('CORS blocked', err));

</script>

<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . ("/utils.php");
view("main");

