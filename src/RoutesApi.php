<?php
use App\Api\StatusApi;

return [
    [['GET'], 'status', [StatusApi::class, 'show'], ['public' => true]],
];
