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

}
