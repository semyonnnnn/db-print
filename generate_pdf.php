<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/utils.php';

use Services\FormService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = new FormService();
    // dd($_POST);
    $service->generate($_POST); // outputs PDF
    exit;
}
