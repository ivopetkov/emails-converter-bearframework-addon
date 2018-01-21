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
    public function testEmailToRawAndRawToEmail()
    {
        $app = $this->getApp();

        $email = $this->getExampleEmail();

        $raw = $app->emailsConverter->emailToRaw($email);

        $email2 = $app->emailsConverter->rawToEmail($raw);

        $this->assertTrue($email->subject === $email2->subject);
        $this->assertTrue($email->date === $email2->date);
        $this->assertTrue($email->sender->toArray() === $email2->sender->toArray());
        $this->assertTrue($email->replyToRecipients->toArray() === $email2->replyToRecipients->toArray());
        $this->assertTrue($email->bccRecipients->toArray() === $email2->bccRecipients->toArray());
        $this->assertTrue($email->ccRecipients->toArray() === $email2->ccRecipients->toArray());
        $this->assertTrue($email->returnPath === $email2->returnPath);
        $this->assertTrue($email->priority === $email2->priority);
        $this->assertTrue($email->recipients->toArray() === $email2->recipients->toArray());
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
        $html = $app->emailsConverter->emailToHTML($email, true);
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

}
