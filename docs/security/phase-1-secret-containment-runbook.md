# Phase 1 secret containment operator runbook

This runbook is internal. It contains no credential values or QR payloads. Phase 1 application changes can be reviewed normally; the history rewrite, remote force-push, credential rotations, APP_KEY rotation, collaborator clone invalidation, and deployment checkout replacement require an explicitly authorized maintenance window.

## 1. Rotation checklist

Treat every credential committed at any time as exposed even after Git history is rewritten. Rotate or revoke it at its owning system before ending the Git freeze.

- Database: create a replacement least-privilege credential, update the deployment secret store and Docker/runtime configuration, verify the application and backup jobs, then revoke the old credential.
- Broadcasting/Pusher: rotate the application credentials, update server-side and `VITE_` build-time configuration, rebuild the frontend, restart long-running workers, verify private channels, then revoke the old credential.
- Hosting/deployment access: rotate any control-panel, SFTP, SSH, database administration, or deployment credentials that appeared in the removed Redkite guides. Remove obsolete accounts and keys.
- APP_KEY: use the coordinated procedure in section 7. Do not treat it like an ordinary provider key because existing QR ciphertext and Laravel cookies depend on it.
- Mail: the scanned repository contained placeholder mail fields rather than a confirmed active mail credential. Verify the provider inventory; rotate only if an active value was ever committed or copied into an affected artifact.
- GitHub: inspect secret-scanning alerts, deploy keys, Actions secrets, personal tokens, webhooks, and environments. No private-key block or GitHub-token pattern was detected locally, but provider state is authoritative.

Record only credential owner, rotation status, operator, and completion time. Never paste an old or new value into an issue, commit, terminal transcript, or this runbook.

## 2. Required freeze and preflight

1. Announce a Git freeze covering every branch, tag, pull request, deployment checkout, fork, and automation writer.
2. Merge or close open pull requests. Record any branches that must survive the rewrite.
3. Confirm every collaborator has acknowledged the freeze and stopped pushing.
4. Confirm the Phase 1 commits are on the branch/ref that will be preserved.
5. Verify the recovery archive and a current database backup without displaying their contents.
6. Confirm repository-owner access, temporary permission to bypass branch protection, and explicit authorization for the force-push. Stop if any authorization is missing.
7. Install `git-filter-repo` 2.47 or newer and a current Gitleaks release from their official sources.

GitHub documents that a sensitive-data rewrite changes commit IDs, invalidates signatures and some pull-request diffs, and requires coordination with forks and clones. Review [GitHub's current sensitive-data removal guidance](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository) immediately before the maintenance window.

## 3. Dry-run analysis and immutable backup

Run from a new PowerShell session in a recovery directory outside every existing clone:

```powershell
$ErrorActionPreference = 'Stop'
$remote = 'git@github.com:Jimcl6/material_inspection_recording_system.git'
$work = 'C:\Users\ai.pc\Documents\MIRS-Recovery\phase1-history-rewrite'
$mirror = Join-Path $work 'material_inspection_recording_system.git'
$bundle = Join-Path $work 'mirs-before-phase1-rewrite.bundle'

New-Item -ItemType Directory -Force -Path $work | Out-Null
git clone --mirror $remote $mirror
Set-Location $mirror
git fetch --prune --tags origin '+refs/heads/*:refs/heads/*'
git show-ref | Set-Content -Encoding utf8 (Join-Path $work 'refs-before.txt')
git bundle create $bundle --all
git bundle verify $bundle
git filter-repo --version
git filter-repo --analyze
```

Copy `history-purge-paths.txt` and `git-filter-repo-replacements.txt` from a clean Phase 1 checkout into `$work`. Review `.git/filter-repo/analysis` (or `filter-repo/analysis` in a bare mirror) and reconcile every branch/tag path against `history-purge-paths.txt`. The analysis is read-only; do not proceed on an unexplained path or missing ref.

Verify the bundle hash and place a copy on independent protected storage. The existing Phase 0 archive is an additional recovery point, not a substitute for this immediately pre-rewrite bundle.

## 4. Rewrite all local branches and tags

From the mirror clone, run this single rewrite. The path list is intentionally explicit so canonical migrations, schemas, fixtures, and the sanitized environment templates remain in history.

```powershell
$replacementFile = Join-Path $work 'git-filter-repo-replacements.txt'

git filter-repo --sensitive-data-removal --force --invert-paths `
  --replace-text $replacementFile `
  --path '.env.before-mysql97-20260702-164057' `
  --path 'backups/sqlite-switch-20260702-160330/.env.before-sqlite' `
  --path 'database/fc_1_data_db_testing (1).sql' `
  --path 'database/productionbatches (2).sql' `
  --path 'docs/redkite-laravel-subdomain-upload-guide.pdf' `
  --path 'docs/redkite-subdomain-and-git-upload-guide.pdf' `
  --path 'resources/js/Layouts/AppLayout.backup.vue' `
  --path 'setup_admin.php' `
  --path 'setup_admin.sql' `
  --path 'check_users.php' `
  --path 'clear_log.php' `
  --path 'temp_read.php' `
  --path '_temp_read.php' `
  --path 'outputs' `
  --path 'database/CSB29043P1-P NEW.xlsx' `
  --path 'database/DFB660023P..xlsx' `
  --path 'dfb660024p_full.txt' `
  --path 'dfb660024p_structure.txt' `
  --path 'excel_structure.txt' `
  --path 'excel_structure2.txt' `
  --path 'item_codes.txt' `
  --path 'master_copy.txt'
```

Do not add broad path globs such as `database/**` or `*.php`; they would destroy required application history. Preserve the rewrite output, especially the first changed commits, changed refs, affected pull-request refs, and any LFS orphan report.

## 5. Verify before the remote update

All commands must pass in the rewritten mirror:

```powershell
$purgePaths = Get-Content (Join-Path $work 'history-purge-paths.txt') | Where-Object { $_ -and -not $_.StartsWith('#') }
foreach ($path in $purgePaths) {
    $hits = git log --all --format='%H' -- $path
    if ($hits) { throw "Purged path still reachable: $path" }
}

git fsck --full
gitleaks git . --redact=100 --log-opts='--all'
```

Also verify that the environment fields listed in `git-filter-repo-replacements.txt` are empty in every reachable `.env.example` or `.env.docker.example` blob. The verifier may report a path, ref, key name, and count; it must never print a field value.

Inspect the changed-ref report and confirm that every expected local branch and tag was rewritten. If the result is wrong, delete only this disposable mirror, restore from the verified bundle or reclone, correct the manifest, and repeat. Do not push a questionable rewrite.

## 6. Authorized GitHub update and cache cleanup

The following commands are destructive and were intentionally not run during implementation. Execute them only after repository-owner authorization is recorded:

```powershell
git remote add origin 'git@github.com:Jimcl6/material_inspection_recording_system.git'
git push --force --mirror origin
```

GitHub read-only `refs/pull/*` rejections are expected; any branch or tag rejection is not. If policy requires separate commands instead of a mirror push, use:

```powershell
git push --force --all origin
git push --force --tags origin
```

After the push:

1. Re-enable every temporarily changed branch-protection rule.
2. Review GitHub secret-scanning alerts. Mark an alert resolved only after provider-side rotation/revocation and rewrite verification are complete.
3. Identify affected pull-request refs from the filter-repo changed-ref report.
4. Check forks. Their owners must purge or delete affected forks; a clean upstream does not clean forks.
5. Open a GitHub Support request with the repository name, affected PR count, first changed commits, and any LFS orphan report so cached views and PR references can be dereferenced and server garbage collection can be performed.
6. Fresh-clone the remote into a new validation directory and repeat the path checks, Gitleaks scan, build, and targeted Phase 1 tests there.

## 7. APP_KEY rotation and badge reissue

APP_KEY rotation is a coordinated production event. It invalidates existing encrypted QR payloads and Laravel encrypted cookies; users should expect to sign in again. Repository inspection found no other explicit application-level `Crypt` usage beyond user QR badges, but a current production inventory remains mandatory.

1. Schedule maintenance and stop badge printing and scanning writes.
2. Take and verify a fresh production database backup without using production as a test target.
3. Store the old APP_KEY only in the approved protected secret manager for the bounded rollback period. Do not display it.
4. Generate and inject the replacement APP_KEY through the deployment secret manager. If a server-local `.env` is the approved source, `php artisan key:generate --force` may write it directly; never use `--show`, copy it into a transcript, or commit the file.
5. Restart the application containers and queue workers, then clear/rebuild Laravel configuration cache using the deployment's normal release procedure.
6. Confirm the application is using the new release and key without printing the key.
7. Preview the active-user scope:

   ```text
   php artisan mirs:regenerate-user-qrs --dry-run --active-only
   ```

8. Reconcile the safe totals and user IDs with the active-user inventory. If correct, execute:

   ```text
   php artisan mirs:regenerate-user-qrs --active-only
   ```

9. Sign in as an authorized admin and open **User Management > Reissue Badges**. Select manageable batches, reissue, review the locally rendered preview, and print. The page sends no QR value to an external service.
10. Mark old physical badges invalid, control disposal, and issue the new copies. Inactive users are excluded by `--active-only` and the printing page.
11. Validate login, an authorized synthetic/staging badge scan, normal application use, queues, and broadcasting. Automated tests must use synthetic identities only; never copy a production badge value into a test.
12. End maintenance only after the new badges validate. Destroy the rollback copy of the old key according to the approved retention policy.

Rollback before badge issuance means restoring the previous APP_KEY through the secret manager and restarting the release. Once new badges are distributed, rollback requires another full regeneration and reissue; treat it as a second controlled event.

## 8. Mandatory collaborator reclone and deployment replacement

Every collaborator must stop using the old clone. Fetching, pulling, merging, or pushing from it can reintroduce the removed objects.

1. Quarantine or archive only reviewed, secret-free uncommitted work as a plain patch outside the clone.
2. Remove the old clone according to workstation data-handling policy.
3. Clone the repository into a new directory and verify the new default-branch commit ID announced by the operator.
4. Create new feature branches from rewritten refs. Apply only reviewed plain patches; never merge or push an old commit or tag.
5. Run the post-rewrite path and secret scans before the first push.

Replace every deployment checkout as well: enter maintenance, preserve only deployment-managed secrets and persistent storage, create a fresh clone/release directory from the rewritten remote, install locked dependencies, build assets, run migrations against the intended environment, restore the external secret configuration, smoke-test, switch traffic, then retire the old checkout. Do not update an old deployment checkout with `git pull`.

## 9. End the Git freeze

End the freeze only when all items are true:

- Provider credentials and APP_KEY have their approved final status.
- Every branch and tag passed the purge-path and secret scans in a fresh remote clone.
- GitHub branch protection is restored and cache/PR/fork follow-up is assigned.
- All collaborators confirmed a fresh clone.
- Every deployment checkout was replaced and validated.
- Active badges were regenerated, printed in controlled batches, and scan-tested.
- The repository owner announces the new authoritative commit IDs and explicitly ends the freeze.

Do not start Phase 2 from this runbook.
