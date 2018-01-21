<?php

/*
 * Emails converter addon for Bear Framework
 * https://github.com/ivopetkov/emails-converter-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

require __DIR__ . '/../vendor/autoload.php';

/**
 * 
 */
class BearFrameworkAddonTestCase extends PHPUnit_Framework_TestCase
{

    private $app = null;

    function getTestDir()
    {
        return sys_get_temp_dir() . '/unittests/' . uniqid() . '/';
    }

    function getApp($config = [], $createNew = false): \BearFramework\App
    {
        if ($this->app == null || $createNew) {
            $rootDir = $this->getTestDir();
            $this->app = new BearFramework\App();
            $this->createDir($rootDir . 'app/');
            $this->createDir($rootDir . 'data/');
            $this->createDir($rootDir . 'logs/');
            $this->createDir($rootDir . 'addons/');

            $initialConfig = [
                'appDir' => $rootDir . 'app/',
                'dataDir' => $rootDir . 'data/',
                'logsDir' => $rootDir . 'logs/',
                'addonsDir' => realpath($rootDir . 'addons/')
            ];
            $config = array_merge($initialConfig, $config);
            foreach ($config as $key => $value) {
                $this->app->config->$key = $value;
            }
            $this->app->config->handleErrors = false;

            $this->app->initialize();
            $this->app->request->base = 'http://example.com/www';
            $this->app->request->method = 'GET';

            $this->app->addons->add('ivopetkov/emails-converter-bearframework-addon');
        }

        return $this->app;
    }

    function createDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    function createFile($filename, $content)
    {
        $pathinfo = pathinfo($filename);
        if (isset($pathinfo['dirname']) && $pathinfo['dirname'] !== '.') {
            if (!is_dir($pathinfo['dirname'])) {
                mkdir($pathinfo['dirname'], 0777, true);
            }
        }
        file_put_contents($filename, $content);
    }

    function createSampleFile($filename, $type)
    {
        if ($type === 'png') {
            $this->createFile($filename, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAGQAAABGCAIAAAC15KY+AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4AIECCIIiEjqvwAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAAd0lEQVR42u3QMQEAAAgDILV/51nBzwci0CmuRoEsWbJkyZKlQJYsWbJkyVIgS5YsWbJkKZAlS5YsWbIUyJIlS5YsWQpkyZIlS5YsBbJkyZIlS5YCWbJkyZIlS4EsWbJkyZKlQJYsWbJkyVIgS5YsWbJkKZAl69sC1G0Bi52qvwoAAAAASUVORK5CYII='));
        } elseif ($type === 'jpg' || $type === 'jpeg') {
            $this->createFile($filename, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wCEAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf/CABEIAEYAZAMBEQACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAACf/aAAgBAQAAAACL4AAAAAAAAAAAAAAAAAAB/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAn/2gAIAQIQAAAAlOAAAAAAAAAAAAAAAAAAAf/EABUBAQEAAAAAAAAAAAAAAAAAAAAK/9oACAEDEAAAAL+AAAAAAAAAAAAAAAAAAAD/xAAUEAEAAAAAAAAAAAAAAAAAAABg/9oACAEBAAE/AGv/xAAUEQEAAAAAAAAAAAAAAAAAAABg/9oACAECAQE/AGv/xAAUEQEAAAAAAAAAAAAAAAAAAABg/9oACAEDAQE/AGv/2Q=='));
        } elseif ($type === 'gif') {
            $this->createFile($filename, base64_decode('R0lGODdhZABGAPAAAP8AAAAAACwAAAAAZABGAAACXISPqcvtD6OctNqLs968+w+G4kiW5omm6sq27gvH8kzX9o3n+s73/g8MCofEovGITCqXzKbzCY1Kp9Sq9YrNarfcrvcLDovH5LL5jE6r1+y2+w2Py+f0uv2Oz5cLADs='));
        } elseif ($type === 'webp') {
            $this->createFile($filename, base64_decode('UklGRlYAAABXRUJQVlA4IEoAAADQAwCdASpkAEYAAAAAJaQB2APwA/QACFiY02iY02iY02iY02iYywAA/v9vVv//8sPx/Unn/yxD///4npzeIqeV//EyAAAAAAAAAA=='));
        } elseif ($type === 'bmp') {
            $this->createFile($filename, base64_decode('Qk16AAAAAAAAAHYAAAAoAAAAAQAAAAEAAAABAAQAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAAAgAAAAICAAIAAAACAAIAAgIAAAICAgADAwMAAAAD/AAD/AAAA//8A/wAAAP8A/wD//wAA////APAAAAA='));
        } elseif ($type === 'broken') {
            $this->createFile($filename, base64_decode('broken'));
        }
    }

    function getExampleEmail($addHTMLContent = true, $addTextContent = true)
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
        if ($addTextContent) {
            $email->content->add('Hi,' . PHP_EOL . 'Welcome to our service.' . PHP_EOL . 'Best regards,' . PHP_EOL . 'John' . PHP_EOL . 'example.com' . PHP_EOL . '(text version)', 'text/plain');
        }
        if ($addHTMLContent) {
            $html = '<html>
    <head>
        <link rel="stylesheet" href="http://ivopetkov.github.io/example-files/example-css.css" />
        <script src="http://ivopetkov.github.io/example-files/example-js.js"></script>
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
        return $email;
    }

    public function fixNewLines($text)
    {
        $text = str_replace(["\r\n", "\n"], '-new-line-', $text);
        return str_replace('-new-line-', PHP_EOL, $text);
    }

}
