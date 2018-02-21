<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov\BearFrameworkAddons;

use BearFramework\App;
use IvoPetkov\HTML5DOMDocument;

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

    /**
     * 
     * @param \BearFramework\Emails\Email $email
     * @param array $options Available options : sanitize, inlineCSS, embedImages, removeImages, secureLinks
     * @return string
     */
    public function emailToHTML(\BearFramework\Emails\Email $email, $options = []): string
    {
        $sanitize = isset($options['sanitize']) && $options['sanitize'] === true;
        $inlineCSS = isset($options['inlineCSS']) && $options['inlineCSS'] === true;
        $embedImages = isset($options['embedImages']) && $options['embedImages'] === true;
        $removeImages = isset($options['removeImages']) && $options['removeImages'] === true;
        $secureLinks = isset($options['secureLinks']) && $options['secureLinks'] === true;

        $contentPart = $email->content->getList()->filterBy('mimeType', 'text/html')->getFirst();
        if ($contentPart !== null) {
            $content = trim($contentPart->content);
        } else {
            $content = '';
            $result = [];
            $contentParts = $email->content->getList();
            foreach ($contentParts as $contentPart) {
                $result[] = htmlspecialchars($contentPart->content, ENT_COMPAT | ENT_HTML5 | ENT_SUBSTITUTE);
            }
            $content = trim(nl2br(implode("\n\n", $result)));
        }

        $getUrlContent = function(string $value) {
            if (strpos($value, '//') === 0) {
                $value = 'http:' . $value;
            }
            if (strpos($value, 'https://') === 0 || strpos($value, 'http://') === 0) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $value);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                $result = curl_exec($ch);
                $error = curl_error($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                if (!isset($error{0})) {
                    return ['content' => $result, 'contentType' => isset($info['content_type']) ? $info['content_type'] : null];
                }
            }
            return ['content' => null, 'contentType' => null];
        };

        $getDataURI = function(string $value) use ($getUrlContent, $email) {
            if (strpos($value, 'data:') === 0) {
                return $value;
            }
            if (strpos($value, 'cid:') === 0) {
                $embed = $email->embeds->getList()
                        ->filterBy('cid', substr($value, 4))
                        ->getFirst();
                if ($embed !== null) {
                    return 'data:;base64,' . base64_encode($embed->content);
                }
                return '';
            }
            $result = $getUrlContent($value);
            if (strlen($result['content']) > 0) {
                return 'data:' . ($result['contentType']) . ';base64,' . base64_encode($result['content']);
            }
            return '';
        };

        if ($inlineCSS) {
            if (strlen($content) > 0) {
                $dom = new HTML5DOMDocument();
                $dom->loadHTML($content);
                $elements = $dom->querySelectorAll('link[rel="stylesheet"]');
                $cssStyles = [];
                foreach ($elements as $element) {
                    $href = (string) $element->getAttribute('href');
                    if (strlen($href) > 0) {
                        $cssStyles[] = trim((string) $getUrlContent($href)['content']);
                    }
                }
                $cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
                $content = $cssToInlineStyles->convert($content);
                foreach ($cssStyles as $cssStyle) {
                    if (strlen($cssStyle) > 0) {
                        $content = $cssToInlineStyles->convert($content, $cssStyle);
                    }
                }
            }
        }

        if ($sanitize) {
            $replacedTexts = [];

            $matches = null;
            preg_match_all('/src\=\"cid:(.*?)\"/', $content, $matches);
            foreach ($matches[0] as $match) {
                $replacement = 'src="http://' . md5($match) . '.xxx"';
                $replacedTexts[$match] = $replacement;
                $content = str_replace($match, $replacement, $content);
            }

            $matches = null;
            preg_match_all('/href\=\"(skype|mailto|tel)\:(.*?)\"/', $content, $matches);
            foreach ($matches[0] as $match) {
                $replacement = 'href="http://' . md5($match) . '.xxx"';
                $replacedTexts[$match] = $replacement;
                $content = str_replace($match, $replacement, $content);
            }

            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Attr.AllowedRel', ['nofollow', 'noopener', 'publisher']);
            $config->set('Cache.SerializerPath', sys_get_temp_dir());
            $purifier = new \HTMLPurifier($config);
            $content = $purifier->purify($content);
            foreach ($replacedTexts as $match => $replacement) {
                $content = str_replace($replacement, $match, $content);
            }
        }

        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content);

        if ($embedImages || $removeImages) {
            $elements = $dom->querySelectorAll('[src]');
            foreach ($elements as $element) {
                $src = (string) $element->getAttribute('src');
                $newSrc = $src;
                if ($embedImages) {
                    $newSrc = $getDataURI($src);
                    if (strlen($newSrc) > 0) {
                        $element->setAttribute('src', $newSrc);
                    } else {
                        $element->removeAttribute('src');
                    }
                } else {
                    $newSrc = '';
                    $element->removeAttribute('src');
                }
                if ($src !== $newSrc) {
                    $element->setAttribute('data-emails-converter-original-src', $src);
                }
            }
            $elements = $dom->querySelectorAll('[style]');
            foreach ($elements as $element) {
                $style = (string) $element->getAttribute('style');
                $newStyle = $style;
                $matches = [];
                preg_match_all('/url\([\'"]*(.*?)[\'"]*\)/', $newStyle, $matches);
                if (isset($matches[1])) {
                    foreach ($matches[1] as $index => $match) {
                        if ($embedImages) {
                            $newValue = $getDataURI($match);
                            if (strlen($newValue) > 0) {
                                $newStyle = str_replace($match, $newValue, $style);
                            } else {
                                $newStyle = str_replace($matches[0][$index], 'none', $style);
                            }
                        } else {
                            $newStyle = str_replace($matches[0][$index], 'none', $style);
                        }
                    }
                }
                if ($style !== $newStyle) {
                    $element->setAttribute('style', $newStyle);
                    $element->setAttribute('data-emails-converter-original-style', $style);
                }
            }
        }

        if ($inlineCSS) {
            $elements = $dom->querySelectorAll('[class]');
            foreach ($elements as $element) {
                $element->removeAttribute('class');
            }
        }

        if ($secureLinks) {
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

    public function rawToHTML(string $raw, $options = []): string
    {
        $email = $this->rawToEmail($raw);
        return $this->emailToHTML($email, $options);
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
