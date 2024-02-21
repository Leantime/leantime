<h1 align="center">Pinyin</h1>

<p align="center">

[![Build Status](https://travis-ci.org/overtrue/pinyin.svg?branch=master)](https://travis-ci.org/overtrue/pinyin)
[![Latest Stable Version](https://poser.pugx.org/overtrue/pinyin/v/stable.svg)](https://packagist.org/packages/overtrue/pinyin) [![Total Downloads](https://poser.pugx.org/overtrue/pinyin/downloads.svg)](https://packagist.org/packages/overtrue/pinyin) [![Latest Unstable Version](https://poser.pugx.org/overtrue/pinyin/v/unstable.svg)](https://packagist.org/packages/overtrue/pinyin) [![License](https://poser.pugx.org/overtrue/pinyin/license.svg)](https://packagist.org/packages/overtrue/pinyin)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fovertrue%2Fpinyin.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fovertrue%2Fpinyin?ref=badge_shield)

</p>

:cn: 基于 [CC-CEDICT](http://cc-cedict.org/wiki/) 词典的中文转拼音工具，更准确的支持多音字的汉字转拼音解决方案。


## 安装

使用 Composer 安装:

```
$ composer require "overtrue/pinyin:~4.0"
```

## 使用

可选转换方案：

- 内存型，适用于服务器内存空间较富余，优点：转换快
- 小内存型(默认)，适用于内存比较紧张的环境，优点：占用内存小，转换不如内存型快
- I/O型，适用于虚拟机，内存限制比较严格环境。优点：非常微小内存消耗。缺点：转换慢，不如内存型转换快,php >= 5.5

## 可用选项：

|      选项      | 描述                                                |
| -------------  | ---------------------------------------------------|
| `PINYIN_TONE`  | UNICODE 式音调：`měi hǎo`                    |
| `PINYIN_ASCII_TONE`  | 带数字式音调：  `mei3 hao3`                    |
| `PINYIN_NO_TONE`    |  无音调：`mei hao` | 
| `PINYIN_KEEP_NUMBER`    | 保留数字  | 
| `PINYIN_KEEP_ENGLISH`   | 保留英文   | 
| `PINYIN_KEEP_PUNCTUATION`   |  保留标点  | 
| `PINYIN_UMLAUT_V` | 使用 `v` 代替 `yu`, 例如：吕 `lyu` 将会转为 `lv` |

### 拼音数组

```php
use Overtrue\Pinyin\Pinyin;

// 小内存型
$pinyin = new Pinyin(); // 默认
// 内存型
// $pinyin = new Pinyin('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
// I/O型
// $pinyin = new Pinyin('\\Overtrue\\Pinyin\\GeneratorFileDictLoader');

$pinyin->convert('带着希望去旅行，比到达终点更美好');
// ["dai", "zhe", "xi", "wang", "qu", "lyu", "xing", "bi", "dao", "da", "zhong", "dian", "geng", "mei", "hao"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_TONE);
// ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_ASCII_TONE);
//["dai4","zhe","xi1","wang4","qu4","lyu3","xing2","bi3","dao4","da2","zhong1","dian3","geng4","mei3","hao3"]
```

- 小内存型: 将字典分片载入内存
- 内存型: 将所有字典预先载入内存
- I/O型: 不载入内存，将字典使用文件流打开逐行遍历并运用php5.5生成器(yield)特性分配单行内存


### 生成用于链接的拼音字符串

```php
$pinyin->permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lyu-xing
$pinyin->permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lyu.xing
```

### 获取首字符字符串

```php
$pinyin->abbr('带着希望去旅行'); // dzxwqlx
$pinyin->abbr('带着希望去旅行', '-'); // d-z-x-w-q-l-x

$pinyin->abbr('你好2018！', PINYIN_KEEP_NUMBER); // nh2018
$pinyin->abbr('Happy New Year! 2018！', PINYIN_KEEP_ENGLISH); // HNY2018
```

### 翻译整段文字为拼音

将会保留中文字符：`，。 ！ ？ ： “ ” ‘ ’` 并替换为对应的英文符号。

```php
$pinyin->sentence('带着希望去旅行，比到达终点更美好！');
// dai zhe xi wang qu lyu xing, bi dao da zhong dian geng mei hao!

$pinyin->sentence('带着希望去旅行，比到达终点更美好！', PINYIN_TONE);
// dài zhe xī wàng qù lǚ xíng, bǐ dào dá zhōng diǎn gèng měi hǎo!
```

### 翻译姓名

姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 `dan`，而作为姓的时候读 `shan`。

```php
$pinyin->name('单某某'); // ['shan', 'mou', 'mou']
$pinyin->name('单某某', PINYIN_TONE); // ["shàn","mǒu","mǒu"]
```

更多使用请参考 [测试用例](https://github.com/overtrue/pinyin/blob/master/tests/AbstractDictLoaderTestCase.php)。

## 在 Laravel 中使用

独立的包在这里：[overtrue/laravel-pinyin](https://github.com/overtrue/laravel-pinyin)

## Contribution
欢迎提意见及完善补充词库 [`overtrue/pinyin-dictionary-maker`](https://github.com/overtrue/pinyin-dictionary-maker/tree/master/patches) :kiss:

## 参考

- [详细参考资料](https://github.com/overtrue/pinyin-resources)

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

# License

[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fovertrue%2Fpinyin.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Fovertrue%2Fpinyin?ref=badge_large)
