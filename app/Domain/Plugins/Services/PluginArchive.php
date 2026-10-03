<?php

namespace Leantime\Domain\Plugins\Services;

use Illuminate\Support\Facades\File;

/**
 * Verifies and unpacks a downloaded marketplace plugin archive.
 *
 * A marketplace download is a ZIP holding the plugin's `{Folder}.phar` (OpenSSL-signed) and its
 * `{Folder}.phar.pubkey`. Nothing from it reaches the plugin directory until it has passed every
 * check: the entries are inspected BEFORE extraction (safe relative paths only, bounded count and
 * size, the expected phar present), the archive is extracted into a private staging directory
 * outside the plugin directory, and the phar's signature is verified there. Only then is the
 * staged content copied into place.
 *
 * The phar signature is checked by reading the phar trailer directly rather than opening it with
 * the Phar class: an installed (loaded) version of the same plugin already holds the phar alias in
 * this process, which would make opening the new copy fail.
 *
 * Intentionally carries no @api tags: it is an internal helper of the plugin installer.
 */
class PluginArchive
{
    /** Most entries a plugin archive may contain. */
    public const MAX_ENTRIES = 100;

    /** Largest total uncompressed size a plugin archive may unpack to (bytes). */
    public const MAX_UNCOMPRESSED_BYTES = 200 * 1024 * 1024;

    /** Phar signature flags (see the phar file format) mapped to their hash algorithm and length. */
    private const HASH_SIGNATURES = [
        0x0001 => ['md5', 16],
        0x0002 => ['sha1', 20],
        0x0003 => ['sha256', 32],
        0x0004 => ['sha512', 64],
    ];

    /** OpenSSL phar signature flags mapped to the digest used with openssl_verify(). */
    private const OPENSSL_SIGNATURES = [
        0x0010 => OPENSSL_ALGO_SHA1,
        0x0011 => OPENSSL_ALGO_SHA256,
        0x0012 => OPENSSL_ALGO_SHA512,
    ];

    /**
     * Verifies $zipPath and installs its content into $pluginDir, replacing what was there.
     *
     * The existing plugin directory is only replaced once the new archive has been fully verified,
     * and the replacement is a rename swap, so a bad download or a failed copy leaves the installed
     * version untouched.
     *
     * @param  string  $zipPath  The downloaded archive.
     * @param  string  $folderName  The plugin folder name; the archive must hold `{folderName}.phar`.
     * @param  string  $pluginDir  The target plugin directory.
     *
     * @throws \RuntimeException When the archive is unsafe, incomplete, or its phar signature is invalid,
     *                           or the content can't be put in place.
     */
    public function install(string $zipPath, string $folderName, string $pluginDir): void
    {
        $stagingDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'leantime-plugin-'.bin2hex(random_bytes(16));

        try {
            $this->extractVerified($zipPath, $folderName, $stagingDir);
            $this->swapInto($stagingDir, $pluginDir);
        } finally {
            if (is_dir($stagingDir)) {
                File::deleteDirectory($stagingDir);
            }
        }
    }

    /**
     * Replaces $pluginDir with the verified content of $stagingDir without ever leaving it
     * missing or half-written: the content is first copied next to the plugin directory (same
     * filesystem), then the old directory is renamed aside and the new one renamed into place.
     * If the swap fails the previous version is restored.
     *
     * @param  string  $stagingDir  The verified, extracted archive.
     * @param  string  $pluginDir  The target plugin directory.
     *
     * @throws \RuntimeException When the new version can't be put in place.
     */
    private function swapInto(string $stagingDir, string $pluginDir): void
    {
        $pluginDir = rtrim($pluginDir, '/\\');
        $parentDir = dirname($pluginDir);
        $suffix = bin2hex(random_bytes(8));
        $incomingDir = $parentDir.DIRECTORY_SEPARATOR.'.'.basename($pluginDir).'.incoming-'.$suffix;
        $backupDir = $parentDir.DIRECTORY_SEPARATOR.'.'.basename($pluginDir).'.previous-'.$suffix;

        try {
            if (! File::copyDirectory($stagingDir, $incomingDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $pluginDir));
            }

            $hadPreviousVersion = is_dir($pluginDir);
            if ($hadPreviousVersion && ! @rename($pluginDir, $backupDir)) {
                throw new \RuntimeException(__('notification.plugin_cant_remove'));
            }

            if (! @rename($incomingDir, $pluginDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $pluginDir));
            }
        } finally {
            // A failed swap puts the previous version back before anything is cleaned up.
            if (! is_dir($pluginDir) && is_dir($backupDir)) {
                @rename($backupDir, $pluginDir);
            }

            foreach ([$incomingDir, $backupDir] as $leftover) {
                if (is_dir($leftover)) {
                    File::deleteDirectory($leftover);
                }
            }
        }
    }

    /**
     * Inspects the archive, extracts it into $stagingDir and verifies the plugin phar there.
     *
     * @param  string  $zipPath  The downloaded archive.
     * @param  string  $folderName  The plugin folder name; the archive must hold `{folderName}.phar`.
     * @param  string  $stagingDir  A directory that does not exist yet; created here.
     *
     * @throws \RuntimeException When any check fails.
     */
    public function extractVerified(string $zipPath, string $folderName, string $stagingDir): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::RDONLY) !== true) {
            throw new \RuntimeException(__('notification.plugin_zip_not_zip'));
        }

        try {
            $pharName = $folderName.'.phar';
            $this->assertSafeEntries($zip, $pharName);

            if (! mkdir($stagingDir, 0700, true) && ! is_dir($stagingDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $stagingDir));
            }

            if (! $zip->extractTo($stagingDir)) {
                throw new \RuntimeException(__('notification.plugin_zip_cant_extract'));
            }
        } finally {
            $zip->close();
        }

        if (! self::hasValidPharSignature($stagingDir.DIRECTORY_SEPARATOR.$pharName)) {
            throw new \RuntimeException('Plugin archive failed signature verification');
        }
    }

    /**
     * Refuses an archive with an unsafe entry (absolute path, `..` segment, backslash, NUL), too
     * many entries, too large an uncompressed size, or without the expected phar at its root.
     *
     * @param  \ZipArchive  $zip  The opened archive.
     * @param  string  $pharName  The phar file the archive must contain at its root.
     *
     * @throws \RuntimeException When the archive is refused.
     */
    public function assertSafeEntries(\ZipArchive $zip, string $pharName): void
    {
        if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ENTRIES) {
            throw new \RuntimeException('Plugin archive has an unexpected number of entries');
        }

        $totalBytes = 0;
        $hasPhar = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            if ($stat === false) {
                throw new \RuntimeException('Plugin archive entry could not be read');
            }

            $entryName = (string) $stat['name'];
            if (! self::isSafeEntryName($entryName)) {
                throw new \RuntimeException('Plugin archive contains an unsafe path');
            }

            $totalBytes += (int) $stat['size'];
            if ($totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                throw new \RuntimeException('Plugin archive is too large');
            }

            if ($entryName === $pharName) {
                $hasPhar = true;
            }
        }

        if (! $hasPhar) {
            throw new \RuntimeException('Plugin archive does not contain '.$pharName);
        }
    }

    /**
     * True for a relative entry path that stays inside the extraction directory.
     *
     * @param  string  $entryName  The entry name as stored in the archive.
     */
    public static function isSafeEntryName(string $entryName): bool
    {
        if ($entryName === '' || str_contains($entryName, "\0") || str_contains($entryName, '\\')) {
            return false;
        }

        // Absolute paths and Windows drive letters.
        if (str_starts_with($entryName, '/') || preg_match('/^[A-Za-z]:/', $entryName) === 1) {
            return false;
        }

        foreach (explode('/', $entryName) as $segment) {
            if ($segment === '..') {
                return false;
            }
        }

        return true;
    }

    /**
     * True when the phar at $pharPath carries a signature that matches its content.
     *
     * Hash signatures (MD5/SHA1/SHA256/SHA512) are recomputed; OpenSSL signatures are verified
     * against the `{phar}.pubkey` file next to it, as the phar extension does when loading it.
     * Unsigned or unreadable files are refused.
     *
     * @param  string  $pharPath  The phar file.
     */
    public static function hasValidPharSignature(string $pharPath): bool
    {
        if (! is_file($pharPath)) {
            return false;
        }

        $content = file_get_contents($pharPath);
        if ($content === false || strlen($content) < 12 || substr($content, -4) !== 'GBMB') {
            return false;
        }

        $flags = unpack('V', substr($content, -8, 4))[1];

        if (isset(self::HASH_SIGNATURES[$flags])) {
            [$algorithm, $signatureLength] = self::HASH_SIGNATURES[$flags];
            $signedLength = strlen($content) - 8 - $signatureLength;
            if ($signedLength <= 0) {
                return false;
            }

            $signature = substr($content, $signedLength, $signatureLength);

            return hash_equals(hash($algorithm, substr($content, 0, $signedLength), true), $signature);
        }

        if (isset(self::OPENSSL_SIGNATURES[$flags])) {
            $signatureLength = unpack('V', substr($content, -12, 4))[1];
            $signedLength = strlen($content) - 12 - $signatureLength;
            if ($signatureLength <= 0 || $signedLength <= 0) {
                return false;
            }

            $publicKey = is_file($pharPath.'.pubkey') ? file_get_contents($pharPath.'.pubkey') : false;
            if ($publicKey === false || $publicKey === '') {
                return false;
            }

            $signature = substr($content, $signedLength, $signatureLength);

            return openssl_verify(substr($content, 0, $signedLength), $signature, $publicKey, self::OPENSSL_SIGNATURES[$flags]) === 1;
        }

        return false;
    }
}
