<?php

return [
    'title' => 'Notifications',
    'subtitle' => 'Early warnings when conditions worsen or a device goes offline.',
    'unread' => ':n unread',
    'mark_all' => 'Mark all as read',
    'mark_read' => 'Mark as read',
    'empty_title' => 'No notifications. All good.',
    'empty_desc' => 'Alerts appear automatically when status changes to Getting Stale / Rotten or a device stays offline.',
    'type_stale' => 'Getting stale',
    'type_rotten' => 'Rotten',
    'type_offline' => 'Device offline',
    'msg_stale' => ':device detected as Getting Stale (:pct% confidence). Sort the cassava soon.',
    'msg_rotten' => ':device detected as Rotten (:pct% confidence). Separate it quickly so it does not spread.',
    'msg_offline' => ':device offline for more than :min minutes. Check power, WiFi, and sensors.',
];
