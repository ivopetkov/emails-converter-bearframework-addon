<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

BearFramework\Addons::register('ivopetkov/emails-converter-bearframework-addon', __DIR__, [
    'require' => [
        'bearframework/emails-addon',
        'ivopetkov/swiftmailer-bearframework-addon'
    ]
]);
