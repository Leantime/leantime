<?php

namespace Leantime\Core;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\ApiRequest;
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
     * @var array
     *
     * @static
     *
     * @final
     */
    public mixed $langlist;

    /**
     * @var array|bool - debug value. Will highlight untranslated text
     *
     * @static
     *
     * @final
     */
    private array|bool $alert = false;

    public Environment $config;

    public IncomingRequest $request;

    /**
     * Constructor method for initializing an instance of the class.
     *
     * @param  Environment  $config  The configuration environment.
     * @param  ApiRequest  $request  The API request object.
     */
    public function __construct() {

        $this->config = app('config');
        $this->request = app('request');

        //Get list of available languages
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
                        ->withSameSite('Lax')
                ))
            );
        }

        $this->readIni();

        return true;
    }

    /**
     * Get the currently selected language.
     *
     * @return string The currently selected language.
     */
    public function getCurrentLanguage(): string
    {

        if (session()->has('usersettings.language')) {
            $this->language = session('usersettings.language');

            return $this->language;
        }

        if (isset($_COOKIE['language'])) {
            $this->language = $_COOKIE['language'];

            return $this->language;
        }

        if (session('companysettings.language')) {
            $this->language = session('companysettings.language');

            return $this->language;
        }

        $language = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        $language = str_replace("_", "-", $language);
        if($language && $this->isValidLanguage($language)) {
            return $this->language;
        }

        $this->language = $this->config->language;

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
        return isset($this->langlist[$langCode]);
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
        if (Cache::store('installation')->has('languages.lang_'.$this->language)) {

            $this->ini_array = self::dispatchFilter(
                'language_resources',
                Cache::store('installation')->get('languages.lang_'.$this->language),
                [
                    'language' => $this->language,
                ]
            ) ?? Cache::store('installation')->get('languages.lang_'.$this->language);

            Cache::store('installation')->set('languages.lang_'.$this->language, $this->ini_array);

            return $this->ini_array;
        }

        // Default to english US
        if (! file_exists(static::DEFAULT_LANG_FOLDER.'/en-US.ini')) {
            throw new Exception('Cannot find default english language file en-US.ini');
        }

        $mainLanguageArray = parse_ini_file(static::DEFAULT_LANG_FOLDER.'en-US.ini', false, INI_SCANNER_RAW);

        foreach ($languageFiles = self::dispatchFilter('language_files', [
            // Complement english with english customization
            static::CUSTOM_LANG_FOLDER.'en-US.ini' => false,

            // Overwrite english language by non-english language
            static::DEFAULT_LANG_FOLDER.$this->language.'.ini' => true,

            // Overwrite with non-engish customizations
            static::CUSTOM_LANG_FOLDER.$this->language.'.ini' => true,
        ], ['language' => $this->language]) as $language_file => $isForeign) {
            $mainLanguageArray = $this->includeOverrides($mainLanguageArray, $language_file, $isForeign);
        }

        $this->ini_array = self::dispatchFilter(
            'language_resources',
            $mainLanguageArray,
            [
                'language' => $this->language,
            ]
        );

        Cache::store('installation')->set('languages.lang_'.$this->language, $this->ini_array);

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
        if (file_exists(static::DEFAULT_LANG_FOLDER.'/languagelist.ini')) {
            $langlist = parse_ini_file(
                static::DEFAULT_LANG_FOLDER.'/languagelist.ini',
                false,
                INI_SCANNER_RAW
            );
        }

        if (file_exists(static::CUSTOM_LANG_FOLDER.'/languagelist.ini')) {
            $langlist = parse_ini_file(
                static::CUSTOM_LANG_FOLDER.'/languagelist.ini',
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
        //If index cannot be found return default or original string
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

    public function get(string $index, $default = '', $locale = '')
    {
        $contentReplacement = '';
        if (is_array($default) && count($default) > 0) {
            $contentReplacement = $default[0];
        }

        return $this->__($index, $contentReplacement);
    }
}
