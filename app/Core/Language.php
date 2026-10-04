<?php

namespace Leantime\Core;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Either takes the translation from ini_array or the default
 *
 * @param  string  $index  - The index of the translated string.
 * @param  mixed  $default  - The default value to return if the index is not found.
 * @return string - The translated string or the default value if the index is not found.
 */
class Language
{
    use DispatchesEvents;

    /**
     * @var string
     *
     * @static
     *
     * @final
     */
    private const DEFAULT_LANG_FOLDER = APP_ROOT.'/app/Language/';

    /**
     * @var string
     *
     * @static
     *
     * @final
     */
    private const CUSTOM_LANG_FOLDER = APP_ROOT.'/custom/Language/';

    /**
     * @static
     *
     * @final
     */
    private string $language = 'en-US';

    /**
     * @static
     *
     * @final
     */
    public array $ini_array;

    /**
     * @static
     *
     * @final
     */
    public array $ini_array_fallback;

    /**
     * @var array|bool Language list keyed by code, or false when no languagelist.ini could be read
     *
     * @static
     *
     * @final
     */
    public mixed $langlist;

    /**
     * @var bool - debug value. Will highlight untranslated text
     *
     * @static
     *
     * @final
     */
    private bool $alert = false;

    public Environment $config;

    public IncomingRequest $request;

    /**
     * Constructor method for initializing an instance of the class.
     */
    public function __construct()
    {

        $this->config = app('config');
        $this->request = app('request');

        // Get list of available languages
        $this->langlist = $this->getLanguageList();

        $lang = $this->getCurrentLanguage();
        $this->readIni();
    }

    /**
     * Set the language for the application.
     *
     * @param  string  $lang  The language code to be set.
     * @return bool True if the language is valid and successfully set, False otherwise.
     */
    public function setLanguage(string $lang): bool
    {
        if (! $this->isValidLanguage($lang)) {
            return false;
        }

        $this->language = $lang;

        session(['usersettings.language' => $lang]);

        if ((! isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) && ! $this->request->isApiOrCronRequest()) {
            $isAPIRequest = $this->request->isApiOrCronRequest();

            EventDispatcher::addFilterListener(
                'leantime.core.http.httpkernel.handle.beforeSendResponse',
                fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                    Cookie::create('language')
                        ->withValue($lang)
                        ->withExpires(time() + 60 * 60 * 24 * 30)
                        ->withPath(Str::finish($this->config->appDir, '/'))
                        ->withSameSite('lax')
                ))
            );
        }

        $this->readIni();

        return true;
    }

    /**
     * Get the currently selected language.
     *
     * Candidates are tried in order (user setting, language cookie, company setting, browser
     * language) and only a code from the list of available languages is accepted. The value
     * ends up in a file path when the language files are read, so anything else — e.g. a
     * tampered cookie — is ignored and the configured default language is used instead.
     *
     * @return string The currently selected language.
     */
    public function getCurrentLanguage(): string
    {
        $browserLanguage = str_replace('_', '-', substr((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''), 0, 2));

        $candidates = [
            session('usersettings.language'),
            $_COOKIE['language'] ?? null,
            session('companysettings.language'),
            $browserLanguage,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && $this->isValidLanguage($candidate)) {
                $this->language = $candidate;

                return $this->language;
            }
        }

        $configuredLanguage = (string) $this->config->language;
        $this->language = $this->isValidLanguage($configuredLanguage) ? $configuredLanguage : 'en-US';

        return $this->language;
    }

    /**
     * Check if a given language code is valid.
     *
     * @param  string  $langCode  The language code to check.
     * @return bool True if the language code is valid, false otherwise.
     */
    public function isValidLanguage(string $langCode): bool
    {
        return is_array($this->langlist) && isset($this->langlist[$langCode]);
    }

    /**
     * Cache key for a language's merged strings.
     *
     * Includes the app version: the installation cache is only flushed by database updates, so a
     * release that adds strings without a migration kept serving the old cached file and new keys
     * rendered raw (e.g. "tabs.history") until the cache was cleared by hand.
     */
    private function languageCacheKey(): string
    {
        return 'languages.lang_'.$this->language.'_'.app(\Leantime\Core\Configuration\AppSettings::class)->appVersion;
    }

    /**
     * Read and load the language resources from the ini files.
     *
     * @return array The array of language resources loaded from the ini files.
     *
     * @throws Exception If the default english language file en-US.ini cannot be found.
     */
    public function readIni(): array
    {
        if (@Cache::store('installation')->has($this->languageCacheKey())) {
            $this->ini_array = self::dispatchFilter(
                'language_resources',
                Cache::store('installation')->get($this->languageCacheKey()),
                [
                    'language' => $this->language,
                ]
            ) ?? Cache::store('installation')->get($this->languageCacheKey());

            Cache::store('installation')->set($this->languageCacheKey(), $this->ini_array);

            return $this->ini_array;
        }

        // Default to english US
        if (! file_exists(self::DEFAULT_LANG_FOLDER.'/en-US.ini')) {
            throw new Exception('Cannot find default english language file en-US.ini');
        }

        $mainLanguageArray = parse_ini_file(self::DEFAULT_LANG_FOLDER.'en-US.ini', false, INI_SCANNER_RAW);

        foreach (
            $languageFiles = self::dispatchFilter('language_files', [
                // Complement english with english customization
                self::CUSTOM_LANG_FOLDER.'en-US.ini' => false,

                // Overwrite english language by non-english language
                self::DEFAULT_LANG_FOLDER.$this->language.'.ini' => true,

                // Overwrite with non-engish customizations
                self::CUSTOM_LANG_FOLDER.$this->language.'.ini' => true,
            ], ['language' => $this->language]) as $language_file => $isForeign
        ) {
            $mainLanguageArray = $this->includeOverrides($mainLanguageArray, $language_file, $isForeign);
        }

        $this->ini_array = self::dispatchFilter(
            'language_resources',
            $mainLanguageArray,
            [
                'language' => $this->language,
            ]
        );

        Cache::store('installation')->set($this->languageCacheKey(), $this->ini_array);

        return $this->ini_array;
    }

    /**
     * Include language overrides from an ini file.
     *
     * @param  array  $language  The original language array.
     * @param  string  $filepath  The path to the ini file.
     * @param  bool  $foreignLanguage  Whether the language is foreign or not. Defaults to false.
     * @return array The modified language array.
     *
     * @throws Exception If the ini file cannot be parsed.
     */
    protected function includeOverrides(array $language, string $filepath, bool $foreignLanguage = false): array
    {
        if ($foreignLanguage && $this->language == 'en-US') {
            return $language;
        }

        if (! file_exists($filepath)) {
            return $language;
        }

        $ini_overrides = parse_ini_file($filepath, false, INI_SCANNER_RAW);

        if (! is_array($ini_overrides)) {
            throw new Exception("Could not parse ini file $filepath");
        }

        foreach ($ini_overrides as $languageKey => $languageValue) {
            $language[$languageKey] = $languageValue;
        }

        return $language;
    }

    /**
     * Get the list of languages.
     *
     * Retrieves the list of languages from a cache or from INI files if the cache is not available.
     * The list of languages is stored in an associative array where the keys represent the language codes
     * and the values represent the language names.
     *
     * @return bool|array The list of languages as an associative array, or false if the list is empty or cannot be retrieved.
     */
    public function getLanguageList(): bool|array
    {
        if (Cache::store('installation')->has('languages.langlist')) {
            return Cache::store('installation')->get('languages.langlist');
        }

        $langlist = false;
        if (file_exists(self::DEFAULT_LANG_FOLDER.'/languagelist.ini')) {
            $langlist = parse_ini_file(
                self::DEFAULT_LANG_FOLDER.'/languagelist.ini',
                false,
                INI_SCANNER_RAW
            );
        }

        if (file_exists(self::CUSTOM_LANG_FOLDER.'/languagelist.ini')) {
            $langlist = parse_ini_file(
                self::CUSTOM_LANG_FOLDER.'/languagelist.ini',
                false,
                INI_SCANNER_RAW
            );
        }

        $parsedLangList = self::dispatchFilter('languages', $langlist);
        Cache::store('installation')->set('languages.langlist', $parsedLangList);

        return $parsedLangList;
    }

    /**
     * Get a translated string or a default value if the index is not found.
     *
     * @param  string  $index  The index of the translated string.
     * @param  string  $default  The default value to return if the index is not found. Defaults to an empty string.
     * @return string The translated string or the default value if the index is not found.
     */
    public function __(string $index, string $default = ''): string
    {
        // If index cannot be found return default or original string
        if (! isset($this->ini_array[$index])) {
            if (! empty($default)) {
                return $default;
            }

            if ($this->alert) {
                return sprintf('<span style="color: red; font-weight:bold;">%s</span>', $index);
            }

            return $index;
        }

        $returnValue = match (trim($index)) {
            'language.dateformat' => session('usersettings.date_format') ?? $this->ini_array['language.dateformat'],
            'language.timeformat' => session('usersettings.time_format') ?? $this->ini_array['language.timeformat'],
            default => $this->ini_array[$index],
        };

        return (string) $returnValue;
    }

    public function mergeLanguageArray($newLanguageArray)
    {

        if (is_array($newLanguageArray)) {
            $this->ini_array = array_merge($this->ini_array, $newLanguageArray);
        }
    }

    public function get(string $index, $default = '', $locale = '')
    {
        $contentReplacement = '';
        if (is_array($default) && count($default) > 0) {
            $contentReplacement = $default[0];
        }

        return $this->__($index, $contentReplacement);
    }
}
