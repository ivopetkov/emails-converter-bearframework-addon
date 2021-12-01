<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) Ivo Petkov
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

    /**
     * 
     * @param \BearFramework\Emails\Email $email
     * @return string
     */
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
            $content = $this->getContentPartContentInUTF8($contentPart, true);
        } else {
            $content = '';
            $result = [];
            $contentParts = $email->content->getList();
            foreach ($contentParts as $contentPart) {
                $result[] = htmlspecialchars($this->getContentPartContentInUTF8($contentPart), ENT_COMPAT | ENT_HTML5 | ENT_SUBSTITUTE);
            }
            $content = trim(nl2br(implode("\n\n", $result)));
        }

        $emailAccountToText = function ($emailAccount): string {
            $name = (string)$emailAccount->name;
            $email = (string)$emailAccount->email;
            if (strlen($name) > 0) {
                return $name . ' <' . $email . '>';
            }
            return $email;
        };
        $embeds = $email->embeds->getList();
        foreach ($embeds as $embed) {
            if ($embed->mimeType === 'message/rfc822') {
                $embedEmail = $this->rawToEmail($embed->content);
                $embedEmailRecipientsList = $embedEmail->recipients->getList();
                $embedEmailRecipientsText = [];
                foreach ($embedEmailRecipientsList as $embedEmailRecipient) {
                    $embedEmailRecipientsText[] = $emailAccountToText($embedEmailRecipient);
                }
                $embedEmailContent = $this->emailToHTML($embedEmail, $options);
                $appendContent = '<br><br>' . nl2br(htmlspecialchars('---------- Forwarded message ----------
From: ' . $emailAccountToText($embedEmail->sender) . '
Date: ' . (strlen($embedEmail->date) > 0 ? date('M j, Y', $embedEmail->date) . ' at ' . date('H:i', $embedEmail->date) : '') . '
Subject: ' . $embedEmail->subject . '
To: ' . implode(', ', $embedEmailRecipientsText))) . '<br><br>';
                $dom = new HTML5DOMDocument();
                $dom->loadHTML($content, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
                $dom->insertHTML($appendContent);
                $dom->insertHTML($embedEmailContent);
                $content = $dom->saveHTML();
            }
        }

        $getURLContent = function (string $value): array {
            if (strpos($value, '//') === 0) {
                $value = 'http:' . $value;
            }
            if (strpos($value, 'https://') === 0 || strpos($value, 'http://') === 0) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $value);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                $result = (string)curl_exec($ch);
                $error = (string)curl_error($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                if (!isset($error[0])) {
                    return ['content' => $result, 'contentType' => isset($info['content_type']) ? $info['content_type'] : null];
                }
            }
            return ['content' => null, 'contentType' => null];
        };

        $getDataURI = function (string $value) use ($getURLContent, $email): string {
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
            $result = $getURLContent($value);
            if (strlen($result['content']) > 0) {
                return 'data:' . ($result['contentType']) . ';base64,' . base64_encode($result['content']);
            }
            return '';
        };

        if ($inlineCSS) {
            if (strlen($content) > 0) {
                $dom = new HTML5DOMDocument();
                $dom->loadHTML($content, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
                $elements = $dom->querySelectorAll('link[rel="stylesheet"]');
                $cssStyles = [];
                foreach ($elements as $element) {
                    $href = (string) $element->getAttribute('href');
                    if (strlen($href) > 0) {
                        $cssStyles[] = trim((string) $getURLContent($href)['content']);
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
        $dom->loadHTML($content, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

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

    /**
     * 
     * @param \BearFramework\Emails\Email $email
     * @return string
     */
    public function emailToText(\BearFramework\Emails\Email $email): string
    {
        $contentPart = $email->content->getList()->filterBy('mimeType', 'text/plain')->getFirst();
        if ($contentPart !== null) {
            return trim($this->getContentPartContentInUTF8($contentPart));
        }
        $html = $this->emailToHTML($email);
        return trim($this->htmlToText($html));
    }

    /**
     * 
     * @param string $raw
     * @return \BearFramework\Emails\Email
     */
    public function rawToEmail(string $raw): \BearFramework\Emails\Email
    {
        $app = App::get();
        $emailParser = new \IvoPetkov\EmailParser();
        $data = $emailParser->parse($raw, true);

        $email = $app->emails->make();
        $email->date = $data['date'] !== null && strlen($data['date']) > 0 ? (int) $data['date'] : null;
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
        foreach ($data['attachments'] as $attachment) {
            $email->attachments->addContent((string) $attachment['content'], $attachment['name'], $attachment['mimeType']);
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

    /**
     * 
     * @param string $raw
     * @param type $options
     * @return string
     */
    public function rawToHTML(string $raw, $options = []): string
    {
        $email = $this->rawToEmail($raw);
        return $this->emailToHTML($email, $options);
    }

    /**
     * 
     * @param string $raw
     * @return string
     */
    public function rawToText(string $raw): string
    {
        $email = $this->rawToEmail($raw);
        return $this->emailToText($email);
    }

    /**
     * 
     * @param string $html
     * @return string
     */
    public function htmlToText(string $html): string
    {
        $html2Text = new \Html2Text\Html2Text($html);
        return $html2Text->getText();
    }

    /**
     * 
     * @param \BearFramework\Emails\Email\ContentPart $contentPart
     * @param bool $isHtml
     * @return string
     */
    private function getContentPartContentInUTF8(\BearFramework\Emails\Email\ContentPart $contentPart, bool $isHtml = false): string
    {
        $content = (string)$contentPart->content;
        $encoding = (string)$contentPart->encoding;
        if (strlen($encoding) > 0) { // && strtolower(mb_detect_encoding($content)) !== 'utf-8'
            $emailParser = new \IvoPetkov\EmailParser();
            $content = $emailParser->convertEncoding($content, 'utf-8', $encoding);
            if ($isHtml) {
                $content = preg_replace('/<meta(.*?)charset=(["\']*)([a-zA-Z0-9\-\_]+)(["\']*)(.*?)>/i', '<meta$1charset=$2utf-8$4$5>', $content);
            }
        }
        return $content;
    }
}
