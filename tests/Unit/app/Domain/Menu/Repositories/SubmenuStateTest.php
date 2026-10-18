<?php

namespace Unit\app\Domain\Menu\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Core\UI\UiState;
use Leantime\Domain\Menu\Repositories\Menu;
use PDO;
use ReflectionClass;
use Unit\TestCase;

/**
 * Api.Api.setSubmenuState (JSON-RPC, which never persists the session) saves through
 * Menu::setSubmenuState. It must write UiState rows, not the session, so the next page load
 * shows the new state.
 */
class SubmenuStateTest extends TestCase
{
    public function test_a_state_saved_over_rpc_is_read_back_on_the_next_request(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $db->getSchemaBuilder()->create('zp_settings', function ($table) {
            $table->string('key', 175)->primary();
            $table->text('value')->nullable();
        });
        session(['userdata.id' => 7]);
        session()->forget('usersettings');

        $menu = (new ReflectionClass(Menu::class))->newInstanceWithoutConstructor();

        app()->instance(UiState::class, new UiState($db));
        $menu->setSubmenuState('accordion_content-myProjectsHub-favorites', 'closed');
        $this->assertNull(session('usersettings.submenuToggle'));

        // Next request: a fresh store, nothing in the session.
        app()->instance(UiState::class, new UiState($db));
        $this->assertSame('closed', $menu->getSubmenuState('accordion_content-myProjectsHub-favorites'));
        $this->assertFalse($menu->getSubmenuState('never-saved'));
    }
}
