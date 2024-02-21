<?php

namespace LasseRafn\InitialAvatarGenerator\Translator;

use Overtrue\Pinyin\Pinyin;

class ZhCN implements Base
{
    /**
     * Inherent instance of zh-CN translator.
     *
     * @var Pinyin
     */
    protected $inherent;

    /**
     * ZhCN constructor, set the instance of PinYin.
     */
    public function __construct()
    {
        $this->inherent = new Pinyin();
    }

    /**
     * @inheritdoc
     */
    public function translate($words)
    {
        return implode(' ', $this->inherent->name($words));
    }

    /**
     * @inheritdoc
     */
    public function getSourceLanguage()
    {
        return 'zh-CN';
    }
}
