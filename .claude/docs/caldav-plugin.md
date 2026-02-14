# CalDAV Plugin Knowledge Base

Knowledge base for Leantime's CalDAV integration. Use this to answer user questions about calendar synchronization.

## Quick Reference

| Attribute | Value |
|-----------|-------|
| **Feature** | Bidirectional calendar sync |
| **Location** | Settings > CalDAV Integration |
| **Access** | Owner, Admin, Manager, Editor roles |
| **Sync Frequency** | Real-time + every 15 minutes |

---

## Common User Questions

### "How do I sync my calendar with Leantime?"

There are two ways to sync:

**Option 1: Use Leantime as a CalDAV Server (Recommended)**
Connect your phone or desktop calendar app directly to Leantime.
- Server URL: `https://[your-leantime-url]/caldav/server/`
- Username: Your Leantime email
- Password: Your Leantime password
- Works with: Apple Calendar, Thunderbird, DAVx5, Evolution

**Option 2: Push to External Server (Outbound)**
Push Leantime events to a CalDAV server like Nextcloud.
1. Go to Settings > CalDAV Integration
2. Click "Add CalDAV Account"
3. Enter your Nextcloud/ownCloud calendar URL and credentials
4. Click Connect

---

### "Why aren't my events syncing?"

Troubleshooting checklist:

1. **Is the account enabled?**
   - Go to Settings > CalDAV Integration
   - Verify the account shows "Enabled" (green badge)

2. **Try manual sync**
   - Click "Sync Now" button next to the account
   - Check if events appear after sync

3. **Check credentials**
   - Edit the account
   - Re-enter the password
   - Save and try again

4. **Verify the calendar URL**
   - URLs vary by server type:
     - Nextcloud: `https://server.com/remote.php/dav/calendars/USERNAME/CALENDAR/`
     - Baikal: `https://server.com/dav.php/calendars/USERNAME/CALENDAR/`
     - Radicale: `https://server.com/USERNAME/CALENDAR/`

5. **Check SSL settings**
   - If using self-signed certificates, uncheck "Verify SSL" in account settings

---

### "Can I use Apple Calendar with Leantime?"

Yes! Apple Calendar works great with Leantime's CalDAV server.

**Setup on Mac:**
1. Open Calendar > Add Account > Other CalDAV Account
2. Choose "Manual" for account type
3. Enter:
   - Username: Your Leantime email
   - Password: Your Leantime password
   - Server: `https://your-leantime-url/caldav/server/`
4. Click Sign In

**Setup on iPhone/iPad:**
1. Settings > Calendar > Accounts > Add Account
2. Choose Other > Add CalDAV Account
3. Enter same details as above
4. Tap Next

---

### "Can I use Google Calendar with Leantime?"

Google Calendar doesn't support standard CalDAV for third-party apps.

**Workarounds:**
1. Use Nextcloud with Google Calendar integration as a bridge
2. Export events as ICS and import manually
3. Use DAVx5 on Android which can sync to multiple calendars

---

### "What's the difference between Server URL and Calendar URL?"

**Server URL**: The base address of the CalDAV server
- Example: `https://cloud.example.com`

**Calendar URL**: The full path to your specific calendar
- Example: `https://cloud.example.com/remote.php/dav/calendars/john/personal/`

Think of it like:
- Server URL = the building address
- Calendar URL = the specific room in that building

---

### "Is my password secure?"

Yes. CalDAV passwords are:
- Encrypted in the database using AES-256-CBC
- Never stored in plain text
- Decrypted only when making CalDAV requests
- Protected by your Leantime installation's APP_KEY

For the CalDAV server feature, connections should always use HTTPS to protect credentials in transit.

---

### "What gets synced?"

| Content | Direction | Editable |
|---------|-----------|----------|
| Calendar events | Two-way | Yes |
| All-day events | Two-way | Yes |
| Ticket due dates | Read-only | No* |
| Ticket work periods | Read-only | No* |

*To change ticket dates, edit the ticket directly in Leantime.

---

### "How often does sync happen?"

- **Immediately**: When you create, edit, or delete an event in Leantime
- **Every 15 minutes**: Automatic background sync pulls changes from external servers
- **On demand**: Click "Sync Now" to force immediate sync

---

### "Why can't I delete ticket due dates from my calendar app?"

Ticket due dates appear on your calendar but are "read-only" in external apps. They're controlled by the ticket itself.

**To change a ticket's due date:**
1. Open the ticket in Leantime
2. Edit the due date field
3. Save the ticket
4. The calendar will update automatically

---

### "How do I see someone else's calendar?"

CalDAV sync is personal - each user can only see their own calendar events.

**For team visibility:**
- Use project-level calendars in Leantime
- Share calendars through your external CalDAV server
- Use the team calendar view in Leantime

---

### "My external app can't connect to Leantime"

Check these common issues:

1. **URL format**: Make sure you're using `https://your-url/caldav/server/` (with trailing slash)

2. **Credentials**: Use your Leantime email and password

3. **HTTPS**: The CalDAV server requires HTTPS. HTTP connections won't work.

4. **Firewall**: Ensure port 443 is open for inbound connections

5. **App compatibility**: Some apps need specific setup:
   - Thunderbird needs TbSync add-on
   - Outlook needs CalDav Synchronizer add-on

---

### "Can I sync with multiple calendars?"

**Outbound (to external servers)**: Yes! Add multiple CalDAV accounts in Settings > CalDAV Integration. Each account syncs to a different external calendar.

**Inbound (as CalDAV server)**: Leantime exposes one calendar per user containing all their events.

---

### "Events sync but show wrong times"

This is usually a timezone issue:

1. Check your Leantime timezone setting (User Settings)
2. Check your calendar app's timezone
3. Ensure both are set correctly

Leantime stores all times in UTC internally and converts for display.

---

## Technical Details

### Sync Architecture

```
┌─────────────┐     Real-time      ┌──────────────┐
│   Leantime  │ ─────────────────> │   External   │
│   Calendar  │                    │   CalDAV     │
│   Events    │ <───────────────── │   Server     │
└─────────────┘     Every 15 min   └──────────────┘

┌──────────────┐                   ┌─────────────┐
│   External   │ ────────────────> │   Leantime  │
│   Calendar   │     CalDAV        │   CalDAV    │
│   App        │ <──────────────── │   Server    │
└──────────────┘     Protocol      └─────────────┘
```

### Event Hooks

- `afterCalendarSave` - Triggers sync when event created/edited
- `afterCalendarDelete` - Triggers deletion on external servers

### Database Tables

- `zp_caldav_accounts` - Stores CalDAV connection settings
- `zp_caldav_events` - Maps Leantime events to CalDAV UIDs

### API Endpoints

- `GET /caldav/settings` - Settings page
- `GET /caldav/connect` - Add account form
- `POST /caldav/connect` - Save new account
- `GET /caldav/edit/{id}` - Edit account form
- `POST /caldav/edit/{id}` - Update account
- `GET /caldav/delete/{id}` - Delete account
- `GET /caldav/sync/{id}` - Manual sync trigger
- `/caldav/server/*` - CalDAV protocol endpoint

---

## Related Features

- **Calendar Module**: View and manage events at `/calendar/showMyCalendar`
- **Ticket Due Dates**: Set via ticket edit, appear on calendar
- **Dashboard Widget**: Shows upcoming calendar events
- **Personal Companion**: Can help schedule and manage time

---

## Error Messages

| Error | Meaning | Solution |
|-------|---------|----------|
| "Connection test failed" | Can't reach CalDAV server | Check URL and credentials |
| "Invalid credentials" | Username/password wrong | Re-enter credentials |
| "SSL certificate error" | Certificate validation failed | Uncheck "Verify SSL" for self-signed certs |
| "Calendar not found" | Calendar URL incorrect | Verify calendar URL format |
| "Sync failed" | Error during sync | Check logs, try manual sync |

---

## Plugin Information

- **Plugin Name**: CalDAV Integration
- **Location**: `app/Plugins/CalDAV/`
- **Dependencies**: sabre/dav, sabre/vobject
- **Documentation**: `CalDAV/docs/README.md`
