---
mode: testing
url: http://localhost:5080/auth/login
max_steps: 25
tags: [smoke, ideas]
---

# Add an idea to the Idea Board

## Log in
Open http://localhost:5080/auth/login.
Type "test@leantime.io" into the email field and "Test123456!" into the password field, then press Enter.
Wait for the page to finish loading.
Verify the URL contains "/dashboard".

## Open the Idea Board
Open http://localhost:5080/ideas/showBoards.
Wait for the page to finish loading. If a "Welcome to the Idea Board!" dialog is shown, click its "Close" button.
Verify the page shows "Ideas / Board" and an "Add Idea" button.

## Add an idea
Click the "Add Idea" button.
In the idea dialog, type "Dark mode for reports" into the "Short name" field, then click "Save & Close".
Wait for the board to reload.
Verify an idea card titled "Dark mode for reports" is shown on the board.
