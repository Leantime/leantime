# Leantime Cloud image

This folder builds the production image for Leantime Cloud. It is not used by
self-hosted installs (those use `.docker/` and the `leantime/leantime` image).

- `Dockerfile`: two-stage build (composer + `npx mix` on serversideup `-cli`,
  then serversideup `-fpm-nginx` on port 8080). Both `FROM` lines pin the PHP
  patch release; bump them together.
- `entrypoint.d/`: runs on container start (cache clear, package discovery).
  Every step must be non-fatal: the script runs with `set -e`, so a failing
  command takes production down in a restart loop.
- `nginx/security.conf`: file blocking only. Security headers come from
  Leantime's `InitialHeaders` middleware.

Cloud-only behaviour (tenants, subscriptions, billing) lives in plugins
(`app/Plugins`, enabled through `LEAN_PLUGINS` in Coolify), not in this repo.

## Deploying

Actions → **Deploy Cloud** → Run workflow, or:

```bash
gh workflow run deploy-cloud.yml -f ref=master       # latest master
gh workflow run deploy-cloud.yml -f ref=v3.15.0      # a release (from 3.14.1 on; older tags lack .deploy/)
gh workflow run deploy-cloud.yml -f ref=<sha>        # rollback / specific commit
```

The workflow moves the `cloud-production` branch to that commit, asks Coolify
to deploy it and waits until app1 and app2 both report finished. Coolify builds
on app1 (~7 min) and then rolls the image to app2 (~30 s).

`cloud-production` is the last commit *requested*, not necessarily what runs.
A failed run can leave app1 and app2 on different commits (app1 updated, app2
not), so check both in Coolify's Deployments tab. To recover, run the workflow
again: with a fixed commit, or with the "Last complete rollout" commit from the
run summary (the newest commit that finished on every server). Every run
deploys to both servers. Only commits that
contain `.deploy/Dockerfile` can be deployed (the workflow refuses older ones),
so the earliest rollback target is the commit that added this folder.

`ADDITIONAL_SERVERS` in the workflow is the number of servers besides app1
(currently 1, app2). The run fails if Coolify doesn't start a rollout on each of
them, so update it when servers are added to or removed from the Coolify app.

## Build locally

```bash
docker build -f .deploy/Dockerfile -t leantime-cloud:dev .
docker run -p 8080:8080 leantime-cloud:dev
```

## One-time setup

1. **Coolify API token**: Coolify → Keys & Tokens → API tokens → create
   `github-deploy` with the `deploy` and `read` permissions.
   API access must be enabled in Coolify → Settings → Advanced.
2. **GitHub environment** `production` (Settings → Environments), deployments
   limited to the `master` branch:
   - secret `COOLIFY_TOKEN`: the token from step 1
   - variable `COOLIFY_URL`: `https://coolify.infra.leantime.io`
   - variable `COOLIFY_APP_UUID`: the production app's UUID
3. **Coolify app** (Leantime Cloud → production → app → Configuration):
   - Source: repository `Leantime/leantime`, branch `cloud-production`
     (the Coolify GitHub App must have access to `Leantime/leantime` and
     `Leantime/plugins`)
   - Dockerfile location: `/.deploy/Dockerfile`, Git submodules on
   - Advanced → Auto Deploy **off** (otherwise the branch push also triggers
     a webhook deploy and Coolify builds twice)

The workflow refuses to deploy if the Coolify app points at another repository
or branch.
