<?php

namespace Leantime\Domain\Files\Controllers;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 *
 */
class Get extends Controller
{
    private FileService $filesService;
    private FileRepository $filesRepo;

    private Environment $config;

    /**
     * @param FileRepository $filesRepo
     * @param FileService    $filesService
     * @return void
     */
    public function init(
        FileRepository $filesRepo,
        FileService $filesService,
        Environment $config
    ): void {
        $this->filesRepo = $filesRepo;
        $this->filesService = $filesService;
        $this->config = $config;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function get(): Response
    {

        $encName = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['encName']);
        $realName = $_GET['realName'];
        $ext = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['ext']);
        $module = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['module'] ?? '');

        if ($this->config->useS3) {
            return $this->getFileFromS3($encName, $ext, $module, $realName);
        } else {
            return $this->getFileLocally($encName, $ext, $module, $realName);
        }
    }

    /**
     * Retrieves a file locally and returns it as a streamed response.
     *
     * @param string $encName The encoded name of the file.
     * @param string $ext The extension of the file.
     * @param string $module The module of the file.
     * @param string $realName The real name of the file.
     * @return Response The streamed response containing the file or a 404 response if the file was not found.
     */
    private function getFileLocally($encName, $ext, $module, $realName): Response
    {

        $mimes = array(
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpg',
            'gif' => 'image/gif',
            'png' => 'image/png',
        );

        //TODO: Replace with ROOT
        $path = realpath(APP_ROOT . "/" . $this->config->userFilePath . "/");

        $fullPath = $path . "/" . $encName . '.' . $ext;

        if (file_exists(realpath($fullPath))) {
            if ($fd = fopen(realpath($fullPath), 'rb')) {
                $path_parts = pathinfo($fullPath);

                if ($ext == 'pdf') {
                    $mime_type = 'application/pdf';
                    header('Content-type: application/pdf');
                    header("Content-Disposition: inline; filename=\"" . $realName . "." . $ext . "\"");
                } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
                    $mime_type = $mimes[$ext];
                    header('Content-type: ' . $mimes[$ext]);
                    header('Content-disposition: inline; filename="' . $realName . "." . $ext . '";');
                } elseif ($ext == 'svg') {
                    $mime_type = 'image/svg+xml';
                    header('Content-type: image/svg+xml');
                    header('Content-disposition: attachment; filename="' . $realName . "." . $ext . '";');
                } else {
                    $mime_type = 'application/octet-stream';
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=\"" . $realName . "." . $ext . "\"");
                }


                $sLastModified = filemtime($fullPath);
                $sEtag = md5_file($fullPath);

                $sFileSize = filesize($fullPath);


                $oStreamResponse = new StreamedResponse();
                $oStreamResponse->headers->set("Content-Type", $mime_type);
                $oStreamResponse->headers->set("Content-Length", $sFileSize);
                $oStreamResponse->headers->set("ETag", $sEtag);

                if (app()->make(Environment::class)->debug == false) {
                    $oStreamResponse->headers->set("Pragma", 'public');
                    $oStreamResponse->headers->set("Cache-Control", 'max-age=86400');
                    $oStreamResponse->headers->set("Last-Modified", gmdate("D, d M Y H:i:s", $sLastModified) . " GMT");
                }else{
                    error_log("not caching");
                }

                $oStreamResponse->setCallback(function () use ($fullPath) {
                    readfile($fullPath);
                });

                return $oStreamResponse;
            }
        }

        return new Response("File not found", 404);

    }

    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getFileFromS3($encName, $ext, $module, $realName): Response
    {

        $mimes = array(
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpg',
            'gif' => 'image/gif',
            'png' => 'image/png',
        );

        // Instantiate the client.

        $s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $this->config->s3Region,
            'endpoint' => $this->config->s3EndPoint == "" ? null : $this->config->s3EndPoint,
            'use_path_style_endpoint' => !($this->config->s3UsePathStyleEndpoint == "false"),
            'credentials' => [
                'key'    => $this->config->s3Key,
                'secret' => $this->config->s3Secret,
            ],
        ]);

        try {
            // implode all non-empty elements to allow s3FolderName to be empty.
            // otherwise you will get an error as the key starts with a slash
            $fileName = implode('/', array_filter(array($this->config->s3FolderName, $encName . "." . $ext)));
            $result = $s3Client->getObject([
                'Bucket' => $this->config->s3Bucket,
                'Key' => $fileName,
                'Body'   => 'this is the body!',
            ]);

            $response = new Response($result->get('Body')->getContents());

            if ($ext == 'pdf') {
                $response->headers->set('Content-type', 'application/pdf');
            } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
                $response->headers->set('Content-type', $result['ContentType']);
            } elseif ($ext == 'svg') {
                $response->headers->set('Content-type', 'image/svg+xml');
            } else {
                header('Content-disposition: attachment; filename="' . $realName . "." . $ext . '";');
            }

            $response->headers->set('Content-Disposition', "inline; filename=\"" . $realName . "." . $ext . "\"");

            return $response;

        } catch (\Exception $e) {

            Log::error($e);

            return new Response("File cannot be found", 400);
        }
    }
}
