<?php

return [
    'enabled' => env('DEMO_LOGIN_ENABLED', false),
    'user_email' => env('DEMO_USER_EMAIL', 'demo@hubsport.test'),
    'token_expiration_minutes' => (int) env('DEMO_TOKEN_EXPIRATION_MINUTES', 120),
    'reset_schedule' => env('DEMO_RESET_SCHEDULE', '0 */6 * * *'),
];
