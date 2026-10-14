---
test: ../idea_board_test.md
status: passed
started: 2026-10-14T06:41:13.432Z
duration_s: 132
session_id: e0cf002d-01e3-4a66-940f-fee896c21162
---

# Add an idea to the Idea Board — Result

## Log in ✓ passed (41.6s)
md5: 1ccaf566ae7bfba71bb76172990775d6
Open http://localhost:5080/auth/login.
Type "test@leantime.io" into the email field and "Test123456!" into the password field, then press Enter.
Wait for the page to finish loading.
Verify the URL contains "/dashboard".

## Open the Idea Board ✓ passed (45s)
md5: d238deaf3ef2eaefb71bf50f2e455e89
Open http://localhost:5080/ideas/showBoards.
Wait for the page to finish loading. If a "Welcome to the Idea Board!" dialog is shown, click its "Close" button.
Verify the page shows "Ideas / Board" and an "Add Idea" button.

## Add an idea ✓ passed (40.4s)
md5: 0a923a0efe42986dd7ec70c27a6fe28b
Click the "Add Idea" button.
In the idea dialog, type "Dark mode for reports" into the "Short name" field, then click "Save & Close".
Wait for the board to reload.
Verify an idea card titled "Dark mode for reports" is shown on the board.
