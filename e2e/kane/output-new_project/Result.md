---
test: ../new_project_test.md
status: passed
started: 2026-10-14T06:43:48.875Z
duration_s: 167
session_id: bbdb7b0c-ff17-4789-bcc3-2db7b3095c7e
---

# Create a new project — Result

## Log in ✓ passed (39.7s)
md5: 1ccaf566ae7bfba71bb76172990775d6
Open http://localhost:5080/auth/login.
Type "test@leantime.io" into the email field and "Test123456!" into the password field, then press Enter.
Wait for the page to finish loading.
Verify the URL contains "/dashboard".

## Fill in the new project form ✓ passed (69.6s)
md5: dfb9011f2b4b7d8eea714819cb4f9c84
Open http://localhost:5080/projects/newProject.
Wait for the page to finish loading. If a welcome dialog is shown, click its "Close" button.
Type "Website Relaunch" into the "Enter the title of this project" field.
Verify the page shows "New Project" and the title field contains "Website Relaunch".

## Save the project ✓ passed (52s)
md5: fa18213dcbb1d5bd430cac118c827256
Click the "Save" button at the bottom of the form.
Wait for the page to finish loading. If a welcome dialog is shown, click its "Close" button.
Verify the current project shown in the header is "Website Relaunch".
