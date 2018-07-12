<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class EmailsConverterTest extends BearFramework\AddonTests\PHPUnitTestCase
{

    /**
     * 
     */
    public function testEmailToRawAndRawToEmail()
    {
        $app = $this->getApp();

        $email = $this->getExampleEmail();

        $raw = $app->emailsConverter->emailToRaw($email);

        $email2 = $app->emailsConverter->rawToEmail($raw);

        $listToArray = function($list) {
            $result = $list->toArray();
            foreach ($result as $i => $item) {
                if (isset($result[$i]['key'])) {
                    unset($result[$i]['key']);
                }
            }
            return $result;
        };

        $this->assertTrue($email->subject === $email2->subject);
        $this->assertTrue($email->date === $email2->date);
        $this->assertTrue($listToArray($email->sender) === $listToArray($email2->sender));
        $this->assertTrue($listToArray($email->replyToRecipients) === $listToArray($email2->replyToRecipients));
        $this->assertTrue($listToArray($email->bccRecipients) === $listToArray($email2->bccRecipients));
        $this->assertTrue($listToArray($email->ccRecipients) === $listToArray($email2->ccRecipients));
        $this->assertTrue($email->returnPath === $email2->returnPath);
        $this->assertTrue($email->priority === $email2->priority);
        $this->assertTrue($listToArray($email->recipients) === $listToArray($email2->recipients));
        $this->assertTrue($email->content->getList()[0]->content === $email2->content->getList()[0]->content);
        $this->assertTrue($email->content->getList()[0]->mimeType === $email2->content->getList()[0]->mimeType);
        $this->assertTrue($email->content->getList()[1]->content === $email2->content->getList()[1]->content);
        $this->assertTrue($email->content->getList()[1]->encoding === $email2->content->getList()[1]->encoding);
        $this->assertTrue($email->content->getList()[1]->mimeType === $email2->content->getList()[1]->mimeType);

        $this->assertTrue($email2->attachments->getList()[0] instanceof \BearFramework\Emails\Email\ContentAttachment);
        $this->assertTrue($email2->attachments->getList()[0]->content === file_get_contents($email->attachments->getList()[0]->filename));
        $this->assertTrue($email2->attachments->getList()[0]->mimeType === $email->attachments->getList()[0]->mimeType);
        $this->assertTrue($email2->attachments->getList()[0]->name === $email->attachments->getList()[0]->name);
        $this->assertTrue($email2->attachments->getList()[1] instanceof \BearFramework\Emails\Email\ContentAttachment);
        $this->assertTrue($email2->attachments->getList()[1]->content === $email->attachments->getList()[1]->content);
        $this->assertTrue($email2->attachments->getList()[1]->mimeType === $email->attachments->getList()[1]->mimeType);
        $this->assertTrue($email2->attachments->getList()[1]->name === $email->attachments->getList()[1]->name);

        $this->assertTrue($email2->embeds->getList()[0] instanceof \BearFramework\Emails\Email\ContentEmbed);
        $this->assertTrue($email2->embeds->getList()[0]->cid === $email->embeds->getList()[0]->cid);
        $this->assertTrue($email2->embeds->getList()[0]->content === file_get_contents($email->embeds->getList()[0]->filename));
        $this->assertTrue($email2->embeds->getList()[0]->mimeType === $email->embeds->getList()[0]->mimeType);
        $this->assertTrue($email2->embeds->getList()[0]->name === $email->embeds->getList()[0]->name);
        $this->assertTrue($email2->embeds->getList()[1] instanceof \BearFramework\Emails\Email\ContentEmbed);
        $this->assertTrue($email2->embeds->getList()[1]->cid === $email->embeds->getList()[1]->cid);
        $this->assertTrue($email2->embeds->getList()[1]->content === $email->embeds->getList()[1]->content);
        $this->assertTrue($email2->embeds->getList()[1]->mimeType === $email->embeds->getList()[1]->mimeType);
        $this->assertTrue($email2->embeds->getList()[1]->name === $email->embeds->getList()[1]->name);
        $email2Headers = $email2->headers->getList();

        $xCustom1Value = null;
        $xCustom2Value = null;
        foreach ($email2Headers as $email2Header) {
            if ($email2Header->name === 'X-Custom-1') {
                $xCustom1Value = $email2Header->value;
            } elseif ($email2Header->name === 'X-Custom-2') {
                $xCustom2Value = $email2Header->value;
            }
        }
        $this->assertTrue($xCustom1Value === 'value1');
        $this->assertTrue($xCustom2Value === 'value2');
    }

    /**
     * 
     */
    public function testEmailToHTML()
    {
        $app = $this->getApp();

        $email = $this->getExampleEmail(true);
        $html = $app->emailsConverter->emailToHTML($email);
        $expectedHTML = '<!DOCTYPE html><html>
    <head>
        <link rel="stylesheet" href="http://ivopetkov.github.io/example-files/example-css.css">
        <script src="http://ivopetkov.github.io/example-files/example-js.js"></script>
        <style>div{border:1px solid black;}</style>
    </head>
    <body>
        Hi,<br>
        <img src="http://ivopetkov.github.io/example-files/example-image-400-x-300.png"><br>
        <strong>Welcome to our service.</strong><br>
        Best regards,<br>
        John<br>
        <a href="http://example.com">example.com</a><br>
        <div style="height:400px;background-image:url(http://ivopetkov.github.io/example-files/example-image-400-x-400.png);background-repeat:no-repeat;"></div>
        (html version)
    </body>
</html>';
        $this->assertTrue($this->fixNewLines($html) === $this->fixNewLines($expectedHTML));

        $email = $this->getExampleEmail(true);
        $html = $app->emailsConverter->emailToHTML($email, ['sanitize' => true, 'inlineCSS' => true, 'embedImages' => true, 'secureLinks' => true]);
        $expectedHTML = '<!DOCTYPE html><html><body>Hi,<br><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAEsCAAAAADI3LoeAAADqUlEQVR42uzY4W2rMBQG0GzCKB7FozCKR2EUj8IzSSsXSEB9cQNI5/yivhG65rOdlBsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAvKPrVkMh9n0fp/GXpQa6cqtyr1AuD+rglIYxL0b6PD7k/rZRek9I5VZfUjiig5Pqy9TmTypPk42xT9NFt1X6H/VO45D6WG42lMv+4x2cVZjW2mIgfy/YmMukN0rvbMsh1qMrlUQ+3cFZ5XF+ZHVlYPZX3ii9sS/DcpuGD3dwUmkcwmxaab7surJ2X5faneLDmA7u4BximeEjkDrHuFi7+WWp4QItjRzcwSl000lRA6lTnCe0VWqkfDkc3MEpDGXLzwPJZWR1qG2VGilNHNzBGfTj8P0s6pLrVofJuFVa3/Tp0P6RNTTr4LJCmeBXIOujfJbRdmm9ZtdD+3s1turgunJ5CDWQumfWH9sp3RY/m5d5jGF/r+ZmHVxWGlM9vuvYsy+a7dI6kV/nUT7TroOLehwANZA6v3VwO6V1Ir/Jo5v+5Q4tO7ik+y/eGkg9AZ7Neav0KpH9PEKc3kkN5W1IvLXt4Iqm5bYTSD3Vt0qvEtnfH/nHW9vGHVzPNJE/C+SeyP551aeUhuHx+r11B1dTQuj+MJB7IjWPve+Qf+3a4XGjMBAGUHeiUlSKSqEUlUIplKLTJbkomFgMN/Ilurz3x+NZBi982MDiGkoe3cFcwlb31DMDeUmk5XEm1qVHdzCVNlu9C2T9/LzZKXUy7+VxXDqP7mAi6S2C51z2tuur9drUYHQH02jzhmfcGLY8tiuJbCUN7mAeH36Lx49OWh4xXEkklzy2g3mkUradt/d54Gjv9Xx+JZGl5J86XEzlc2u7fz+ccB6WenncJ3L+DRnXwVTCXt2o8NvDZ0D51il18jhJ5HgyGNfB1IY/wm15XEikrmtcB3Mb/SeHlseFRFIpYVgHk6uB7Lcx7IoldUoneZwn0hbKwzqY3UsgzdY2us00+qXjs6azROISdousdc2jOpjeXSChbnRqe3cLvdJR3OLx8E/Hzyw5fnxCVeKwDqZXAzlOofKy5LW+rqFX+ntLqda8vK/rn3fwfd0FUqWtvFrTrVM6cT50/2ONty/oYCohppRieFwacmyGVMXwhR0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/jV+E4HC5cUi14AAAAABJRU5ErkJggg==" style="border:1px solid #333;" alt="example-image-400-x-300.png" data-emails-converter-original-src="http://ivopetkov.github.io/example-files/example-image-400-x-300.png"><br><strong>Welcome to our service.</strong><br>
        Best regards,<br>
        John<br><a href="http://example.com" rel="noopener" target="_blank">example.com</a><br><div style=\'border:1px solid #000000;height:400px;background-image:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAGQCAAAAACl1GkQAAADXklEQVR42u3a4W2rMBQGUDZhFEbxKIySUTIKo/gpLX3BgB2iugjCOb/a3Kr6uBcZQ2gaAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADggtp28VEX+r4P7dpfF0oVA+yc4FDucZh90g/x29A320sVA+yd4FD6OOtHNzwONoT+9vih3ViqGGD3BIfSxVk/uhiHbvw5DMlBF0oVA+ye4FiGmK4Y7RDvyW/DllLFAPsnOJRbvHfJYd3S066Nsd9Qqhhg9wSHEuLQJv1oYwyzFX54XaoYYPcEx9rwxtg1ST8Wh/hsQqFUMcDeCY624+2btB/DYhG4/SzbhVLFAHsnONiO996k/WhjbBeLSnxVSv/ppo9yAWokOPOOt531IywX5Z82FEovztnsabwSoEKCM+94QzPrR7/Su/HPCqX8pvV7HrHbHKBCghPveG/NvB8/ny3W+WKpOJH8PNYC1Ehw3h1vs+jH2vGNfSiUShPJz2M1QJUE593xLvqxtgKMx1woFSaSn8d6gDoJTrvj3TSQcekulPITyc8jE6BSgrPueP9oIP8nUphHJsBVB9JNHgr9wUDGiRTmkQtw0YG0w6RTfzGQr4kU5pENcNGB3KeXwnSXFfJ7nLcuqe3ji73u7QAVE5xxx/sn297n/ur+foBLbntnzxsq3xg+5zFkrzD5AJe8MZytxXUfnUzuP9rcRAoBrvjoJMQ4JMbfbxUf7X1fzzMTKQW44sPFENfdk9vnxXpfKGXmkZtIKUCtBOe6hqQe36I+jEtAn7tsFkqZeeQmUgpQJ8HZbxKnS/jvv0Cd3n9kryO5ANf+CnfZjwqvGKT3gxsm4iWHFydouiV99qBQys5jy0RmrwH9OsFnDaRJ3gfskm4WSpN2zq+8LycyC/DbBJ82kMf7gOHZ3ekpWShNutQtn6KEtwbyywSfNpDvp1C3vr/dH1vRdmOpYoD9Exx7IF+vMY93BotTu1CqGGDvBCe4VelCCF37ZumzEgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACwg3+2V3wP89ppTwAAAABJRU5ErkJggg==");background-repeat:no-repeat;\' data-emails-converter-original-style=\'border:1px solid #000000;height:400px;background-image:url("http://ivopetkov.github.io/example-files/example-image-400-x-400.png");background-repeat:no-repeat;\'></div>
        (html version)</body></html>';
        $this->assertTrue($this->fixNewLines($html) === $this->fixNewLines($expectedHTML));

        $email = $this->getExampleEmail(false);
        $html = $app->emailsConverter->emailToHTML($email);
        $expectedHTML = '<!DOCTYPE html><html><body>Hi,<br>
Welcome to our service.<br>
Best regards,<br>
John<br>
example.com<br>
(text version)</body></html>';
        $this->assertTrue($this->fixNewLines($html) === $this->fixNewLines($expectedHTML));
    }

    /**
     * 
     */
    public function testEmailToText()
    {
        $app = $this->getApp();

        $email = $this->getExampleEmail();
        $text = $app->emailsConverter->emailToText($email);
        $expectedText = 'Hi,
Welcome to our service.
Best regards,
John
example.com
(text version)';
        $this->assertTrue($this->fixNewLines($text) === $this->fixNewLines($expectedText));

        $email = $this->getExampleEmail(true, false);
        $text = $app->emailsConverter->emailToText($email);
        $expectedText = 'Hi,

WELCOME TO OUR SERVICE.
Best regards,
John
example.com [http://example.com]

 (html version)';
        $this->assertTrue($this->fixNewLines($text) === $this->fixNewLines($expectedText));
    }

    /**
     * 
     */
    public function testRawToHTML()
    {
        $app = $this->getApp();

        $email = $this->getExampleEmail(true);
        $raw = $app->emailsConverter->emailToRaw($email);
        $html = $app->emailsConverter->rawToHTML($raw);
        $expectedHTML = '<!DOCTYPE html><html>
    <head>
        <link rel="stylesheet" href="http://ivopetkov.github.io/example-files/example-css.css">
        <script src="http://ivopetkov.github.io/example-files/example-js.js"></script>
        <style>div{border:1px solid black;}</style>
    </head>
    <body>
        Hi,<br>
        <img src="http://ivopetkov.github.io/example-files/example-image-400-x-300.png"><br>
        <strong>Welcome to our service.</strong><br>
        Best regards,<br>
        John<br>
        <a href="http://example.com">example.com</a><br>
        <div style="height:400px;background-image:url(http://ivopetkov.github.io/example-files/example-image-400-x-400.png);background-repeat:no-repeat;"></div>
        (html version)
    </body>
</html>';
        $this->assertTrue($this->fixNewLines($html) === $this->fixNewLines($expectedHTML));

        $email = $this->getExampleEmail(false);
        $raw = $app->emailsConverter->emailToRaw($email);
        $html = $app->emailsConverter->rawToHTML($raw);
        $expectedHTML = '<!DOCTYPE html><html><body>Hi,<br>
Welcome to our service.<br>
Best regards,<br>
John<br>
example.com<br>
(text version)</body></html>';
        $this->assertTrue($this->fixNewLines($html) === $this->fixNewLines($expectedHTML));
    }

    /**
     * 
     */
    public function testRawToText()
    {
        $app = $this->getApp();

        $email = $this->getExampleEmail();
        $raw = $app->emailsConverter->emailToRaw($email);
        $text = $app->emailsConverter->rawToText($raw);
        $expectedText = 'Hi,
Welcome to our service.
Best regards,
John
example.com
(text version)';
        $this->assertTrue($this->fixNewLines($text) === $this->fixNewLines($expectedText));

        $email = $this->getExampleEmail(true, false);
        $raw = $app->emailsConverter->emailToRaw($email);
        $text = $app->emailsConverter->rawToText($raw);
        $text = str_replace(["\r\n", "\n"], PHP_EOL, $text);
        $expectedText = 'Hi,

WELCOME TO OUR SERVICE.
Best regards,
John
example.com [http://example.com]

 (html version)';
        $this->assertTrue($this->fixNewLines($text) === $this->fixNewLines($expectedText));
    }

    private function getExampleEmail($addHTMLContent = true, $addTextContent = true)
    {
        $app = $this->getApp();
        $tempDir = $this->getTempDir() . '/files/';

        $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA1SsXa/03BOGTaZi1DpnFZfXNn9UfdiOYmiAFlA8wzDKNn3sX
ySEDYBfBGVTlH4SuMW5S//51Cb9UcI/hRySLH2FzBVHCmRXHUUQdVtOh8x7It2sF
wyONW7FWyHF3ygmAv7QdXJ+QsgsGVLKl8fsvq/cgUkgjsZXcSIJQU+4HQ0UNx4iK
toieVzs7tp692xrxbiTLqZEubfDL7Jckz8vy5rnRuSkF0w0ya5EBMclKAu3Usi76
kVkwIaUxsVz/vJrQ6HgjUaU+izeSU87tkHw8avpdTUGtZNGRLV8+yBicTiptKh4Z
oV+ZGfJC8+yj0fgzann1uqxXYZedy9UAFrL2hQIDAQABAoIBAQCfehuNuOTei/yC
9bIO+E/MFfb96q4c7kiOlgJWYH77ZZ66f5at7DrgOyVF1FOOHu9LH+SMjEv3POLp
S1sYpGhI09j/U8moSbjSPYlNmgsBHji/sBuhgAmTXbs1Pl6GElH2GcQVtXLFIKZd
20F3JZrmpn3R0RKTGzCwNn3uLJfWZIdx7uwHaaKy4bLLgxjlDtBrn5JP4K+LGxXK
E3YHU7dC9jnY+1J9fM2e/O8DB4QAT9l/dwEYdQt5wtCWJHfeFwQxcqhhezWe57Pm
h7tKHmMZ1RAboxnYfqrUZ056QYZq2nRkpQnjvbMSVLwBCDQdfej90JEEqUpT9UOm
4MQKun6BAoGBAO2jyxHOPzQIi1ZiFnPxME3mVLSEgAa6Ip/HMIDdSllfoDw180Uf
M+Iwpl8XhktmFoy3ZmmQ3Pd14vaOJZ/hZVRrvo5fTkJlqaA1fpqs2maQx4xn3bSt
8o6ffyrYCzvb1qdoOZ8X/cJIWtBgbxoF+dVKhb4g9Pei1LYkrQJp810xAoGBAOWj
SLFrwBA3az95h/EmpEeEw386mtf2v98y9ZajqoK9DIwmUSrTiQ5sC8cp3DPsR0oS
XDYSFziVS5HxGMm9vutv8FKgAzgeLNNVG6Gl4Fv0c0dFAchYrSPEN8J6NSjT0I4N
B6WA+mFUOrc0vmQkeTjgp0BAPhwKE03tSVRMdwmVAoGALDR+zuYdzbEVMlF2ucSQ
5rzE3vuS0S2IyU4FUMNZVDy8kta6VQ5T2WyRVjkLCzWHVk+7ZkHDSOkN+i1BBHeq
IMUWImfKKAG/RwUMcvtaeR/PbufXTwfYif1Ta4XauRzQ1j1GErkkxCIvenml0SJx
ceK19EMvLm2EwgkagctxsNECgYEA3c53+pd/Lqq49timjDGs4D/GrW+n/Q4jiq2o
Ndbkbd+47O5d3CXy7nFCdx9hyO1idpOBaeDTeR4Lnm3oaYTTkonO8aAJO/05gu9j
/yE/stJNPvvSmve8VR3EVh7Alizx9yNyzVPlhHldNXTGqefpBx1Hr3HeDCtXNiAK
glhRankCgYBgrafaZRt/4Q1GbnFBem1NFtMDmSM6XwdUjVs7/oGlMCZYLPlIhPgy
K/1+eR5gMTvLyXQNY07Vz8Dv49ViyLUcEU78hnve0ynaVfYTc26CH23SZpqCt2oN
UCbdDt1seqXdGqda2u5DCQmycF2jRRgAEZivej/TvsGtPP8gBWq9Ww==
-----END RSA PRIVATE KEY-----';

        $certificate = '-----BEGIN CERTIFICATE-----
MIIC/zCCAeegAwIBAgIJAJ94s/F1sEJJMA0GCSqGSIb3DQEBBQUAMBYxFDASBgNV
BAMMC2V4YW1wbGUuY29tMB4XDTE3MTIyODE4NTUwMFoXDTI3MTIyNjE4NTUwMFow
FjEUMBIGA1UEAwwLZXhhbXBsZS5jb20wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAw
ggEKAoIBAQDVKxdr/TcE4ZNpmLUOmcVl9c2f1R92I5iaIAWUDzDMMo2fexfJIQNg
F8EZVOUfhK4xblL//nUJv1Rwj+FHJIsfYXMFUcKZFcdRRB1W06HzHsi3awXDI41b
sVbIcXfKCYC/tB1cn5CyCwZUsqXx+y+r9yBSSCOxldxIglBT7gdDRQ3HiIq2iJ5X
Ozu2nr3bGvFuJMupkS5t8MvslyTPy/LmudG5KQXTDTJrkQExyUoC7dSyLvqRWTAh
pTGxXP+8mtDoeCNRpT6LN5JTzu2QfDxq+l1NQa1k0ZEtXz7IGJxOKm0qHhmhX5kZ
8kLz7KPR+DNqefW6rFdhl53L1QAWsvaFAgMBAAGjUDBOMB0GA1UdDgQWBBRsRun4
q/CGM1hXhrZuZIsrTaI0KTAfBgNVHSMEGDAWgBRsRun4q/CGM1hXhrZuZIsrTaI0
KTAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQA2cYDpr0pdu3V1Al/o
i2SwSQdJg1drluCEGcTZ/nVsnk5fMo5TZ8rsTLB74LkrtN+0ZhDkng9nNXd1PaUD
SRBSS7cf7CaFEXQHkB9ikXzA5JePLkU5ef8QbwjQdn32S9z8C5Ai4XV+3T71BmAj
Kd0LYCglnZxZTWwHLfS9haofDIgeREzqIgiz7pqB1zVo2qfGc5BW+slktfKU0OVN
qqOLHoSNSHXC3KR7vaO3s35+v4WY0cGG0RrS0Mc/tf9yYLl2ULUZE3ev/LX3BpZF
eCOEPGanuzUPXtA54I9FlgeL+MeDrqgPMFVkxr9vFPrVN7SGk+dDu1pQOf7ObKYw
t4AX
-----END CERTIFICATE-----';

        $email = $app->emails->make();
        $email->subject = 'The subject';
        $email->date = 1514481017;
        $email->sender->email = 'sender@example.com';
        $email->sender->name = 'John Smith';
        $email->replyToRecipients->add('replyto@example.com', 'John');
        $email->bccRecipients->add('bcc1@example.com', 'Henry');
        $email->bccRecipients->add('bcc2@example.com', 'Tom');
        $email->ccRecipients->add('cc1@example.com', 'Jane');
        $email->ccRecipients->add('cc2@example.com', 'Lisa');
        $email->returnPath = 'bounce@example.com';
        $email->priority = 3;
        $email->recipients->add('recipient1@example.com', 'Mark Smith');
        $email->recipients->add('recipient2@example.com', 'Bill Smith');
        if ($addTextContent) {
            $email->content->add('Hi,' . PHP_EOL . 'Welcome to our service.' . PHP_EOL . 'Best regards,' . PHP_EOL . 'John' . PHP_EOL . 'example.com' . PHP_EOL . '(text version)', 'text/plain');
        }
        if ($addHTMLContent) {
            $html = '<html>
    <head>
        <link rel="stylesheet" href="http://ivopetkov.github.io/example-files/example-css.css" />
        <script src="http://ivopetkov.github.io/example-files/example-js.js"></script>
        <style>div{border:1px solid black;}</style>
    </head>
    <body>
        Hi,<br>
        <img src="http://ivopetkov.github.io/example-files/example-image-400-x-300.png"/><br>
        <strong>Welcome to our service.</strong><br>
        Best regards,<br>
        John<br>
        <a href="http://example.com">example.com</a><br>
        <div style="height:400px;background-image:url(http://ivopetkov.github.io/example-files/example-image-400-x-400.png);background-repeat:no-repeat;"></div>
        (html version)
    </body>
</html>';
            $html = str_replace(["\r\n", "\n"], PHP_EOL, $html);
            $email->content->add($html, 'text/html', 'utf-8');
        }
        $this->makeSampleFile($tempDir . 'file1.jpg', 'jpg');
        $email->attachments->addFile($tempDir . 'file1.jpg', 'file1.jpg', 'image/jpeg');
        $email->attachments->addContent('text1', 'text1.txt', 'text/plain');
        $this->makeSampleFile($tempDir . 'file2.jpg', 'jpg');
        $email->embeds->addFile('embed1@a.a', $tempDir . 'file2.jpg', 'file2.jpg', 'image/jpeg');
        $email->embeds->addContent('embed2@a.a', 'text2', 'text2.txt', 'text/plain');
        //$email->signers->addSMIME($certificate, $privateKey); // todo
        $email->signers->addDKIM($privateKey, 'example.com', 'default');
        $email->headers->add('X-Custom-1', 'value1');
        $email->headers->add('X-Custom-2', 'value2');
        return $email;
    }

    private function fixNewLines($text)
    {
        $text = str_replace(["\r\n", "\n"], '-new-line-', $text);
        return str_replace('-new-line-', PHP_EOL, $text);
    }

}
