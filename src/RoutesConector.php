<?php
use App\Api\ConectorApi;

return [
    [['POST'], 'ping', [ConectorApi::class, 'ping']],
];
