# Google Calendar Plugin - Technical Specification

## Overview

Native Google Calendar integration for Leantime using OAuth 2.0. Allows users to connect their Google Calendar account and sync events bidirectionally with Leantime.

**Scope:** This is a standalone plugin that does NOT touch OIDC (login) or CalDAV (different protocol).

---

## User Experience

### Connection Flow
1. User goes to Settings → Calendar → Google Calendar
2. Clicks "Connect Google Calendar"
3. Redirected to Google consent screen
4. Grants calendar permissions
5. Redirected back to Leantime
6. Selects which calendars to sync
7. Done - events appear in Leantime calendar

### Ongoing Sync
- Pull: Google events appear in Leantime calendar view
- Push: Leantime events (optionally) sync to Google
- Background sync via cron job
- Manual sync button in settings

---

## Prerequisites

### Google Cloud Console Setup (Manual - Done Once)

1. Go to https://console.cloud.google.com/
2. Create new project or select existing
3. Enable Google Calendar API:
   - APIs & Services → Library → Search "Google Calendar API" → Enable
4. Create OAuth credentials:
   - APIs & Services → Credentials → Create Credentials → OAuth client ID
   - Application type: Web application
   - Name: "Leantime Calendar Integration"
   - Authorized redirect URIs: `https://your-leantime.com/googlecalendar/callback`
5. Download or copy:
   - Client ID
   - Client Secret

### Environment Variables
```env
GOOGLE_CALENDAR_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CALENDAR_CLIENT_SECRET=your-client-secret
GOOGLE_CALENDAR_REDIRECT_URI=${APP_URL}/googlecalendar/callback
```

---

## Plugin Structure

```
app/Plugins/GoogleCalendar/
├── Controllers/
│   ├── Connect.php          # Initiates OAuth flow
│   ├── Callback.php         # Handles OAuth callback
│   ├── Settings.php         # Account management UI
│   ├── Sync.php             # Manual sync trigger
│   └── Disconnect.php       # Remove account
├── Services/
│   ├── GoogleCalendar.php   # Main service (API calls, sync logic)
│   ├── GoogleAuth.php       # OAuth token management
│   └── EventMapper.php      # Maps Google ↔ Leantime events
├── Repositories/
│   └── GoogleCalendarAccounts.php  # Database operations
├── Models/
│   ├── GoogleCalendarAccount.php   # Account entity
│   └── GoogleCalendarEvent.php     # Synced event entity
├── Migrations/
│   └── CreateGoogleCalendarTables.php
├── Templates/
│   ├── settings.blade.php   # Settings page
│   ├── connect.blade.php    # Pre-connect info
│   └── partials/
│       └── accountCard.blade.php
├── Language/
│   └── en-US.ini
├── Commands/
│   └── SyncCalendars.php    # Artisan command for cron
├── register.php             # Plugin registration & hooks
├── composer.json            # Dependencies
└── index.php                # Plugin metadata
```

---

## Database Schema

### Table: `zp_google_calendar_accounts`

```sql
CREATE TABLE `zp_google_calendar_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `userId` INT UNSIGNED NOT NULL,
    `googleEmail` VARCHAR(255) NOT NULL,
    `accessToken` TEXT NOT NULL,
    `refreshToken` TEXT NOT NULL,
    `tokenExpiresAt` DATETIME NOT NULL,
    `selectedCalendars` JSON DEFAULT NULL,  -- ["primary", "cal_id_2"]
    `syncDirection` ENUM('pull', 'push', 'both') DEFAULT 'pull',
    `syncEnabled` TINYINT(1) DEFAULT 1,
    `lastSyncAt` DATETIME DEFAULT NULL,
    `lastSyncStatus` VARCHAR(50) DEFAULT NULL,  -- 'success', 'error', 'partial'
    `lastSyncError` TEXT DEFAULT NULL,
    `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_userId` (`userId`),
    INDEX `idx_syncEnabled` (`syncEnabled`),
    UNIQUE KEY `unique_user_email` (`userId`, `googleEmail`),
    FOREIGN KEY (`userId`) REFERENCES `zp_user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `zp_google_calendar_events`

```sql
CREATE TABLE `zp_google_calendar_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `accountId` INT UNSIGNED NOT NULL,
    `googleEventId` VARCHAR(255) NOT NULL,
    `googleCalendarId` VARCHAR(255) NOT NULL,
    `leantimeEventId` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `startDate` DATETIME NOT NULL,
    `endDate` DATETIME NOT NULL,
    `allDay` TINYINT(1) DEFAULT 0,
    `location` VARCHAR(500) DEFAULT NULL,
    `googleUpdatedAt` DATETIME NOT NULL,
    `syncedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `syncDirection` ENUM('from_google', 'to_google') NOT NULL,
    
    INDEX `idx_accountId` (`accountId`),
    INDEX `idx_googleEventId` (`googleEventId`),
    INDEX `idx_leantimeEventId` (`leantimeEventId`),
    UNIQUE KEY `unique_google_event` (`accountId`, `googleEventId`),
    FOREIGN KEY (`accountId`) REFERENCES `zp_google_calendar_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Core Files Implementation

### index.php
```php
<?php

return [
    'name' => 'GoogleCalendar',
    'description' => 'Native Google Calendar integration with OAuth 2.0',
    'version' => '1.0.0',
    'author' => 'Leantime',
    'authorUrl' => 'https://leantime.io',
    'license' => 'AGPL-3.0',
    'minVersion' => '3.0.0',
    'premium' => false,
];
```

### composer.json
```json
{
    "name": "leantime/googlecalendar",
    "description": "Google Calendar integration for Leantime",
    "require": {
        "google/apiclient": "^2.15"
    },
    "autoload": {
        "psr-4": {
            "Leantime\\Plugins\\GoogleCalendar\\": ""
        }
    }
}
```

### register.php
```php
<?php

namespace Leantime\Plugins\GoogleCalendar;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Plugins\GoogleCalendar\Services\GoogleCalendar;

class Register
{
    public function __construct(
        private GoogleCalendar $googleCalendarService,
    ) {}

    /**
     * Register plugin routes
     */
    public function registerRoutes(): void
    {
        // Routes are auto-discovered from Controllers
    }

    /**
     * Register event listeners
     */
    public function registerEvents(): void
    {
        // Hook into calendar event retrieval
        Events::add_filter_listener(
            'leantime.domain.calendar.services.calendar.getCalendar.result',
            function (array $events, array $params) {
                if (!session()->exists('userdata.id')) {
                    return $events;
                }
                
                $userId = session('userdata.id');
                $googleEvents = $this->googleCalendarService->getEventsForUser($userId);
                
                return array_merge($events, $googleEvents);
            }
        );

        // Hook into external calendar events
        Events::add_filter_listener(
            'leantime.domain.calendar.services.calendar.getExternalCalendarEvents.result',
            function (array $events, array $params) {
                if (!session()->exists('userdata.id')) {
                    return $events;
                }
                
                $userId = session('userdata.id');
                $googleEvents = $this->googleCalendarService->getEventsForUser($userId);
                
                return array_merge($events, $googleEvents);
            }
        );
    }

    /**
     * Register menu items
     */
    public function registerMenuItems(): void
    {
        Events::add_filter_listener(
            'leantime.domain.menu.services.menu.getSettingsMenu.result',
            function (array $menuItems) {
                $menuItems['googlecalendar'] = [
                    'label' => 'Google Calendar',
                    'icon' => 'fa-google',
                    'url' => '/googlecalendar/settings',
                    'section' => 'integrations',
                ];
                return $menuItems;
            }
        );
    }

    /**
     * Register scheduled tasks
     */
    public function registerScheduledTasks(Schedule $schedule): void
    {
        // Sync every 15 minutes
        $schedule->command('googlecalendar:sync')
            ->everyFifteenMinutes()
            ->withoutOverlapping();
    }
}
```

---

## OAuth Flow

### Step 1: Connect Controller
```php
<?php

namespace Leantime\Plugins\GoogleCalendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Plugins\GoogleCalendar\Services\GoogleAuth;
use Symfony\Component\HttpFoundation\Response;

class Connect extends Controller
{
    public function __construct(
        private GoogleAuth $googleAuth,
    ) {}

    public function get(): Response
    {
        // Generate OAuth URL and redirect
        $authUrl = $this->googleAuth->getAuthUrl();
        
        return $this->tpl->redirect($authUrl);
    }
}
```

### Step 2: Callback Controller
```php
<?php

namespace Leantime\Plugins\GoogleCalendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Plugins\GoogleCalendar\Services\GoogleAuth;
use Leantime\Plugins\GoogleCalendar\Repositories\GoogleCalendarAccounts;
use Symfony\Component\HttpFoundation\Response;

class Callback extends Controller
{
    public function __construct(
        private GoogleAuth $googleAuth,
        private GoogleCalendarAccounts $accountRepo,
    ) {}

    public function get(): Response
    {
        $code = $_GET['code'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($error) {
            $this->tpl->setNotification('Google Calendar connection was cancelled.', 'error');
            return $this->tpl->redirect('/googlecalendar/settings');
        }

        if (!$code) {
            $this->tpl->setNotification('Invalid callback. Please try again.', 'error');
            return $this->tpl->redirect('/googlecalendar/settings');
        }

        try {
            // Exchange code for tokens
            $tokens = $this->googleAuth->exchangeCode($code);
            
            // Get user info from Google
            $googleUser = $this->googleAuth->getUserInfo($tokens['access_token']);
            
            // Store account
            $this->accountRepo->createOrUpdate([
                'userId' => session('userdata.id'),
                'googleEmail' => $googleUser['email'],
                'accessToken' => $tokens['access_token'],
                'refreshToken' => $tokens['refresh_token'],
                'tokenExpiresAt' => date('Y-m-d H:i:s', time() + $tokens['expires_in']),
            ]);

            $this->tpl->setNotification('Google Calendar connected successfully!', 'success');
            
        } catch (\Exception $e) {
            $this->tpl->setNotification('Failed to connect: ' . $e->getMessage(), 'error');
        }

        return $this->tpl->redirect('/googlecalendar/settings');
    }
}
```

### Step 3: GoogleAuth Service
```php
<?php

namespace Leantime\Plugins\GoogleCalendar\Services;

use Google\Client;
use Leantime\Core\Configuration\Environment;

class GoogleAuth
{
    private Client $client;

    public function __construct(
        private Environment $config,
    ) {
        $this->client = new Client();
        $this->client->setClientId($config->googleCalendarClientId);
        $this->client->setClientSecret($config->googleCalendarClientSecret);
        $this->client->setRedirectUri($config->googleCalendarRedirectUri);
        $this->client->setAccessType('offline');  // Get refresh token
        $this->client->setPrompt('consent');      // Force consent to get refresh token
        $this->client->addScope([
            'https://www.googleapis.com/auth/calendar.readonly',
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/userinfo.email',
        ]);
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function exchangeCode(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['error'])) {
            throw new \Exception($token['error_description'] ?? $token['error']);
        }

        return $token;
    }

    public function refreshToken(string $refreshToken): array
    {
        $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
        return $this->client->getAccessToken();
    }

    public function getUserInfo(string $accessToken): array
    {
        $this->client->setAccessToken($accessToken);
        $oauth2 = new \Google\Service\Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();
        
        return [
            'email' => $userInfo->getEmail(),
            'name' => $userInfo->getName(),
        ];
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
```

---

## Calendar Sync Service

### GoogleCalendar Service
```php
<?php

namespace Leantime\Plugins\GoogleCalendar\Services;

use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Leantime\Plugins\GoogleCalendar\Repositories\GoogleCalendarAccounts;
use Leantime\Plugins\GoogleCalendar\Services\GoogleAuth;
use Leantime\Plugins\GoogleCalendar\Services\EventMapper;

class GoogleCalendar
{
    public function __construct(
        private GoogleAuth $googleAuth,
        private GoogleCalendarAccounts $accountRepo,
        private EventMapper $eventMapper,
    ) {}

    /**
     * Get all Google Calendar events for a user (formatted for Leantime)
     */
    public function getEventsForUser(int $userId): array
    {
        $accounts = $this->accountRepo->getByUserId($userId);
        $allEvents = [];

        foreach ($accounts as $account) {
            if (!$account['syncEnabled']) {
                continue;
            }

            try {
                $events = $this->fetchEventsFromGoogle($account);
                $allEvents = array_merge($allEvents, $events);
            } catch (\Exception $e) {
                // Log error but continue with other accounts
                error_log("Google Calendar sync error for account {$account['id']}: " . $e->getMessage());
            }
        }

        return $allEvents;
    }

    /**
     * Fetch events from Google Calendar API
     */
    private function fetchEventsFromGoogle(array $account): array
    {
        // Refresh token if expired
        if (strtotime($account['tokenExpiresAt']) < time()) {
            $newTokens = $this->googleAuth->refreshToken($account['refreshToken']);
            $this->accountRepo->updateTokens($account['id'], $newTokens);
            $account['accessToken'] = $newTokens['access_token'];
        }

        // Initialize Google Calendar service
        $client = $this->googleAuth->getClient();
        $client->setAccessToken($account['accessToken']);
        $calendarService = new Calendar($client);

        // Get selected calendars or default to primary
        $calendarIds = json_decode($account['selectedCalendars'] ?? '["primary"]', true);
        
        $events = [];
        $timeMin = (new \DateTime('-1 month'))->format(\DateTime::RFC3339);
        $timeMax = (new \DateTime('+3 months'))->format(\DateTime::RFC3339);

        foreach ($calendarIds as $calendarId) {
            try {
                $googleEvents = $calendarService->events->listEvents($calendarId, [
                    'timeMin' => $timeMin,
                    'timeMax' => $timeMax,
                    'singleEvents' => true,
                    'orderBy' => 'startTime',
                    'maxResults' => 250,
                ]);

                foreach ($googleEvents->getItems() as $googleEvent) {
                    $events[] = $this->eventMapper->googleToLeantime($googleEvent, $account);
                }
            } catch (\Exception $e) {
                error_log("Error fetching calendar {$calendarId}: " . $e->getMessage());
            }
        }

        return $events;
    }

    /**
     * Sync a Leantime event to Google Calendar
     */
    public function pushEventToGoogle(int $userId, array $leantimeEvent): ?string
    {
        $account = $this->accountRepo->getPrimaryAccount($userId);
        
        if (!$account || $account['syncDirection'] === 'pull') {
            return null;
        }

        // Refresh token if needed
        if (strtotime($account['tokenExpiresAt']) < time()) {
            $newTokens = $this->googleAuth->refreshToken($account['refreshToken']);
            $this->accountRepo->updateTokens($account['id'], $newTokens);
            $account['accessToken'] = $newTokens['access_token'];
        }

        $client = $this->googleAuth->getClient();
        $client->setAccessToken($account['accessToken']);
        $calendarService = new Calendar($client);

        $googleEvent = $this->eventMapper->leantimeToGoogle($leantimeEvent);
        
        $createdEvent = $calendarService->events->insert('primary', $googleEvent);
        
        return $createdEvent->getId();
    }

    /**
     * Get user's available calendars from Google
     */
    public function getAvailableCalendars(int $accountId): array
    {
        $account = $this->accountRepo->getById($accountId);
        
        if (!$account) {
            return [];
        }

        // Refresh token if expired
        if (strtotime($account['tokenExpiresAt']) < time()) {
            $newTokens = $this->googleAuth->refreshToken($account['refreshToken']);
            $this->accountRepo->updateTokens($account['id'], $newTokens);
            $account['accessToken'] = $newTokens['access_token'];
        }

        $client = $this->googleAuth->getClient();
        $client->setAccessToken($account['accessToken']);
        $calendarService = new Calendar($client);

        $calendarList = $calendarService->calendarList->listCalendarList();
        
        $calendars = [];
        foreach ($calendarList->getItems() as $calendar) {
            $calendars[] = [
                'id' => $calendar->getId(),
                'summary' => $calendar->getSummary(),
                'primary' => $calendar->getPrimary() ?? false,
                'backgroundColor' => $calendar->getBackgroundColor(),
            ];
        }

        return $calendars;
    }
}
```

### EventMapper Service
```php
<?php

namespace Leantime\Plugins\GoogleCalendar\Services;

use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class EventMapper
{
    /**
     * Convert Google Calendar event to Leantime format
     */
    public function googleToLeantime(Event $googleEvent, array $account): array
    {
        $start = $googleEvent->getStart();
        $end = $googleEvent->getEnd();
        
        // Handle all-day events (date) vs timed events (dateTime)
        $allDay = !empty($start->getDate());
        
        if ($allDay) {
            $dateFrom = $start->getDate() . ' 00:00:00';
            $dateTo = $end->getDate() . ' 23:59:59';
        } else {
            $dateFrom = date('Y-m-d H:i:s', strtotime($start->getDateTime()));
            $dateTo = date('Y-m-d H:i:s', strtotime($end->getDateTime()));
        }

        return [
            'id' => 'google_' . $googleEvent->getId(),
            'title' => $googleEvent->getSummary() ?? '(No title)',
            'description' => $googleEvent->getDescription() ?? '',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'allDay' => $allDay,
            'eventType' => 'external',
            'source' => 'Google: ' . $account['googleEmail'],
            'color' => '#4285f4',  // Google blue
            'projectId' => null,
            'taskId' => null,
            'userId' => $account['userId'],
            'editable' => false,  // External events not editable in Leantime
        ];
    }

    /**
     * Convert Leantime event to Google Calendar format
     */
    public function leantimeToGoogle(array $leantimeEvent): Event
    {
        $event = new Event();
        $event->setSummary($leantimeEvent['title'] ?? $leantimeEvent['description']);
        $event->setDescription($leantimeEvent['description'] ?? '');

        $start = new EventDateTime();
        $end = new EventDateTime();

        if (!empty($leantimeEvent['allDay'])) {
            // All-day event
            $start->setDate(date('Y-m-d', strtotime($leantimeEvent['dateFrom'])));
            $end->setDate(date('Y-m-d', strtotime($leantimeEvent['dateTo'])));
        } else {
            // Timed event
            $start->setDateTime(date('c', strtotime($leantimeEvent['dateFrom'])));
            $end->setDateTime(date('c', strtotime($leantimeEvent['dateTo'])));
        }

        $event->setStart($start);
        $event->setEnd($end);

        return $event;
    }
}
```

---

## Settings UI

### Settings Controller
```php
<?php

namespace Leantime\Plugins\GoogleCalendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Plugins\GoogleCalendar\Repositories\GoogleCalendarAccounts;
use Leantime\Plugins\GoogleCalendar\Services\GoogleCalendar;
use Symfony\Component\HttpFoundation\Response;

class Settings extends Controller
{
    public function __construct(
        private GoogleCalendarAccounts $accountRepo,
        private GoogleCalendar $googleCalendarService,
    ) {}

    public function get(): Response
    {
        $userId = session('userdata.id');
        $accounts = $this->accountRepo->getByUserId($userId);

        // Get available calendars for each account
        foreach ($accounts as &$account) {
            $account['availableCalendars'] = $this->googleCalendarService->getAvailableCalendars($account['id']);
            $account['selectedCalendarsArray'] = json_decode($account['selectedCalendars'] ?? '[]', true);
        }

        $this->tpl->assign('accounts', $accounts);
        
        return $this->tpl->display('GoogleCalendar::settings');
    }

    public function post(): Response
    {
        $accountId = $_POST['accountId'] ?? null;
        $selectedCalendars = $_POST['selectedCalendars'] ?? [];
        $syncDirection = $_POST['syncDirection'] ?? 'pull';
        $syncEnabled = isset($_POST['syncEnabled']) ? 1 : 0;

        if ($accountId) {
            $this->accountRepo->update($accountId, [
                'selectedCalendars' => json_encode($selectedCalendars),
                'syncDirection' => $syncDirection,
                'syncEnabled' => $syncEnabled,
            ]);

            $this->tpl->setNotification('Settings saved successfully!', 'success');
        }

        return $this->tpl->redirect('/googlecalendar/settings');
    }
}
```

### settings.blade.php Template
```blade
@extends('layout.main')

@section('content')
<div class="maincontent">
    <div class="maincontentinner">
        <h1>Google Calendar Integration</h1>
        
        <div class="row">
            <div class="col-md-8">
                @if(empty($accounts))
                    <div class="alert alert-info">
                        <h4>Connect Your Google Calendar</h4>
                        <p>Connect your Google Calendar to see your events alongside your Leantime tasks and meetings.</p>
                        <a href="/googlecalendar/connect" class="btn btn-primary">
                            <i class="fa fa-google"></i> Connect Google Calendar
                        </a>
                    </div>
                @else
                    @foreach($accounts as $account)
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fa fa-google text-primary"></i>
                                    <strong>{{ $account['googleEmail'] }}</strong>
                                </div>
                                <a href="/googlecalendar/disconnect/{{ $account['id'] }}" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to disconnect this account?')">
                                    Disconnect
                                </a>
                            </div>
                            <div class="card-body">
                                <form method="post" action="/googlecalendar/settings">
                                    <input type="hidden" name="accountId" value="{{ $account['id'] }}">
                                    
                                    <div class="form-group">
                                        <label><strong>Calendars to Sync</strong></label>
                                        @foreach($account['availableCalendars'] as $calendar)
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="selectedCalendars[]" 
                                                       value="{{ $calendar['id'] }}"
                                                       id="cal_{{ md5($calendar['id']) }}"
                                                       {{ in_array($calendar['id'], $account['selectedCalendarsArray']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cal_{{ md5($calendar['id']) }}">
                                                    <span style="display: inline-block; width: 12px; height: 12px; background: {{ $calendar['backgroundColor'] }}; border-radius: 2px; margin-right: 5px;"></span>
                                                    {{ $calendar['summary'] }}
                                                    @if($calendar['primary']) <span class="badge badge-primary">Primary</span> @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Sync Direction</strong></label>
                                        <select name="syncDirection" class="form-control">
                                            <option value="pull" {{ $account['syncDirection'] == 'pull' ? 'selected' : '' }}>
                                                Pull only (Google → Leantime)
                                            </option>
                                            <option value="push" {{ $account['syncDirection'] == 'push' ? 'selected' : '' }}>
                                                Push only (Leantime → Google)
                                            </option>
                                            <option value="both" {{ $account['syncDirection'] == 'both' ? 'selected' : '' }}>
                                                Two-way sync
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               name="syncEnabled" 
                                               id="syncEnabled_{{ $account['id'] }}"
                                               {{ $account['syncEnabled'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="syncEnabled_{{ $account['id'] }}">
                                            Enable automatic sync
                                        </label>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="submit" class="btn btn-primary">Save Settings</button>
                                        <a href="/googlecalendar/sync/{{ $account['id'] }}" class="btn btn-outline-secondary">
                                            <i class="fa fa-refresh"></i> Sync Now
                                        </a>
                                    </div>

                                    @if($account['lastSyncAt'])
                                        <small class="text-muted mt-2 d-block">
                                            Last synced: {{ $account['lastSyncAt'] }}
                                            @if($account['lastSyncStatus'] == 'error')
                                                <span class="text-danger">(Error: {{ $account['lastSyncError'] }})</span>
                                            @endif
                                        </small>
                                    @endif
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <hr>
                    <a href="/googlecalendar/connect" class="btn btn-outline-primary">
                        <i class="fa fa-plus"></i> Connect Another Google Account
                    </a>
                @endif
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <strong>About Google Calendar Integration</strong>
                    </div>
                    <div class="card-body">
                        <p>This integration allows you to:</p>
                        <ul>
                            <li>See Google Calendar events in your Leantime calendar</li>
                            <li>Optionally push Leantime events to Google Calendar</li>
                            <li>Connect multiple Google accounts</li>
                        </ul>
                        <p class="text-muted small">
                            Events sync automatically every 15 minutes, or you can trigger a manual sync.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## Terminal Setup Commands

Run these commands in order to scaffold the plugin:

```bash
# Navigate to plugins directory
cd /Users/gloriafolaron/Herd/leantime/app/Plugins

# Create plugin directory structure
mkdir -p GoogleCalendar/{Controllers,Services,Repositories,Models,Migrations,Templates/partials,Language,Commands}

# Create empty files
touch GoogleCalendar/index.php
touch GoogleCalendar/register.php
touch GoogleCalendar/composer.json
touch GoogleCalendar/Controllers/Connect.php
touch GoogleCalendar/Controllers/Callback.php
touch GoogleCalendar/Controllers/Settings.php
touch GoogleCalendar/Controllers/Sync.php
touch GoogleCalendar/Controllers/Disconnect.php
touch GoogleCalendar/Services/GoogleCalendar.php
touch GoogleCalendar/Services/GoogleAuth.php
touch GoogleCalendar/Services/EventMapper.php
touch GoogleCalendar/Repositories/GoogleCalendarAccounts.php
touch GoogleCalendar/Models/GoogleCalendarAccount.php
touch GoogleCalendar/Models/GoogleCalendarEvent.php
touch GoogleCalendar/Migrations/CreateGoogleCalendarTables.php
touch GoogleCalendar/Templates/settings.blade.php
touch GoogleCalendar/Templates/connect.blade.php
touch GoogleCalendar/Language/en-US.ini
touch GoogleCalendar/Commands/SyncCalendars.php

# Install Google API client (from plugin directory)
cd GoogleCalendar
composer require google/apiclient:^2.15

# Go back to Leantime root and dump autoload
cd /Users/gloriafolaron/Herd/leantime
composer dump-autoload
```

---

## Environment Configuration

Add to `.env`:
```env
# Google Calendar Integration
GOOGLE_CALENDAR_CLIENT_ID=
GOOGLE_CALENDAR_CLIENT_SECRET=
GOOGLE_CALENDAR_REDIRECT_URI=${APP_URL}/googlecalendar/callback
```

Add to `config/configuration.php` (if using config file):
```php
'googleCalendarClientId' => env('GOOGLE_CALENDAR_CLIENT_ID', ''),
'googleCalendarClientSecret' => env('GOOGLE_CALENDAR_CLIENT_SECRET', ''),
'googleCalendarRedirectUri' => env('GOOGLE_CALENDAR_REDIRECT_URI', ''),
```

---

## Cron Job Setup

Add to your server's crontab:
```bash
* * * * * cd /path/to/leantime && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually for testing:
```bash
php artisan googlecalendar:sync
```

---

## Testing Checklist

- [ ] Google Cloud Console project created
- [ ] OAuth credentials generated
- [ ] Environment variables configured
- [ ] Plugin files created
- [ ] Composer dependencies installed
- [ ] Database migration run
- [ ] Can click "Connect Google Calendar"
- [ ] OAuth redirect works
- [ ] Callback stores tokens
- [ ] Settings page shows connected account
- [ ] Calendar list loads
- [ ] Events appear in Leantime calendar
- [ ] Manual sync works
- [ ] Token refresh works (test after 1 hour)
- [ ] Disconnect removes account

---

## Future Enhancements

1. **Outlook Calendar Plugin** - Same pattern, Microsoft Graph API
2. **Webhook support** - Real-time sync instead of polling
3. **Conflict resolution** - Handle overlapping events
4. **Event editing** - Edit Google events from Leantime
5. **Color sync** - Match calendar colors
6. **Recurring events** - Handle recurrence rules
