---
mode: testing
url: http://localhost:5080/auth/login
max_steps: 25
tags: [smoke, projects]
---

# Create a new project

## Log in
Open http://localhost:5080/auth/login.
Type "test@leantime.io" into the email field and "Test123456!" into the password field, then press Enter.
Wait for the page to finish loading.
Verify the URL contains "/dashboard".

## Fill in the new project form
Open http://localhost:5080/projects/newProject.
Wait for the page to finish loading. If a welcome dialog is shown, click its "Close" button.
Type "Website Relaunch" into the "Enter the title of this project" field.
Verify the page shows "New Project" and the title field contains "Website Relaunch".

## Save the project
Click the "Save" button at the bottom of the form.
Wait for the page to finish loading. If a welcome dialog is shown, click its "Close" button.
Verify the current project shown in the header is "Website Relaunch".
