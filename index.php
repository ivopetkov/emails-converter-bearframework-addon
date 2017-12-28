<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

use BearFramework\App;

$app = App::get();
$context = $app->context->get(__FILE__);

$context->classes->add('IvoPetkov\BearFrameworkAddons\EmailsConverter', 'classes/EmailsConverter.php');

$app->shortcuts
        ->add('emailsConverter', function() {
            return new IvoPetkov\BearFrameworkAddons\EmailsConverter();
        });