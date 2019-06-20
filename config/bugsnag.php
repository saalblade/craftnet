<?php

return [
    // Enable exception logging
    'enabled' => true,
    // Project API key
    'serverApiKey' => getenv('BUGSNAG_API_KEY'),
    // Release stage
    'releaseStage' => 'production',
    // Release stages to log exceptions in
    'notifyReleaseStages' => ['production'],
    // Sensitive attributes to filter out, like 'password'
    'filters' => [],
    // Metadata to send with every request
    'metaData' => [],
];
