# Leantime CI/CD Setup Guide

## Overview

This CI/CD pipeline automatically deploys the Leantime application to your Ubuntu server:

- **Develop** branch → `STAGING_PATH` (Staging environment)
- **Master** branch → `PRODUCTION_PATH` (Production environment)

> **Note:** Server connection details (SSH host, user, and key) are configured via GitHub Secrets (see Step 1 below).

---

## Setup

### Step 1: Add GitHub Secrets

Go to: **GitHub Repository → Settings → Secrets and variables → Actions**

Click **"New repository secret"** and add the following:

#### 1. SSH_PRIVATE_KEY

Content of your SSH private key file:

```bash
# On your local machine, display the key content:
cat ~/.ssh/your-private-key
```

Copy the **entire output** (including `-----BEGIN ... KEY-----` and `-----END ... KEY-----`)

**Name:** `SSH_PRIVATE_KEY`  
**Value:** [paste entire key content]

#### 2. SSH_HOST

**Name:** `SSH_HOST`  
**Value:** `your.server.ip.address`

#### 3. SSH_USER

**Name:** `SSH_USER`  
**Value:** `your-ssh-username`

#### 4. STAGING_PATH

**Name:** `STAGING_PATH`  
**Value:** `/path/to/your/staging/deployment`

#### 5. PRODUCTION_PATH

**Name:** `PRODUCTION_PATH`  
**Value:** `/path/to/your/production/deployment`

#### 6. PRODUCTION_URL (optional, for health check)

**Name:** `PRODUCTION_URL`  
**Value:** `https://yourdomain.com` (replace with actual URL)

---

### Step 2: Verify SSH Access

On your server, ensure the SSH key is added:

```bash
# Connect to server
ssh -i ~/.ssh/your-private-key your-ssh-username@your.server.ip.address

# Check if authorized_keys exists
cat ~/.ssh/authorized_keys

# If not added, add it:
cat ~/.ssh/your-private-key.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

---

### Step 3: Prepare Directories on Server

```bash
# Connect to server
ssh -i ~/.ssh/your-private-key your-ssh-username@your.server.ip.address

# Staging directory
sudo mkdir -p /path/to/staging/deployment
sudo chown -R www-data:www-data /path/to/staging/deployment
sudo chmod -R 775 /path/to/staging/deployment

# Production directory
sudo mkdir -p /path/to/production/deployment
sudo chown -R www-data:www-data /path/to/production/deployment
sudo chmod -R 775 /path/to/production/deployment
```

---

### Step 4: Check .env Files

```bash
# Staging .env
sudo nano /path/to/staging/deployment/config/.env

# Production .env
sudo nano /path/to/production/deployment/config/.env
```

Ensure each .env has:
```ini
LEAN_DB_HOST=localhost
LEAN_DB_USER=your_db_user
LEAN_DB_PASSWORD=your_db_password
LEAN_DB_DATABASE=your_db_name
LEAN_SESSION_PASSWORD=random_32_characters
```

---

### Step 5: Configure sudoers (for nginx reload)

```bash
# On the server
sudo visudo

# Add this line at the end (replace 'your-ssh-username' with your actual SSH user):
your-ssh-username ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx, /bin/chown, /bin/chmod
```

Save and exit (Ctrl+X, Y, Enter)

---

## Monitoring Deployments

### Viewing Deployment Process

1. Go to GitHub repository
2. Click on **"Actions"** tab
3. You'll see all deployments

### Manual Trigger

In GitHub Actions:
1. Click on **"Deploy Leantime"** workflow
2. Click **"Run workflow"**
3. Select branch (develop or master)

---

## 🔧 What the Pipeline Does

### For Develop branch (Staging):

1. Checks out code
2. Installs PHP dependencies (Composer)
3. Installs NPM dependencies
4. Builds frontend (CSS/JS)
5. Syncs files to server
6. Sets permissions
7. Clears cache
8. Restarts web server

### For Master branch (Production):

1.  Everything from develop
2.  **Runs health check**

---

## What Does NOT Get Overwritten During Deployment

The pipeline **does not modify** the following files on the server:

- `config/.env` - Your configuration
- `userfiles/` - Uploaded files
- `public/userfiles/` - Public files
- `storage/logs/` - Logs
- `app/Plugins/` - Installed plugins

---

