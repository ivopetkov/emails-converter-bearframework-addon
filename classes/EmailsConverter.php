<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov\BearFrameworkAddons;

use BearFramework\App;

/**
 * Emails converter.
 */
class EmailsConverter
{

    public function emailToRaw(\BearFramework\Emails\Email $email): string
    {
        $app = App::get();
        $swiftMessage = $app->swiftMailer->emailToSwiftMessage($email);
        return $swiftMessage->toString();
    }

    public function rawToEmail(string $raw): \BearFramework\Emails\Email
    {
        $app = App::get();
        $emailParser = new \IvoPetkov\EmailParser();
        $data = $emailParser->parse($raw);

        $email = $app->emails->make();
        if (strlen($data['text']) > 0) {
            $email->content->add($data['text'], 'text/plain');
        }
        if (strlen($data['html']) > 0) {
            $email->content->add($data['html'], 'text/html');
        }
        $email->subject = $data['subject'];
        $email->recipients->add($data['to'][0], $data['to'][1]);
        $email->sender->email = $data['from'][0];
        $email->sender->name = $data['from'][1];
        return $email;
    }

}
