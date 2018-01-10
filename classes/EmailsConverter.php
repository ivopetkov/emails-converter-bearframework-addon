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
        $email->date = strlen($data['date']) > 0 ? (int) $data['date'] : null;
        $email->subject = $data['subject'];
        foreach ($data['to'] as $recipient) {
            $email->recipients->add($recipient['email'], $recipient['name']);
        }
        $email->sender->email = $data['from']['email'];
        $email->sender->name = $data['from']['name'];
        $email->returnPath = $data['returnPath'];
        $email->priority = $data['priority'];
        foreach ($data['replyTo'] as $replyToRecipient) {
            $email->replyToRecipients->add($replyToRecipient['email'], $replyToRecipient['name']);
        }
        foreach ($data['cc'] as $ccRecipient) {
            $email->ccRecipients->add($ccRecipient['email'], $ccRecipient['name']);
        }
        foreach ($data['bcc'] as $bccRecipient) {
            $email->bccRecipients->add($bccRecipient['email'], $bccRecipient['name']);
        }
        foreach ($data['content'] as $contentPart) {
            $email->content->add($contentPart['content'], $contentPart['mimeType'], $contentPart['encoding']);
        }
        foreach ($data['attachments'] as $attachments) {
            $email->attachments->addContent((string) $attachments['content'], $attachments['name'], $attachments['mimeType']);
        }
        foreach ($data['embeds'] as $embed) {
            $email->embeds->addContent((string) $embed['id'], (string) $embed['content'], $embed['name'], $embed['mimeType']);
        }
        foreach ($data['headers'] as $header) {
            $lowerCaseName = strtolower($header['name']);
            if (in_array($lowerCaseName, ['from', 'reply-to', 'to', 'cc', 'bcc', 'date', 'subject', 'return-path', 'x-priority'])) {
                continue;
            }
            $email->headers->add($header['name'], $header['value']);
        }

        return $email;
    }

}
