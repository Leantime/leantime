# GitHub Secrets - Quick Setup

## Required Secrets

Go to: **Settings → Secrets and variables → Actions → New repository secret**

> **Note:** Replace all placeholder values below (e.g., `your.server.ip.address`, `your-ssh-username`) with your actual server configuration values.

---

### 1. SSH_PRIVATE_KEY

```bash
# Display key content:
cat ~/.ssh/your-private-key
```

Copy the **entire output** (including `-----BEGIN ... KEY-----` and `-----END ... KEY-----`) and paste into GitHub Secret.

---

### 2. SSH_HOST

**Value:** `your.server.ip.address`

---

### 3. SSH_USER

**Value:** `your-ssh-username`

---

### 4. STAGING_PATH

**Value:** `your-leantime-staging-path`

---

### 5. PRODUCTION_PATH

**Value:** `your-leantime-production-path`

---

### 6. PRODUCTION_URL (optional)

**Value:** `https://your-production-domain.com`

Replace with your actual production application URL for health checks.

---

## Verification

After adding all secrets, you should see:

```
✓ SSH_PRIVATE_KEY
✓ SSH_HOST
✓ SSH_USER  
✓ STAGING_PATH
✓ PRODUCTION_PATH
✓ PRODUCTION_URL
```

## Testing

1. Ensure develop branch exists: `git checkout develop`
2. Push: `git push -u origin develop`
3. GitHub Actions will automatically start deployment
4. Monitor in **Actions** tab

