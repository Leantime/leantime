<?php

namespace LasseRafn\InitialAvatarGenerator\Translator;

class En implements Base
{
    /**
     * @inheritdoc
     */
    public function translate($words)
    {
        return $words;
    }

    /**
     * @inheritdoc
     */
    public function getSourceLanguage()
    {
        return 'en';
    }
}
