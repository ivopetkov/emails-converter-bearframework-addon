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

    public function emailToHTML(\BearFramework\Emails\Email $email, $optimize = false): string
    {
        $contentPart = $email->content->getList()->filterBy('mimeType', 'text/html')->getFirst();
        if ($contentPart !== null) {
            $content = $contentPart->content;
        } else {
            $content = '';
            $result = [];
            $contentParts = $email->content->getList();
            foreach ($contentParts as $contentPart) {
                $result[] = htmlspecialchars($contentPart->content);
            }
            $content = nl2br(implode("\n\n", $result));
        }
        if ($optimize) {
            $matches = null;
            preg_match_all('/href\=\"(skype|mailto|tel)\:(.*?)\"/', $content, $matches);
            $replacedTexts = [];
            foreach ($matches[0] as $match) {
                $replacement = 'href="http://' . md5($match) . '.xxx"';
                $replacedTexts[$match] = $replacement;
                $content = str_replace($match, $replacement, $content);
            }
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Attr.AllowedRel', ['nofollow', 'publisher']);
            $config->set('Cache.SerializerPath', sys_get_temp_dir());
            $purifier = new \HTMLPurifier($config);
            $content = $purifier->purify($content);
            foreach ($replacedTexts as $match => $replacement) {
                $content = str_replace($replacement, $match, $content);
            }
        }
        $dom = new \IvoPetkov\HTML5DOMDocument();
        $dom->loadHTML($content);
        if ($optimize) {

            $getDataURI = function(string $url) {
                if (strpos($url, '//') === 0) {
                    $url = 'http:' . $url;
                }
                if (strpos($url, 'https://') === 0 || strpos($url, 'http://') === 0) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $result = curl_exec($ch);
                    $error = curl_error($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);
                    if (!isset($error{0})) {
                        return 'data:' . (isset($info['content_type']) ? $info['content_type'] : '') . ';base64,' . base64_encode($result);
                    }
                }
                return 'emails-converter-undefined';
            };

            $elements = $dom->querySelectorAll('[src]');
            foreach ($elements as $element) {
                $element->setAttribute('src', $getDataURI((string) $element->getAttribute('src')));
            }
            $elements = $dom->querySelectorAll('[style]');
            foreach ($elements as $element) {
                $style = (string) $element->getAttribute('style');
                $matches = [];
                preg_match_all('/url\([\'"]*(.*?)[\'"]*\)/', $style, $matches);
                if (isset($matches[1])) {
                    foreach ($matches[1] as $match) {
                        $style = str_replace($match, $getDataURI($match), $style);
                    }
                }
                $element->setAttribute('style', $style);
            }
            $elements = $dom->querySelectorAll('a[href]');
            foreach ($elements as $element) {
                $element->setAttribute('rel', 'noopener');
                $element->setAttribute('target', '_blank');
            }
        }
        return $dom->saveHTML();
    }

    public function emailToText(\BearFramework\Emails\Email $email): string
    {
        $contentPart = $email->content->getList()->filterBy('mimeType', 'text/plain')->getFirst();
        if ($contentPart !== null) {
            return trim($contentPart->content);
        }
        $html = $this->emailToHTML($email);
        return trim($this->htmlToText($html));
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

    public function rawToHTML(string $raw): string
    {
        $email = $this->rawToEmail($raw);
        return $this->emailToHTML($email);
    }

    public function rawToText(string $raw): string
    {
        $email = $this->rawToEmail($raw);
        return $this->emailToText($email);
    }

    public function htmlToText(string $html): string
    {
        $html2Text = new \Html2Text\Html2Text($html);
        return $html2Text->getText();
    }

}
