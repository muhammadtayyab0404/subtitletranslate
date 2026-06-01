<?php

return [
    'python' => env('LOCAL_AI_PYTHON', '/home/malikdon/Transaltion/venv/bin/python'),
    'script' => env('LOCAL_AI_SCRIPT', '/home/malikdon/Transaltion/test06_bridge.py'),
    'timeout' => (int) env('LOCAL_AI_TIMEOUT', 180),
];