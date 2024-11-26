<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use LasseRafn\Initials\Initials;
use Leantime\Core\UI\Theme;
use SVG\SVG;

class Avatarcreator
{
    protected $filePrefix = 'user';

    public function __construct(
        protected InitialAvatar $avatarGenerator,
        protected Initials $initials,
        protected Theme $theme
    ) {

        $this->initials->allowSpecialCharacters(false);

        $bgColor = '#00a887';
        if (isset($colorschemes['companyColors']) && isset($colorschemes['companyColors']['secondaryColor'])) {
            $bgColor = $colorschemes['companyColors']['secondaryColor'];
        }

        //Set some default values
        $this->avatarGenerator->font(APP_ROOT.'/public/dist/fonts/roboto/Roboto-Medium.ttf')
            ->background('#00a887')
            ->color('#fff');

    }

    public function setBackground(string $color)
    {
        $this->avatarGenerator->background($color);
    }

    public function setFilePrefix($prefix)
    {
        $this->filePrefix = Str::sanitizeFilename($prefix);
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
        $filename = storage_path('framework/cache/avatars/'.$this->filePrefix.'-'.$this->getInitials().'.svg');

        if (file_exists($filename)) {
            return $filename;
        }

        return $this->saveAvatar();

    }

    protected function saveAvatar(): string|SVG
    {
        if (is_dir(storage_path('framework/cache/avatars')) === false) {
            mkdir(storage_path('framework/cache/avatars'));
        }

        if (! file_exists($filename = storage_path('framework/cache/avatars/'.$this->filePrefix.'-'.$this->getInitials().'.svg'))) {

            $image = $this->generateAvatar();

            if (! is_writable(storage_path('framework/cache/avatars/'))) {

                Log::error("Can't write to avatars folder");

                return $image;
            }

            file_put_contents($filename, $image);

        }

        return $filename;

    }

    protected function generateAvatar(): SVG
    {
        return $this->avatarGenerator->generateSvg();
    }
}
