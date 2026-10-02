<?php

namespace Unit\app\Domain\Plugins\Services;

use Illuminate\Support\Facades\File;
use Leantime\Domain\Plugins\Services\PluginArchive;
use Unit\TestCase;

/**
 * The marketplace archive must be verified before anything reaches the plugin directory: unsafe
 * entries are refused before extraction, the phar signature is checked in a staging directory,
 * and the installed version is only replaced once both pass. Phars are assembled byte-for-byte in
 * the phar file format (phar.readonly forbids building them with the Phar class in tests).
 */
class PluginArchiveTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'plugin-archive-test-'.bin2hex(random_bytes(8));
        mkdir($this->workDir, 0700, true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->workDir);

        parent::tearDown();
    }

    /**
     * An OpenSSL-signed phar (Phar::OPENSSL, SHA-1 digest) and its public key, laid out as the
     * phar extension writes them: content, signature, signature length, flags, "GBMB".
     *
     * @return array{0: string, 1: string} [phar bytes, public key PEM]
     */
    private function opensslSignedPhar(string $content = "<?php __HALT_COMPILER(); ?>\nplugin-body"): array
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_sign($content, $signature, $key, OPENSSL_ALGO_SHA1);
        $publicKey = openssl_pkey_get_details($key)['key'];

        $phar = $content.$signature.pack('V', strlen($signature)).pack('V', 0x0010).'GBMB';

        return [$phar, $publicKey];
    }

    /**
     * @param  array<string, string>  $entries  entry name => content
     */
    private function makeZip(array $entries): string
    {
        $zipPath = $this->workDir.DIRECTORY_SEPARATOR.'download-'.bin2hex(random_bytes(4)).'.zip';
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return $zipPath;
    }

    private function existingPluginDir(): string
    {
        $pluginDir = $this->workDir.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'Notes';
        mkdir($pluginDir, 0700, true);
        file_put_contents($pluginDir.DIRECTORY_SEPARATOR.'Notes.phar', 'installed-version');

        return $pluginDir;
    }

    public function test_installs_a_correctly_signed_archive_replacing_the_old_version(): void
    {
        [$phar, $publicKey] = $this->opensslSignedPhar();
        $zipPath = $this->makeZip(['Notes.phar' => $phar, 'Notes.phar.pubkey' => $publicKey]);
        $pluginDir = $this->existingPluginDir();

        (new PluginArchive)->install($zipPath, 'Notes', $pluginDir);

        $this->assertSame($phar, file_get_contents($pluginDir.'/Notes.phar'));
        $this->assertSame($publicKey, file_get_contents($pluginDir.'/Notes.phar.pubkey'));
    }

    public function test_refuses_a_tampered_phar_and_keeps_the_installed_version(): void
    {
        [$phar, $publicKey] = $this->opensslSignedPhar();
        $tampered = 'X'.substr($phar, 1);
        $zipPath = $this->makeZip(['Notes.phar' => $tampered, 'Notes.phar.pubkey' => $publicKey]);
        $pluginDir = $this->existingPluginDir();

        try {
            (new PluginArchive)->install($zipPath, 'Notes', $pluginDir);
            $this->fail('A phar whose signature does not match must be refused');
        } catch (\RuntimeException) {
        }

        $this->assertSame('installed-version', file_get_contents($pluginDir.'/Notes.phar'));
    }

    public function test_refuses_an_archive_with_a_traversal_entry_before_extracting(): void
    {
        [$phar, $publicKey] = $this->opensslSignedPhar();
        $zipPath = $this->makeZip([
            'Notes.phar' => $phar,
            'Notes.phar.pubkey' => $publicKey,
            '../../escaped.php' => '<?php echo 1;',
        ]);
        $pluginDir = $this->existingPluginDir();

        try {
            (new PluginArchive)->install($zipPath, 'Notes', $pluginDir);
            $this->fail('A path-traversal entry must be refused');
        } catch (\RuntimeException) {
        }

        $this->assertSame('installed-version', file_get_contents($pluginDir.'/Notes.phar'));
        $this->assertFileDoesNotExist(dirname($this->workDir).DIRECTORY_SEPARATOR.'escaped.php');
    }

    public function test_refuses_an_archive_without_the_expected_phar(): void
    {
        $zipPath = $this->makeZip(['Other.phar' => 'x', 'index.php' => '<?php']);
        $pluginDir = $this->existingPluginDir();

        $this->expectException(\RuntimeException::class);

        (new PluginArchive)->install($zipPath, 'Notes', $pluginDir);
    }

    public function test_refuses_an_openssl_signed_phar_without_its_public_key(): void
    {
        [$phar] = $this->opensslSignedPhar();
        $pharPath = $this->workDir.'/Lonely.phar';
        file_put_contents($pharPath, $phar);

        $this->assertFalse(PluginArchive::hasValidPharSignature($pharPath));
    }

    public function test_verifies_hash_signed_phars(): void
    {
        $content = "<?php __HALT_COMPILER(); ?>\nbody";
        $pharPath = $this->workDir.'/Hashed.phar';

        file_put_contents($pharPath, $content.hash('sha256', $content, true).pack('V', 0x0003).'GBMB');
        $this->assertTrue(PluginArchive::hasValidPharSignature($pharPath));

        file_put_contents($pharPath, $content.'tampered'.hash('sha256', $content, true).pack('V', 0x0003).'GBMB');
        $this->assertFalse(PluginArchive::hasValidPharSignature($pharPath));

        file_put_contents($pharPath, 'not a phar at all');
        $this->assertFalse(PluginArchive::hasValidPharSignature($pharPath));
    }

    public function test_entry_name_rules(): void
    {
        $this->assertTrue(PluginArchive::isSafeEntryName('Notes.phar'));
        $this->assertTrue(PluginArchive::isSafeEntryName('sub/dir/file.php'));

        $this->assertFalse(PluginArchive::isSafeEntryName('../x.php'));
        $this->assertFalse(PluginArchive::isSafeEntryName('a/../../x.php'));
        $this->assertFalse(PluginArchive::isSafeEntryName('/etc/passwd'));
        $this->assertFalse(PluginArchive::isSafeEntryName('C:/x.php'));
        $this->assertFalse(PluginArchive::isSafeEntryName('a\\..\\x.php'));
        $this->assertFalse(PluginArchive::isSafeEntryName("a\0.php"));
        $this->assertFalse(PluginArchive::isSafeEntryName(''));
    }
}
