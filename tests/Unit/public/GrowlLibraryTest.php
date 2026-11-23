<?php

namespace Unit\public;

use Unit\TestCase;

/**
 * jquery.growl.js builds its toast as an HTML string. Messages are often server error text
 * (JSON-RPC errors that name users and projects), so the library shows them as plain text
 * unless the caller passes html: true, and reduces the style to a class-safe token.
 *
 * Runs the real library in Node with a stub jQuery that records the HTML it is handed.
 * Skipped when Node isn't installed.
 */
class GrowlLibraryTest extends TestCase
{
    private const SHIM = <<<'JS'
        const captured = [];
        const chain = new Proxy(function () {}, {
            get: (target, prop) => (prop === 'length' ? 0 : chain),
            apply: () => chain,
        });
        global.setTimeout = () => 0;
        global.jQuery = function (arg) {
            if (typeof arg === 'string' && arg.includes('growl-message')) captured.push(arg);
            return chain;
        };
        global.jQuery.extend = (...objects) => Object.assign(...objects);
        require(process.argv[1]);
        global.jQuery.growl(JSON.parse(process.argv[2]));
        process.stdout.write(captured[0] || '');
        JS;

    private function render(array $options): string
    {
        if (trim((string) shell_exec('command -v node')) === '') {
            $this->markTestSkipped('Node is not installed.');
        }

        $library = realpath(__DIR__.'/../../../public/assets/js/libs/jquery.growl.js');
        $command = 'node -e '.escapeshellarg(self::SHIM).' '.escapeshellarg($library).' '.escapeshellarg(json_encode($options));

        $html = (string) shell_exec($command.' 2>&1');
        $this->assertStringContainsString('growl-message', $html, $html);

        return $html;
    }

    public function test_a_message_is_shown_as_text_by_default(): void
    {
        $html = $this->render(['message' => 'Ops <img src=x onerror=alert(1)> can\'t', 'style' => 'error']);

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt; can&#39;t', $html);
    }

    public function test_a_style_cannot_add_attributes(): void
    {
        $html = $this->render(['message' => 'Saved', 'style' => "success' onmouseover='alert(1)' data-x='"]);

        $this->assertStringNotContainsString("onmouseover='", $html);
        $this->assertStringContainsString("class='growl growl-successonmouseoveralert1data-x growl-medium'", $html);
    }

    public function test_html_true_keeps_markup_for_sanitized_server_notifications(): void
    {
        $html = $this->render(['message' => 'Copied,<br/><a href="https://example.com/p/1">open it</a>', 'style' => 'success', 'html' => true]);

        $this->assertStringContainsString('<a href="https://example.com/p/1">open it</a>', $html);
    }
}
