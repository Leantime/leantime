<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use LasseRafn\Initials\Initials;
use SVG\SVG;

class Avatarcreator
{
    protected $filePrefix = 'user';

    protected const MAX_FILENAME_LENGTH = 255;

    public function __construct(
        protected InitialAvatar $avatarGenerator,
        protected Initials $initials
    ) {
        $this->initials->allowSpecialCharacters(true);

        // Set some default values
        $this->avatarGenerator->font(APP_ROOT.'/public/dist/fonts/roboto/Roboto-Medium.ttf');
        $this->avatarGenerator->background('#00a887')->color('#fff');

    }

    public function setBackground(string $color)
    {
        $this->avatarGenerator->background($color);
    }

    public function setFilePrefix($prefix)
    {
        $this->filePrefix = Str::sanitizeFilename($prefix);
    }

    public function getFilePrefix(): string
    {
        return $this->filePrefix;
    }

    public function setInitials($name)
    {
        $cleanString = Str::sanitizeFilename($name);

        if (empty($cleanString)) {
            $this->initials->name('👻');
        } else {
            $this->initials->name($cleanString);
        }

        $this->avatarGenerator->name($cleanString);

    }

    public function getInitials()
    {
        return $this->initials->getInitials();
    }

    public function getAvatar($name): string|SVG
    {
        $this->setInitials($name);
        $filename = $this->getSafeFilename();

        if (file_exists($filename)) {
            return $filename;
        }

        return $this->saveAvatar();

    }

    protected function saveAvatar(): string|SVG
    {

        if (is_dir(storage_path('framework/cache/avatars')) === false) {
            mkdir(storage_path('framework/cache/avatars'));

            // Set proper permissions for security
            chmod(storage_path('framework/cache/avatars'), 0755);
        }

        $filename = $this->getSafeFilename();

        if (! file_exists($filename)) {
            $image = $this->generateAvatar();

            if (! is_writable(storage_path('framework/cache/avatars/'))) {

                Log::error("Can't write to avatars folder");

                return $image;
            }

            file_put_contents($filename, $image);

        }

        return $filename;

    }

    protected function getSafeFilename(): string
    {
        $baseFilename = $this->filePrefix.'-'.$this->getInitials();

        // Ensure filename doesn't exceed maximum length
        if (strlen($baseFilename) > self::MAX_FILENAME_LENGTH - 4) { // -4 for .svg
            $baseFilename = substr($baseFilename, 0, self::MAX_FILENAME_LENGTH - 4);
        }

        return storage_path('framework/cache/avatars/'.
            Str::sanitizeFilename($baseFilename).'.svg');
    }

    protected function generateAvatar(): SVG
    {
        return $this->avatarGenerator->generateSvg();
    }
}
