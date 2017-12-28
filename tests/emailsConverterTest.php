<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class EmailsConverterTest extends BearFrameworkAddonTestCase
{

    /**
     * 
     */
    public function testConvert()
    {
        $app = $this->getApp();

        $raw = 'Message-ID: <xxx111.john@example.com>
Date: xxx222
Subject: Hi
From: John <john@example.com>
To: Mark <mark@example.com>
MIME-Version: 1.0
Content-Type: multipart/alternative;
 boundary=boundary-xxx333


--boundary-xxx333
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

Hi there

--boundary-xxx333
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable

<strong>Hi</strong>

--boundary-xxx333--';
        $raw = str_replace("\n", "\r\n", $raw);

        $email = $app->emailsConverter->rawToEmail($raw);
        //print_r($email->toArray());
        $newRaw = $app->emailsConverter->emailToRaw($email);
        $newRaw = preg_replace('/Message\-ID\: \<(.*)\.john\@example\.com\>/', 'Message-ID: <xxx111.john@example.com>', $newRaw);
        $newRaw = preg_replace('/Date\: (.*)/', 'Date: xxx222', $newRaw);
        $newRaw = preg_replace('/boundary\-([a-z0-9]*)/', 'boundary-xxx333', $newRaw);
        $newRaw = trim($newRaw);
        
        $raw = preg_replace('~\r\n?~', "\n", $raw);
        $newRaw = preg_replace('~\r\n?~', "\n", $newRaw);

        $this->assertTrue($raw === $newRaw);
    }

}
