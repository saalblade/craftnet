<?php
/**
 * Site URL Rules
 *
 * You can define custom site URL rules here, which Craft will check in addition
 * to any routes you’ve defined in Settings → Routes.
 *
 * See http://www.yiiframework.com/doc-2.0/guide-runtime-routing.html for more
 * info about URL rules.
 *
 * In addition to Yii’s supported syntaxes, Craft supports a shortcut syntax for
 * defining template routes:
 *
 *     'blog/archive/<year:\d{4}>' => ['template' => 'blog/_archive'],
 *
 * That example would match URIs such as `/blog/archive/2012`, and pass the
 * request along to the `blog/_archive` template, providing it a `year` variable
 * set to the value `2012`.
 */

return [
    'queue/handle-message' => 'queue/handle-message',
    'api' => [
        'POST v1/updates' => 'craftcom/api/v1/updates',
        'POST v1/plugin-store' => 'craftcom/api/v1/plugin-store',
        'GET  v1/account' => 'craftcom/api/v1/account',
        'POST v1/developer/<userId:\d+>' => 'craftcom/api/v1/developer',
        'POST v1/plugin/<pluginId:\d+>' => 'craftcom/api/v1/plugin',
        'POST v1/checkout' => 'craftcom/api/v1/checkout',
        'POST v1/utils/releases-2-changelog' => 'craftcom/api/v1/utils/releases-2-changelog',
        'POST v1/available-plugins' => 'craftcom/api/v1/available-plugins',
        'POST webhook/github' => 'craftcom/api/webhook/github',
        'update-deps' => 'craftcom/update-deps',

        'oauth/login' => 'oauth-server/oauth/login',
        'oauth/authorize' => 'oauth-server/oauth/authorize',
        'oauth/access-token' => 'oauth-server/oauth/access-token',
        'oauth/revoke' => 'oauth-server/oauth/revoke',
    ],
    'composer' => [
    ],
    'craftId' => [
        'GET  v1/id' => 'craftcom/id/v1/id',
        'GET  craft-id' => 'craftcom/id/craft-id',
        'GET  apps/connect/<appTypeHandle:{handle}>' => 'craftcom/id/apps/connect',
        'GET  apps/callback' => 'craftcom/id/apps/callback',
        'GET  apps/disconnect/<appTypeHandle:{handle}>' => 'craftcom/id/apps/disconnect',

        'GET  stripe/connect' => 'craftcom/id/stripe/connect',
        'GET  stripe/account' => 'craftcom/id/stripe/account',
        'POST stripe/disconnect' => 'craftcom/id/stripe/disconnect',
        'GET  stripe/customer' => 'craftcom/id/stripe/customer',
        'POST stripe/save-card' => 'craftcom/id/stripe/save-card',
        'POST stripe/remove-card' => 'craftcom/id/stripe/remove-card',

        // Catch-all route for Vue when people reload the page.
        'login'=> ['template' => 'login'],
        'register'=> ['template' => 'register'],
        'register/success'=> ['template' => 'register/success'],
        'forgotpassword'=> ['template' => 'forgotpassword'],
        '<url:(.*)>'=> 'craftcom/id/account',
    ],
    'plugins' => [
        //'' => 'craftcom/plugins/index/index'
    ]
];
