<?php

namespace LasseRafn\InitialAvatarGenerator\Translator;

interface Base
{
    /**
     * Translate words to english.
     *
     * @param string $words
     *
     * @return mixed
     */
    public function translate($words);

    /**
     * Get the source language of translator.
     *
     * @return string
     */
    public function getSourceLanguage();
}
