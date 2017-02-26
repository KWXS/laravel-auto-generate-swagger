<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 25.02.17
 * Time: 11:28
 */

namespace  RonasIT\Support\AutoDoc\Services;


use RonasIT\Support\AutoDoc\Exceptions\CannotFindTemporaryFileException;
use Illuminate\Support\Str;

class RemoteDataCollectorService
{
    protected $remoteUrl;
    protected $tempFilePath;
    protected $key;

    public function __construct()
    {
        $this->tempfilePath = config('auto-doc.files.temporary');
        $this->remoteUrl = "http://docs.ronasit.com/{$this->key}";

        $this->createKeyFromTitle();

        if (empty($this->tempfilePath)) {
            throw new CannotFindTemporaryFileException();
        }
    }

    public function saveData($tempFile){
        $this->tempfilePath = $tempFile;

        $this->makeRequest();
    }

    public function getFileContent() {
        return file_get_contents($this->remoteUrl);
    }

    protected function makeRequest() {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->remoteUrl,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => [
                'document' => $this->tempfilePath,
            ]
        ]);

        curl_exec($curl);

        if (curl_error($curl)) {
            throw new CurlRequestErrorException();
        } else {
            curl_close($curl);
        }
    }

    protected function createKeyFromTitle() {
        $appTitle = strtolower(config('auto-doc.info.title'));

        $this->key = str_replace(' ', '_', $appTitle);
    }
}