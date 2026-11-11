<?php

namespace Unit\app\Command;

use Leantime\Command\ClearAll;
use PHPUnit\Framework\TestCase;

/**
 * cache:clearAll never emptied the per-domain data caches. Without LEAN_APP_URL the web and
 * the CLI use different domain keys, so a setting changed from the CLI (e.g. turning the
 * login form back on) kept being served stale to the site for up to an hour.
 */
class ClearAllDomainCachesTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().'/lt-cache-'.bin2hex(random_bytes(4));
        foreach ([md5('web'), md5('cli')] as $domain) {
            mkdir($this->root.'/'.$domain.'/data/ab/cd', 0777, true);
            file_put_contents($this->root.'/'.$domain.'/data/ab/cd/entry', 'cached');
        }
        mkdir($this->root.'/avatars', 0777, true);
        file_put_contents($this->root.'/avatars/user.png', 'png');
        mkdir($this->root.'/installation/data', 0777, true);
        file_put_contents($this->root.'/installation/data/entry', 'cached');
    }

    protected function tearDown(): void
    {
        (new \Illuminate\Filesystem\Filesystem)->deleteDirectory($this->root);
    }

    public function test_every_domain_data_cache_is_emptied(): void
    {
        $cleared = (new ClearAll)->flushDomainCaches($this->root);

        $this->assertSame(2, $cleared);
        $this->assertFileDoesNotExist($this->root.'/'.md5('web').'/data/ab/cd/entry');
        $this->assertFileDoesNotExist($this->root.'/'.md5('cli').'/data/ab/cd/entry');
        $this->assertDirectoryExists($this->root.'/'.md5('web').'/data');
    }

    public function test_other_cache_directories_are_left_alone(): void
    {
        (new ClearAll)->flushDomainCaches($this->root);

        $this->assertFileExists($this->root.'/avatars/user.png');
        $this->assertFileExists($this->root.'/installation/data/entry');
    }

    public function test_a_missing_cache_root_clears_nothing(): void
    {
        $this->assertSame(0, (new ClearAll)->flushDomainCaches($this->root.'/missing'));
    }
}
