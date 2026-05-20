# Work Session Tracker — Employee Privacy Notice

**Audience:** All employees who use Leantime
**Owner:** IT / People Operations
**Status:** Effective from rollout
**Version:** 1.0

This 1-page notice explains, in plain English, exactly what the new
**Work Session Tracker** in Leantime captures, when it captures it, who can see it,
and what your rights are. Please read before starting your first session.

---

## 1. What is the Work Session Tracker?

A button in your Leantime navbar that lets you **start** and **stop** a work
session. While a session is open, Leantime simply counts the time. **Nothing else
is recorded between start and stop** — no continuous video, no keystrokes,
no mouse tracking, no microphone, no clipboard, no browser-tab snooping.

## 2. What is captured?

| When                  | What                                                    |
|-----------------------|---------------------------------------------------------|
| **At Start**          | One still image (JPEG) of the screen / window you chose |
| **At Stop (optional)**| One still image (JPEG) of the screen / window you chose |
| Throughout the session| Only the timestamp — no images, no other telemetry      |

That's it. Two screenshots maximum per session. Both are JPEG stills, not video.

## 3. When does the browser ask for permission?

**Every time you click "Start Session"** (and every time you click the
"Stop with screenshot…" option, if you choose to use it), your browser
will show its native screen-share picker:

> *"leantime.intelliversex.com wants to share the contents of your screen.
> Choose what to share."*

You **choose** at that moment which screen, window, or browser tab to share.
The platform never captures anything you didn't explicitly choose to share.

You can cancel the picker at any time — if you cancel, **no screenshot is
captured and no session is started**.

## 4. What does *not* happen

- ❌ No continuous screen recording
- ❌ No video, audio, or microphone capture
- ❌ No keystroke or mouse logging
- ❌ No background capture — the browser **must** show the screen-share dialog every time
- ❌ No off-hours capture — only when you click Start
- ❌ No silent operation — Chrome / Edge / Firefox always show a clear "Sharing your screen" indicator while a screen-share stream is open (it lasts under a second)

## 5. Who can see the screenshots?

| Role               | Access                                                             |
|--------------------|--------------------------------------------------------------------|
| **You (Employee)** | Your own sessions and screenshots, via *My Sessions*               |
| **Admin / Owner**  | All employees' sessions and screenshots, via *Admin Monitor*       |
| **Manager**        | **No direct screenshot access** — only aggregate session summaries |
| **Other employees**| **No access** — you cannot view colleagues' screenshots or sessions|

All screenshot access is authenticated and audit-logged.

## 6. Where are the screenshots stored?

- Stored inside our private Kubernetes-managed Leantime instance, on the
  `leantime-userfiles` persistent volume (encrypted at rest by AWS EBS).
- **Never** sent to a third-party service, never shared outside the company,
  never used for advertising or machine-learning training.

## 7. How long are they kept?

- Default retention: **180 days** from session end.
- You can request earlier deletion of your own screenshots by emailing
  the IT team (`it@intelliversex.com`) with the session ID.

## 8. Browser support

Screen capture requires Chrome, Edge, or Firefox on a **desktop or laptop**.
**Safari and most mobile browsers do not currently support this feature.**
If your browser is not supported, Leantime will show a clear "Browser not
supported" message and will not start a session.

## 9. What if I don't want to take a stop-screenshot?

The navbar **"Stop Session"** button stops the session immediately with
**no end screenshot**. Use **"Stop with screenshot…"** only if you want to
record a finishing snapshot (for example, to evidence completed work).

## 10. Your rights

- You can stop a session at any time.
- You can view, but cannot delete, your own session history (for payroll integrity).
- You can request a copy of all screenshots taken of you at any time.
- You can request correction or deletion via the IT team within applicable
  retention rules.
- If you believe a screenshot was taken outside the rules above, please report
  it immediately to your manager and `it@intelliversex.com`.

## 11. Questions?

- Technical questions → `it@intelliversex.com`
- Privacy questions → `people@intelliversex.com`

---

*By starting a work session, you acknowledge you have read this notice
and consent to the limited screen-capture described above.*
