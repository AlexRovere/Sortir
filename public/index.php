<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

if (getenv('PHP_SESSION_PATH')) {
    session_save_path(getenv('PHP_SESSION_PATH'));
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
