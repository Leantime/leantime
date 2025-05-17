<?php

namespace Tests\Unit\app\Core\Support;

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use LasseRafn\Initials\Initials;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\UI\Theme;
use SVG\SVG;
use Unit\TestCase;

class AvatarcreatorTest extends TestCase
{
    private $avatarGenerator;

    private $initials;

    private $theme;

    private $avatarCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->avatarGenerator = $this->createMock(InitialAvatar::class);
        $this->avatarGenerator->method('background')->willReturn($this->avatarGenerator);
        $this->avatarGenerator->method('font')->willReturn($this->avatarGenerator);
        $this->avatarGenerator->method('color')->willReturn($this->avatarGenerator);
        $this->avatarGenerator->method('generateSvg')->willReturn(SVG::fromString('<svg></svg>'));

        $this->initials = $this->createMock(Initials::class);
        $this->theme = $this->createMock(Theme::class);

        $this->avatarCreator = new Avatarcreator(
            $this->avatarGenerator,
            $this->initials,
            $this->theme
        );
    }

    public function test_set_background_color()
    {
        $this->avatarGenerator->expects($this->once())
            ->method('background')
            ->with('#ffffff');

        $this->avatarCreator->setBackground('#ffffff');
    }

    public function test_set_file_prefix()
    {
        $this->avatarCreator->setFilePrefix('test-prefix');
        $this->assertEquals('test-prefix', $this->avatarCreator->getFilePrefix());
    }

    public function test_set_initials_with_valid_name()
    {
        $this->initials->expects($this->once())
            ->method('name')
            ->with('john-doe');

        $this->avatarGenerator->expects($this->once())
            ->method('name')
            ->with('john-doe');

        $this->avatarCreator->setInitials('John Doe');
    }

    public function test_set_initials_with_empty_name()
    {
        $this->initials->expects($this->once())
            ->method('name')
            ->with('👻');

        $this->avatarCreator->setInitials('');
    }

    public function test_get_initials()
    {
        $this->initials->expects($this->once())
            ->method('getInitials')
            ->willReturn('JD');

        $this->assertEquals('JD', $this->avatarCreator->getInitials());
    }

    public function test_get_avatar_with_cache_hit()
    {
        $this->initials->method('getInitials')->willReturn('JD');

        // Create test file
        $cacheDir = storage_path('framework/cache/avatars');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $testFile = $cacheDir.'/user-jd.svg';
        file_put_contents($testFile, '<svg>test</svg>');

        $result = $this->avatarCreator->getAvatar('John Doe');

        $this->assertEquals(SVG::fromString('<svg>test</svg>'), $result);
        unlink($testFile);
    }

    public function test_get_avatar_with_cache_miss()
    {
        $this->initials->method('getInitials')->willReturn('JD');
        $this->avatarGenerator->method('generateSvg')
            ->willReturn(SVG::fromString('<svg></svg>'));

        $result = $this->avatarCreator->getAvatar('John Doe');

        $cacheDir = storage_path('framework/cache/avatars');
        $testFile = $cacheDir.'/user-jd.svg';

        $this->assertFileExists($testFile);
    }

    public function test_get_avatar_with_special_characters()
    {
        $this->initials->method('getInitials')->willReturn('JD');
        $this->avatarGenerator->method('generateSvg')
            ->willReturn(SVG::fromString('<svg></svg>'));

        $result = $this->avatarCreator->getAvatar('John@Doe#$%');

        $cacheDir = storage_path('framework/cache/avatars');
        $testFile = $cacheDir.'/user-jd.svg';

        $this->assertFileExists($testFile);
    }

    public function test_get_avatar_with_non_latin_characters()
    {
        $this->initials->method('getInitials')->willReturn('李王');
        $this->avatarGenerator->method('generateSvg')
            ->willReturn(SVG::fromString('<svg></svg>'));

        $result = $this->avatarCreator->getAvatar('李王');

        $cacheDir = storage_path('framework/cache/avatars');
        $testFile = $cacheDir.'/user-李王.svg';

        $this->assertFileExists($testFile);
    }
}
