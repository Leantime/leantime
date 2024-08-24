<?php

/**
 * downloads.php - For Handling Downloads.
 *
 */

use Leantime\Core\Configuration\Environment;
use Leantime\Domain\Auth\Services\Auth;

define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

if (! file_exists($composer = APP_ROOT . '/vendor/autoload.php')) {
    throw new RuntimeException('Please run "composer install".');
}

require $composer;

$app = bootstrap_minimal_app();
$config = $app->make(Environment::class);

$login = $app->make(Auth::class);

if ($login->loggedIn() !== true) {
    header('Pragma: public');
    header('Cache-Control: max-age=86400');
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
    header('Content-Type: image/jpeg');

    ob_end_clean();
    clearstatcache();
    readfile(__DIR__ . '/dist/images/leantime-no-access.jpg');

    exit();
} else {
    if ($config->useS3) {
        getFileFromS3();
    } else {
        getFileLocally();
    }
}

/**
 * @return void
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 */
function getFileLocally(): void
{
    $config = app()->make(Environment::class);

    $encName = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['encName']);
    $realName = $_GET['realName'];
    $ext = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['ext']);
    $module = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['module']);

    $mimes = array(
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    );

    //TODO: Replace with ROOT
    $path = realpath(__DIR__ . "/../" . $config->userFilePath . "/");

    $fullPath = $path . "/" . $encName . '.' . $ext;

    if (file_exists(realpath($fullPath))) {
        if ($fd = fopen(realpath($fullPath), 'rb')) {
            $path_parts = pathinfo($fullPath);

            if ($ext == 'pdf') {
                header('Content-type: application/pdf');
                header("Content-Disposition: inline; filename=\"" . $realName . "." . $ext . "\"");
            } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
                header('Content-type: ' . $mimes[$ext]);
                header('Content-disposition: inline; filename="' . $realName . "." . $ext . '";');
            } elseif ($ext == 'svg') {
                header('Content-type: image/svg+xml');
                header('Content-disposition: attachment; filename="' . $realName . "." . $ext . '";');
            } else {
                header("Content-type: application/octet-stream");
                header("Content-Disposition: filename=\"" . $realName . "." . $ext . "\"");
            }

            if (ob_get_length() > 0) {
                ob_end_clean();
            }

            $chunkSize = 1024 * 1024;

            while (!feof($fd)) {
                $buffer = fread($fd, $chunkSize);
                echo $buffer;
            }
            fclose($fd);
        }
    } else {
        http_response_code(404);
        die();
    }
}

/**
 * @return void
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 */
function getFileFromS3(): void
{

    // Include the AWS SDK using the Composer autoloader.
    $encName = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['encName']);
    $realName = $_GET['realName'];
    $ext = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['ext']);
    $module = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['module']);

    $config = app()->make(Environment::class);

    $mimes = array(
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    );

    // Instantiate the client.

    $s3Client = new Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => $config->s3Region,
        'endpoint' => $config->s3EndPoint == "" ? null : $config->s3EndPoint,
        'use_path_style_endpoint' => !($config->s3UsePathStyleEndpoint == "false"),
        'credentials' => [
            'key'    => $config->s3Key,
            'secret' => $config->s3Secret,
        ],
    ]);

    try {
        // implode all non-empty elements to allow s3FolderName to be empty.
        // otherwise you will get an error as the key starts with a slash
        $fileName = implode('/', array_filter(array($config->s3FolderName, $encName . "." . $ext)));
        $result = $s3Client->getObject([
            'Bucket' => $config->s3Bucket,
            'Key' => $fileName,
            'Body'   => 'this is the body!',
        ]);

        if ($ext == 'pdf') {
            header('Content-type: application/pdf');
            header("Content-Disposition: inline; filename=\"" . $realName . "." . $ext . "\"");
        } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
            header('Content-Type: ' . $result['ContentType']);
            header("Content-Disposition: inline; filename=\"" . $fileName . "\"");
        } elseif ($ext == 'svg') {
            header('Content-type: image/svg+xml');
            header('Content-disposition: attachment; filename="' . $realName . "." . $ext . '";');
        } else {
            header('Content-disposition: attachment; filename="' . $realName . "." . $ext . '";');
        }

        $body = $result->get('Body');
        echo $body->getContents();
    } catch (Aws\S3\Exception\S3Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
