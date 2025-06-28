<?php

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$envPath = dirname(__DIR__).'/.env';

if (file_exists($envPath)) {
    $dotenv->bootEnv($envPath);
} else {
    putenv('APP_ENV=test');
    putenv('APP_SECRET=dummy_secret');
    putenv('DATABASE_URL=sqlite:///%kernel.project_dir%/var/test.db');
}

