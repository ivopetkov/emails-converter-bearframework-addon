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

        $tempDir = $this->getTestDir() . 'files/';

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
        $email->content->add('Hi', 'text/plain');
        $email->content->add('<strong>Hi</strong>', 'text/html', 'utf-8');
        $this->createSampleFile($tempDir . 'file1.jpg', 'jpg');
        $email->attachments->addFile($tempDir . 'file1.jpg', 'file1.jpg', 'image/jpeg');
        $email->attachments->addContent('text1', 'text1.txt', 'text/plain');
        $this->createSampleFile($tempDir . 'file2.jpg', 'jpg');
        $email->embeds->addFile('embed1@a.a', $tempDir . 'file2.jpg', 'file2.jpg', 'image/jpeg');
        $email->embeds->addContent('embed2@a.a', 'text2', 'text2.txt', 'text/plain');
        //$email->signers->addSMIME($certificate, $privateKey); // todo
        $email->signers->addDKIM($privateKey, 'example.com', 'default');
        $email->headers->add('X-Custom-1', 'value1');
        $email->headers->add('X-Custom-2', 'value2');

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

}
