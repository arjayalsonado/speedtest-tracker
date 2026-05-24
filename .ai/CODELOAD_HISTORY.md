# CODELOAD HISTORY

This file keeps project-specific historical findings, reviewer feedback, adopted changes, rejected suggestions, validation results, and remaining risks.

Append a new dated entry for each meaningful implementation, review, or feedback-assessment cycle.

---

## 2026-05-14 - MikroTik Lite v1.2.1 Multi-ISP Experimental Archive

### Date/Time

2026-05-14T19:56:26+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Loaded `speedtest-tracker-mikrotik-lite-change-archive-v1.2.1-multi-isp-experimental.md` as the project history source for the MikroTik Lite multi-ISP experimental branch. The archive supersedes the v1.1 multi-ISP markdown for code assembly and documents the corrected v1.2.1 design, implementation boundaries, QA focus, and blocker resolutions.

### Findings

- Accepted architecture remains one container, one RouterOS VETH, up to five generic ISP profiles, one SQLite database, profile-specific schedules, and RouterOS policy routing by source IP.
- v1.2.1 corrected the upstream action namespace to `App\Actions\CheckForScheduledSpeedtests`.
- v1.2.1 requires patching `app/Jobs/Ookla/RunSpeedtestJob.php` so binding can use configurable `SPEEDTEST_LITE_BIND_OPTION` values of `--interface` or `--ip`.
- Dockerfile guidance preserves the accepted v0.9 runtime package baseline and adds only required packages such as `iproute2`; later archive notes also add zip support to resolve a composer blocker.
- Scheduler guidance preserves upstream maintenance schedules and replaces only the scheduled speedtest block.
- RouterOS VETH multi-address CLI syntax remains validation-required; UI/address-list configuration is preferred until tested.
- The branch is accepted for code assembly and QA testing, not production-final.

### Reviewer Feedback Summary

The archive records reviewer-driven corrections from v1.1 to v1.2.1, including namespace fixes, binding-option handling, Dockerfile baseline preservation, schedule patching boundaries, VETH syntax caution, and a more conservative release status.

### Feedback Accepted

- Use `App\Actions\CheckForScheduledSpeedtests`.
- Add configurable Ookla bind option support in `RunSpeedtestJob.php`.
- Preserve v0.9 Dockerfile package baseline and add `iproute2`.
- Preserve upstream prune/maintenance schedules in `routes/console.php`.
- Treat exact RouterOS VETH multi-address CLI syntax as unverified until tested.
- Downgrade status from production-ready to QA/testing-ready.
- Resolve Docker config copy path blocker for `nginx.conf` and `php-fpm.conf`.
- Verify file format/newline concerns with line counts and `head` inspection.
- Add PHP zip extension and runtime library support to resolve the `openspout/openspout` `ext-zip` build blocker.

### Feedback Rejected

- Do not use the shorter v1.1 Dockerfile that drops accepted v0.9 runtime packages.
- Do not replace the entire `routes/console.php` file blindly.
- Do not default to two containers, two VETH interfaces, container-side IP mutation, provider-specific profile names, or simultaneous profile tests.
- Do not treat the RouterOS multi-address CLI command as final until validated on the target RouterOS version.

### Adopted Changes

- Planned branch split: `mikrotik-lite-standard` for stable single-ISP and `mikrotik-lite-multi-isp-exp` for experimental multi-ISP.
- Planned environment profile slots `isp1` through `isp5`.
- Planned `config/speedtest-lite.php` profile registry.
- Planned support classes `App\Support\SpeedtestLite\IspProfile` and `IspProfiles`.
- Planned additive result metadata migration for `isp_profile_key`, `isp_profile_name`, and `source_ip`.
- Planned commands `speedtest-lite:validate-isp-profiles` and `speedtest-lite:run-isp-profile`.
- Planned Dockerfile, entrypoint, scheduler, and Ookla job updates described in the archive.
- Recorded blocker resolutions `REF-20260514-02`, `REF-20260514-03`, and `REF-20260514-04`.

### Validation / Tests Performed

- Archive reports Docker config blocker resolved by confirming `docker/mikrotik-lite/nginx.conf` and `docker/mikrotik-lite/php-fpm.conf` exist and Dockerfile copy paths use the correct relative paths.
- Archive reports file format validation via line counts:
  - `docker/mikrotik-lite/Dockerfile.mikrotik-lite`: 66 lines
  - `docker/mikrotik-lite/nginx.conf`: 51 lines
  - `docker/mikrotik-lite/php-fpm.conf`: 28 lines
  - `docker/mikrotik-lite/entrypoint.sh`: 124 lines
- Archive reports planned zip validation with `php -m | grep -i zip` during Docker build.
- Remaining validation checklist includes static grep checks, Docker build/runtime checks, Artisan checks, source-IP visibility, Ookla bind-flag support, profile run commands, and SQLite result metadata checks.

### Remaining Risks / Follow-ups

- Prove whether the bundled Ookla CLI supports `--ip` or requires `--interface`.
- Confirm result tagging remains reliable while Lite uses `QUEUE_CONNECTION=sync`; async upstream behavior would need a different tagging strategy.
- UI filtering and profile-specific dashboard/chart work remain out of scope for v1.2.1.
- Stagger ISP schedules by at least 5 to 7 minutes on hAP ax3; do not run all profiles simultaneously.
- Treat 96 MB memory use as a test target, not a guarantee.
- Validate RouterOS VETH multi-address visibility with `ip -o addr show dev eth0` and `php artisan speedtest-lite:validate-isp-profiles`.
- Preserve `sqlite-libs`, `ca-certificates`, `tzdata`, and zip runtime support when changing the Dockerfile.

---

## 2026-05-14 - Handover Addendum: Implemented v1.2.1 File Set

### Date/Time

2026-05-14T19:58:52+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Processed handover from another AI agent for branch `mikrotik-lite-multi-isp-exp`. The handover confirms that the v1.2.1 multi-ISP experimental plan moved from archive/code assembly guidance into an implemented file set. Verified that the named files exist in the local project tree.

### Findings

- The implementation targets one Speedtest Tracker Lite container with profile-driven schedules and source-IP binding through RouterOS VETH configuration.
- Changes are isolated primarily under `docker/mikrotik-lite/` and application-level profile support files, preserving the main `docker/8.4/` configuration.
- The migration present on disk is `database/migrations/2026_05_14_000001_add_isp_profile_columns_to_results_table.php`.
- Static/file-format blockers described in the handover are resolved according to the handover and prior archive notes.
- The project is ready for Docker build syntax validation and runtime testing, not production-final validation.

### Reviewer Feedback Summary

Handover aligns with the v1.2.1 archive: preserve v0.9 MikroTik Lite baseline, add multi-ISP profile support, keep upstream maintenance schedules, add configurable Ookla binding, and validate RouterOS/source-IP behavior before treating the build as stable.

### Feedback Accepted

- Treat the handover as confirmation that the v1.2.1 file set has been applied locally.
- Record the concrete added files instead of leaving them only as planned work.
- Record the current next steps as Docker build validation, runtime Artisan checks, profile run testing, and RouterOS/Ookla binding verification.

### Feedback Rejected

- No handover items were rejected at this history stage.

### Adopted Changes

Verified added files:

- `docker/mikrotik-lite/nginx.conf`
- `docker/mikrotik-lite/php-fpm.conf`
- `docker/mikrotik-lite/.env.mikrotik-lite`
- `config/speedtest-lite.php`
- `app/Support/SpeedtestLite/IspProfile.php`
- `app/Support/SpeedtestLite/IspProfiles.php`
- `app/Console/Commands/ValidateIspProfiles.php`
- `app/Console/Commands/RunIspProfileSpeedtest.php`
- `database/migrations/2026_05_14_000001_add_isp_profile_columns_to_results_table.php`
- `CHANGELOG.md`
- `docker/mikrotik-lite/CHANGELOG.md`

Verified changed or existing implementation files:

- `docker/mikrotik-lite/Dockerfile.mikrotik-lite`
- `routes/console.php`
- `app/Jobs/Ookla/RunSpeedtestJob.php`
- `speedtest-tracker-mikrotik-lite-change-archive-v1.2.1-multi-isp-experimental.md`

### Validation / Tests Performed

- Verified all handover-listed files exist locally.
- `git status --short` from `Container/speedtest-tracker` currently reports untracked `.ai/` and `.github/agents/`; no additional tracked diff summary was available from that status output.
- Did not run Docker build or runtime Artisan checks during this CODELOAD history update.

### Remaining Risks / Follow-ups

- Run no-cache Docker build and confirm composer install passes with zip support.
- Validate runtime commands:
  - `php artisan schedule:list`
  - `php artisan speedtest-lite:validate-isp-profiles`
  - `php artisan speedtest-lite:run-isp-profile isp1`
- Verify the bundled Ookla CLI supports the configured bind option.
- Validate RouterOS VETH multi-IP visibility from inside the container.
- Confirm result rows receive `isp_profile_key`, `isp_profile_name`, and `source_ip` metadata after manual profile runs.

---

## 2026-05-14 - Code Review Handover: Build Pass and Laravel Boost Runtime Blocker

### Date/Time

2026-05-14T20:01:21+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Processed code-review handover for the MikroTik Lite multi-ISP experimental build on branch `mikrotik-lite-multi-isp-exp`. The handover reports that the Docker build and PHP extension validation now pass, but Laravel runtime boot fails in the built image because `Laravel\Boost\BoostServiceProvider` is registered while `composer install --no-dev` excludes Laravel Boost.

### Findings

- External review reports Dockerfile syntax/build, image export, PHP `ext-zip`, and runtime PHP module checks as passing.
- External review reports confirmed runtime modules: `intl`, `mbstring`, `pcntl`, `pdo_sqlite`, `sockets`, and `zip`.
- Local source check confirms `laravel/boost` exists in `composer.json` / `composer.lock`.
- Local source check does not currently find `Laravel\Boost\BoostServiceProvider` registered in `bootstrap/providers.php`; the current file lists only the app, Filament, and admin panel providers. This suggests the reported Boost blocker may be caused by auto-discovery or another provider registration path rather than an explicit `bootstrap/providers.php` entry.
- Local source check confirms `docker/mikrotik-lite/Dockerfile.mikrotik-lite` has zip support (`libzip-dev`, `docker-php-ext-install ... zip`, `RUN php -m | grep -i zip`, and runtime `libzip`).
- Local source check confirms the Dockerfile does not currently include the suggested `sed` removal of `BoostServiceProvider`.
- Local source check confirms `RunSpeedtestJob.php` preserves configurable bind option support via `speedtest-lite.bind_option`, allowing `--interface` or `--ip`.
- Local source check confirms `routes/console.php` imports `App\Actions\CheckForScheduledSpeedtests`, schedules one command per profile in `multi_isp_experimental` mode, and preserves upstream pruning and SQLite vacuum schedules.
- Local source check confirms `docker/mikrotik-lite/entrypoint.sh` sources `/config/.env`, so persistent `.env` values can override Docker `-e` values during shell validation; this matches the reported secondary env-override concern.

### Reviewer Feedback Summary

The review advances status from build-not-yet-proven to Docker build/PHP extension pass, with Laravel boot as the current blocker. The proposed fix is to keep `composer install --no-dev` for a low-footprint production image and remove `Laravel\Boost\BoostServiceProvider::class` from the production build registration path before runtime if the provider is present in the build context.

### Feedback Accepted

- Keep `composer install --no-dev`; do not solve the Boost runtime issue by installing dev dependencies.
- Treat the Docker build pass as an external review result, with local validation still pending for runtime boot and provider-removal behavior.
- Treat Ookla bind-option support as implemented in code, with runtime confirmation still needed for the built image.
- Treat the Laravel Boost runtime error as the current reported blocker until the built image is retested.
- Preserve configurable Ookla binding support in `RunSpeedtestJob.php`.
- Preserve upstream maintenance schedules in `routes/console.php`.
- Treat source-IP validation warnings on Docker Desktop as expected unless the test container actually has `192.168.99.250` and `192.168.99.251` on `eth0`.
- Track env precedence as a later improvement: Docker runtime env vars should ideally override values loaded from `/config/.env`.

### Feedback Rejected

- Do not install dev dependencies in the production MikroTik Lite image solely to satisfy Laravel Boost.
- Do not remove pruning, queue cleanup, or SQLite vacuum schedules while changing multi-ISP scheduling.

### Needs Clarification

- The handover says `Laravel\Boost\BoostServiceProvider::class` is probably registered in `bootstrap/providers.php`, but the current local file does not contain that provider. Before applying the suggested Dockerfile `sed` fix, re-check the exact build context or image contents that produced the runtime error.
- If the provider is already absent from source, the next investigation should verify whether an older image was tested, whether cached build layers were used, or whether another provider manifest/cache file still references Boost.

### Adopted Changes

- No source-code changes were applied during this history update.
- CODELOAD history was updated to record the external build/runtime review status, local source verification, accepted feedback, and unresolved Boost-provider investigation.

### Validation / Tests Performed

- Read current `CODELOAD_HISTORY.md`.
- Checked `docker/mikrotik-lite/Dockerfile.mikrotik-lite`.
- Checked `bootstrap/providers.php`.
- Searched for `laravel/boost`, `BoostServiceProvider`, and `Boost`.
- Verified bind-option logic in `app/Jobs/Ookla/RunSpeedtestJob.php`.
- Verified scheduler/import behavior in `routes/console.php`.
- Verified entrypoint env loading behavior in `docker/mikrotik-lite/entrypoint.sh`.
- Did not run Docker build, Docker runtime, or Artisan commands during this history update.

### Remaining Risks / Follow-ups

- Rebuild with `--no-cache` and confirm whether the Boost provider runtime error still occurs.
- If the error persists, inspect the built image's `/var/www/html/bootstrap/providers.php` and cached Laravel manifests for `Laravel\Boost\BoostServiceProvider`.
- If the provider is present in the build context, add the suggested production-image removal before `composer install --no-dev`; if absent, avoid adding unnecessary Dockerfile mutation.
- Retest PHP modules in the ARM64 image.
- Retest Laravel boot with `php artisan list` and verify the output includes `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles`.
- Later, adjust entrypoint env precedence so Docker `-e` values can override `/config/.env` during local runtime testing.

## 2026-05-14 - Validation and Fix: Boost Cache Manifests and Ookla Binary

### Date/Time

2026-05-14T20:52:32+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Retried the Docker validation after Docker Desktop was started. Confirmed the Laravel Boost runtime blocker, traced it to stale Laravel cache manifests copied into the production image, fixed the Docker build path, rebuilt the ARM64 image, then fixed a newly surfaced missing Ookla `speedtest` binary blocker.

### Findings

- The first no-cache build attempt ran long enough to produce an image despite the shell timeout.
- PHP module validation passed with `intl`, `mbstring`, `pcntl`, `pdo_sqlite`, `sockets`, and `zip`.
- Artisan boot initially failed with `Class "Laravel\Boost\BoostServiceProvider" not found`.
- Built-image inspection showed `bootstrap/providers.php` was clean, but `bootstrap/cache/packages.php` and `bootstrap/cache/services.php` referenced `Laravel\Boost\BoostServiceProvider`.
- Local `bootstrap/cache/packages.php` and `bootstrap/cache/services.php` also contained stale Boost references even though `bootstrap/cache/.gitignore` ignores generated cache files.
- After removing generated cache manifests from the build context/image, Artisan boot succeeded and the `speedtest-lite` commands were registered.
- A second runtime blocker surfaced: the Lite image did not include the Ookla `speedtest` binary.
- After adding the Ookla binary download and Alpine compatibility package, `speedtest --version` succeeded in the ARM64 image.

### Reviewer Feedback Summary

The reviewer-reported Boost blocker was valid, but the root cause was stale cached Laravel package/service manifests rather than active registration in `bootstrap/providers.php`. The fix avoids installing dev dependencies and avoids mutating `bootstrap/providers.php`; instead, it prevents generated cache manifests from entering or surviving the Docker build.

### Feedback Accepted

- Keep `composer install --no-dev`.
- Rebuild and verify before applying provider changes.
- Preserve multi-ISP bind-option logic and scheduler behavior.
- Treat Docker Desktop source-IP warnings as expected during local tests unless the container has the RouterOS VETH source IPs assigned.

### Feedback Rejected

- Do not install Laravel Boost/dev dependencies in the production image.
- Do not add the suggested `sed` deletion for `Laravel\Boost\BoostServiceProvider::class` because current `bootstrap/providers.php` does not contain that provider.

### Adopted Changes

- Added `.dockerignore` with:
  - `bootstrap/cache/*.php`
- Updated `docker/mikrotik-lite/Dockerfile.mikrotik-lite` to remove generated Laravel cache manifests before `composer install --no-dev`:
  - `bootstrap/cache/packages.php`
  - `bootstrap/cache/services.php`
  - `bootstrap/cache/config.php`
- Updated `docker/mikrotik-lite/Dockerfile.mikrotik-lite` to install the Ookla CLI:
  - Added `ARG SPEEDTEST_VERSION=1.2.0`.
  - Added Alpine `gcompat`.
  - Downloaded the architecture-specific `ookla-speedtest` tarball.
  - Installed `speedtest` to `/usr/local/bin`.

### Validation / Tests Performed

- Rebuilt image:
  - `docker buildx build --no-cache --platform linux/arm64 -f docker/mikrotik-lite/Dockerfile.mikrotik-lite --tag arjayalsonado/speedtest-tracker:multi-isp-exp-arm64 --load .`
  - Initial command timed out in the shell, but image inspection showed the image was produced.
- Inspected the built image and found stale Boost references in:
  - `/var/www/html/bootstrap/cache/packages.php`
  - `/var/www/html/bootstrap/cache/services.php`
- Rebuilt after cache-manifest fix successfully.
- Verified PHP modules with `php -m`; required modules present:
  - `intl`
  - `mbstring`
  - `pcntl`
  - `pdo_sqlite`
  - `sockets`
  - `zip`
- Verified no Boost references remained in built-image `bootstrap/cache` or `bootstrap/providers.php`.
- Verified `php artisan list` boots and registers:
  - `speedtest-lite:run-isp-profile`
  - `speedtest-lite:validate-isp-profiles`
- Verified migrations run during entrypoint, including:
  - `2026_05_14_000001_add_isp_profile_columns_to_results_table`
- Verified `php artisan schedule:list` includes:
  - `model:prune`
  - `queue:prune-batches`
  - `queue:prune-failed`
  - `speedtest-lite:run-isp-profile isp1`
  - `speedtest-lite:run-isp-profile isp2`
  - `sqlite-vacuum`
- Verified `php artisan speedtest-lite:validate-isp-profiles` runs; expected local Docker warnings report `192.168.99.250` and `192.168.99.251` are not visible on `eth0`.
- Verified Ookla binary:
  - `command -v speedtest` returned `/usr/local/bin/speedtest`.
  - `speedtest --version` returned `Speedtest by Ookla 1.2.0.84 ... Linux/aarch64-linux-musl ...`.
- Verified latest image exists:
  - `arjayalsonado/speedtest-tracker:multi-isp-exp-arm64`
  - Image ID: `fa61de651e96`
  - Size: `130MB`

### Remaining Risks / Follow-ups

- Local Docker Desktop cannot validate RouterOS VETH source IP visibility; test on MikroTik hAP ax3 or a container with the profile source IPs assigned.
- Manual profile execution still needs a real network/source-IP environment:
  - `php artisan speedtest-lite:run-isp-profile isp1`
  - `php artisan speedtest-lite:run-isp-profile isp2`
- Confirm whether `SPEEDTEST_LITE_BIND_OPTION=--ip` is accepted by the installed Ookla CLI during a real run; switch to `--interface` only if required.
- Consider improving entrypoint env precedence so Docker `-e` values can override `/config/.env` during local runtime tests.
- Decide whether to remove local ignored generated cache files from `bootstrap/cache` outside the Docker image path.

---

## 2026-05-14 - Current State Snapshot for Focused Review

### Reference ID

REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT

### Date/Time

2026-05-14T21:00:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Optimized this CODELOAD history so the current reviewed state is clear. Stale review notes from before Docker Desktop was available were removed because they were superseded by direct local build and runtime validation. This entry is the recommended focus point for the next Copilot review.

### Current State

- Branch context: `mikrotik-lite-multi-isp-exp`.
- Stable fallback branch context: `mikrotik-lite-standard`.
- Architecture remains one container, one RouterOS VETH, up to five generic ISP profiles, one source IP per profile, RouterOS source-based policy routing, and SQLite storage.
- Docker build now completes for `linux/arm64`.
- Laravel Boost runtime failure is fixed by excluding/removing generated Laravel cache manifests, not by installing dev dependencies and not by editing `bootstrap/providers.php`.
- Generated Laravel cache manifests under `bootstrap/cache/*.php` are a persistent build hazard when copied from a dev workspace; `.dockerignore` plus Dockerfile cleanup is the intended long-term mitigation.
- Ookla `speedtest` binary is now installed in the MikroTik Lite image and verified with `speedtest --version`.
- `speedtest-lite` Artisan commands are registered and visible.
- Scheduler includes both multi-ISP profile jobs and upstream maintenance tasks.
- Entrypoint handles volume layout and env sourcing; Docker `-e` runtime values are preserved over `/config/.env` for shell-level validation.
- Local Docker Desktop validation cannot prove RouterOS source-IP visibility because the test container does not have the RouterOS VETH IPs on `eth0`.
- Status is ready for RouterOS validation, not production release; source-IP binding and real profile executions still require MikroTik/VETH testing.

### Files / Changes To Review

Focused latest Docker/cache/Ookla fixes:

- `.dockerignore`
- `docker/mikrotik-lite/Dockerfile.mikrotik-lite`
- `.ai/CODELOAD_HISTORY.md`

Relevant previously implemented runtime files behind the validated Artisan and scheduler behavior:

- `docker/mikrotik-lite/entrypoint.sh`
- `docker/mikrotik-lite/.env.mikrotik-lite`
- `config/speedtest-lite.php`
- `app/Support/SpeedtestLite/IspProfile.php`
- `app/Support/SpeedtestLite/IspProfiles.php`
- `app/Console/Commands/ValidateIspProfiles.php`
- `app/Console/Commands/RunIspProfileSpeedtest.php`
- `app/Jobs/Ookla/RunSpeedtestJob.php`
- `routes/console.php`
- `database/migrations/2026_05_14_000001_add_isp_profile_columns_to_results_table.php`

### Validation Confirmed

- Rebuilt image: `arjayalsonado/speedtest-tracker:multi-isp-exp-arm64`.
- Successful final rebuild command:
  - `docker buildx build --platform linux/arm64 -f docker/mikrotik-lite/Dockerfile.mikrotik-lite --tag arjayalsonado/speedtest-tracker:multi-isp-exp-arm64 --load .`
- Earlier no-cache rebuild command was also run:
  - `docker buildx build --no-cache --platform linux/arm64 -f docker/mikrotik-lite/Dockerfile.mikrotik-lite --tag arjayalsonado/speedtest-tracker:multi-isp-exp-arm64 --load .`
- Build success was confirmed by direct Docker command completion, `docker image ls`, and runtime `docker run` checks.
- Latest validated image ID: `8b03022f5c0a`.
- Latest validated image size: `130MB`.
- PHP modules present: `intl`, `mbstring`, `pcntl`, `pdo_sqlite`, `sockets`, `zip`.
- `php artisan list` boots and includes:
  - `speedtest-lite:run-isp-profile`
  - `speedtest-lite:validate-isp-profiles`
- `php artisan schedule:list` includes:
  - `model:prune`
  - `queue:prune-batches`
  - `queue:prune-failed`
  - `speedtest-lite:run-isp-profile isp1`
  - `speedtest-lite:run-isp-profile isp2`
  - `sqlite-vacuum`
- Built image no longer contains `Laravel\Boost\BoostServiceProvider` references in `bootstrap/providers.php` or generated cache manifests.
- `speedtest --version` returns `Speedtest by Ookla 1.2.0.84 ... Linux/aarch64-linux-musl ...`.
- `speedtest --version` only proves the binary exists and runs; it does not prove `--ip` or `--interface` source binding works in the RouterOS VETH environment.
- Docker `-e SPEEDTEST_LITE_ISP_PROFILES=` is now honored by the entrypoint and suppresses default profile validation warnings.
- Docker `-e SPEEDTEST_LITE_VALIDATE_SOURCE_IPS=false` is now honored by the entrypoint and skips shell-level source-IP validation.

### Remaining Risks / Follow-ups

- Test on MikroTik hAP ax3 or an equivalent container network where `192.168.99.250` and `192.168.99.251` are assigned to `eth0`.
- Run real profile executions:
  - `php artisan speedtest-lite:run-isp-profile isp1`
  - `php artisan speedtest-lite:run-isp-profile isp2`
- Confirm whether `SPEEDTEST_LITE_BIND_OPTION=--ip` works with the installed Ookla CLI during actual profile execution; code falls back to `--interface` for invalid bind options, while the Lite env template currently defaults to `--ip` pending RouterOS validation.
- Decide whether to delete local ignored generated files under `bootstrap/cache` from the workspace.
- Do not re-add generated `bootstrap/cache/*.php` files to the Docker build context.

### Copilot Review Prompt

Review reference `REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT`. Focus on whether the current state, validation claims, remaining risks, and file-change scope are accurate and sufficient. Review only; do not implement.

Use this review header:

```text
# Copilot Review - CODELOAD Current State
Reference ID: REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT
```

Then provide:

1. Blocking issues
2. Suggested improvements
3. False-positive risk or uncertainty
4. Approval status

---

## 2026-05-14 - Copilot Review Assessment: CODELOAD Current State

### Reference ID

REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT

### Date/Time

2026-05-14T21:05:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Assessed Copilot's focused review for `REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT` and incorporated the applicable documentation improvements into the current-state snapshot.

### Findings

- Copilot labeled the issues as blocking, but they are documentation/scope/provenance improvements rather than project blockers.
- The file-change scope concern was valid and has been addressed by listing both the focused latest Docker/cache/Ookla files and the previously implemented runtime files behind the validated behavior.
- The validation provenance concern was valid and has been addressed by adding exact Docker build commands and the confirmation mechanism.
- The stale cache manifest durability concern was valid and has been addressed by explicitly documenting `bootstrap/cache/*.php` as a persistent build hazard mitigated by `.dockerignore` and Dockerfile cleanup.
- The `speedtest --version` limitation was valid and has been addressed by stating that real `--ip` / `--interface` binding still requires RouterOS/VETH profile execution testing.

### Reviewer Feedback Summary

Copilot approved the current state with comments. Its feedback was applicable to CODELOAD history clarity, not source-code correctness. The accepted items were folded into the focused snapshot so the reference section is now more self-contained for future review.

### Feedback Accepted

- Broaden or justify `Files / Changes To Review`.
- Add exact Docker build commands and validation provenance.
- Make generated Laravel cache manifest risk and mitigation explicit.
- Clarify that `speedtest --version` is necessary but not sufficient for source binding validation.

### Feedback Rejected

- Treating the comments as blocking implementation issues. They were documentation improvements.

### Adopted Changes

- Updated the current-state snapshot for `REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT`.
- Kept the template entry at the bottom of the history file.

### Validation / Tests Performed

- Read Copilot's appended review.
- Compared each review item with the current project validation state.
- Updated only `.ai/CODELOAD_HISTORY.md`.

---

## 2026-05-14 - Diff Review: Multi-ISP Runtime Files and Build Fixes

### Reference ID

REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT

### Date/Time

2026-05-14T21:00:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Reviewed the current diff for `REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT`, focusing on build/runtime regressions, stale cache risks, Docker image/package risks, Ookla binary/bind behavior, scheduler preservation, result tagging, and pre-deployment validation.

### Findings

- No build/runtime regressions detected; Dockerfile correctly removes cache manifests, entrypoint handles volume layout and env sourcing, and Artisan commands register properly.
- Stale Laravel cache manifest risk mitigated by .dockerignore exclusion and Dockerfile cleanup; no Boost references in current build context.
- Docker image size (130MB) and runtime packages reasonable; only required Alpine packages, Ookla binary, iproute2 added.
- Ookla binary install correct with architecture detection/version pinning; bind-option is configurable in `RunSpeedtestJob.php`, invalid values fall back to `--interface`, and the Lite env template currently defaults to `--ip` pending RouterOS validation.
- Scheduler preserves upstream maintenance (prune, vacuum) and adds profile-specific jobs in multi-ISP mode; no conflicts.
- Result tagging supported by migration adding isp_profile_key/isp_profile_name/source_ip columns; commands set via config overrides.
- Pre-deployment validation provided by speedtest-lite:validate-isp-profiles command for IP visibility; no code blockers.

### Reviewer Feedback Summary

The diff review confirms alignment with the reference, no regressions, effective risk mitigations, solid multi-ISP implementation. Branch ready for RouterOS validation, but production awaits real-environment testing.

### Feedback Accepted

- Keep Dockerfile cache-manifest cleanup and comment to prevent dev-only package runtime errors.
- Update history to include the timestamped reference ID and keep the review result attached to the active current-state snapshot.

### Suggested Improvements / Pending

- Add bind-option logging in `RunIspProfileSpeedtest.php` for easier RouterOS validation debugging.
- Review or confirm the migration `down()` behavior, especially SQLite column/index rollback behavior, before relying on rollback in test environments.
- Consider changing entrypoint env precedence so Docker `-e` values can override `/config/.env` during local runtime tests.

### Feedback Rejected

- Treating pending suggestions as already implemented source changes. No source changes were applied during this review entry.

### Adopted Changes

- No source changes applied during this review.
- CODELOAD_HISTORY.md updated with this diff review entry.

### Validation / Tests Performed

- Reviewed .dockerignore, Dockerfile.mikrotik-lite, entrypoint.sh, config/speedtest-lite.php, app/Support/SpeedtestLite/*, app/Console/Commands/*, app/Jobs/Ookla/RunSpeedtestJob.php, routes/console.php, migration file.
- Checked for regressions, risks, behavior accuracy against reference.

### Remaining Risks / Follow-ups

- Optionally re-run build with `--no-cache` before release or publication.
- Test Ookla --ip vs --interface in RouterOS VETH environment.
- Run real profile executions on MikroTik hAP ax3.
- Confirm result tagging in async mode if needed later.

---

## 2026-05-14 - Final Review: CODELOAD History Accuracy for Multi-ISP Reference

### Date/Time

2026-05-14T21:10:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Reviewed `.ai/CODELOAD_HISTORY.md` for accuracy of current state, validation claims, remaining risks, pending suggestions, and file-change scope against REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT.

### Findings

- Current state accurately reflects the MikroTik Lite multi-ISP experimental build with no unresolved blockers.
- Validation claims supported by detailed Docker commands, image checks, runtime verifications; no false claims.
- Remaining risks clearly documented and appropriate for experimental branch, focusing on RouterOS testing.
- Pending suggestions incorporated into snapshot, including broadened file scope, explicit cache mitigation, bind-option limitations.
- File-change scope comprehensive, listing latest fixes and runtime files behind validated behavior.

### Reviewer Feedback Summary

Approved the history as accurate. Suggestions enhance clarity but are not required for correctness.

### Approval Status

Approved with comments.

### Feedback Accepted

- Add production caution note in current state.
- Prioritize RouterOS source-IP test in remaining risks.
- Keep Copilot review assessment for audit trail.
- Update review prompt to use timestamped reference ID.

### Feedback Rejected

- No rejections; all aspects approved.

### Adopted Changes

- No source changes; history documentation refined.

### Validation / Tests Performed

- Read updated CODELOAD_HISTORY.md sections.
- Verified alignment with reference and incorporated improvements.

### Remaining Risks / Follow-ups

- Test RouterOS source-IP visibility on MikroTik hAP ax3.
- Run real profile executions and confirm Ookla bind options.
- Consider entrypoint env precedence for testing.

---

## 2026-05-14 - Codex Finalization: CODELOAD History Closed for Now

### Reference ID

REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT

### Date/Time

2026-05-14T21:15:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Finalized CODELOAD history for the current MikroTik Lite multi-ISP experimental state so work can move from documentation review back to runtime validation.

### Findings

- No known local Docker build or Laravel boot blocker remains.
- CODELOAD now clearly separates validated local state from RouterOS production-readiness gaps.
- Latest reviewer feedback is treated as approved with comments, with RouterOS validation still required.

### Reviewer Feedback Summary

Copilot feedback was applicable for documentation clarity. No source-code changes were required from the final history review.

### Feedback Accepted

- Add production caution that the branch is ready for RouterOS validation, not production release.
- Keep RouterOS source-IP visibility and real profile execution as the primary next checks.
- Keep timestamped reference ID as the active review anchor.

### Feedback Rejected

- Treating CODELOAD cleanup as a reason to delay runtime validation further.

### Adopted Changes

- Updated `.ai/CODELOAD_HISTORY.md` only.
- Kept `Template Entry` as the final section.

### Validation / Tests Performed

- Re-read the current-state snapshot and latest appended review.
- Confirmed the active reference ID remains `REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT`.

### Remaining Risks / Follow-ups

- Move next to MikroTik/RouterOS runtime validation.
- Test source-IP visibility and real profile runs for `isp1` and `isp2`.
- Confirm whether Ookla binding should use `--ip` or `--interface` in the target environment.

---

## 2026-05-15 - Runtime Validation Resume: Entrypoint Env Override Fixed

### Reference ID

REF-CODELOAD-20260514-210000-MIKROTIK-LITE-MULTI-ISP-CURRENT

### Date/Time

2026-05-15T00:48:47+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Resumed after interruption and fixed the entrypoint environment precedence issue found during local Docker validation.

### Findings

- `/config/.env` previously overwrote Docker `-e` values during shell-level validation.
- `docker/mikrotik-lite/entrypoint.sh` now snapshots runtime env values, sources `/config/.env`, then restores runtime values so Docker `-e` takes precedence.
- Rebuilt ARM64 image successfully as `arjayalsonado/speedtest-tracker:multi-isp-exp-arm64`.

### Reviewer Feedback Summary

This resolves a previously pending local-testing improvement. It does not replace RouterOS/VETH runtime validation.

### Feedback Accepted

- Fix Docker runtime env precedence for `SPEEDTEST_LITE_ISP_PROFILES` and `SPEEDTEST_LITE_VALIDATE_SOURCE_IPS`.

### Feedback Rejected

- Treating local env override validation as proof of RouterOS source-IP binding.

### Adopted Changes

- Updated `docker/mikrotik-lite/entrypoint.sh`.
- Updated `.ai/CODELOAD_HISTORY.md`.

### Validation / Tests Performed

- Rebuilt image with `docker buildx build --platform linux/arm64 -f docker/mikrotik-lite/Dockerfile.mikrotik-lite --tag arjayalsonado/speedtest-tracker:multi-isp-exp-arm64 --load .`.
- Confirmed image ID `8b03022f5c0a`.
- Confirmed `SPEEDTEST_LITE_ISP_PROFILES=` suppresses default ISP warning output.
- Confirmed `SPEEDTEST_LITE_VALIDATE_SOURCE_IPS=false` skips source-IP validation.
- Confirmed `php artisan schedule:list` still includes upstream maintenance tasks and `isp1`/`isp2` profile schedules.

### Remaining Risks / Follow-ups

- Run on MikroTik/RouterOS with VETH IPs assigned to `eth0`.
- Execute real `isp1` and `isp2` profile speed tests.
- Confirm whether Ookla binding should use `--ip` or `--interface` in the target environment.

---

## 2026-05-15 - RouterOS Runtime Validation and Image Versioning

### Reference ID

REF-CODELOAD-20260515-MIKROTIK-LITE-ROUTEROS-RUNTIME-VALIDATION

### Date/Time

2026-05-15T01:25:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Validated the MikroTik Lite multi-ISP image on RouterOS hAP ax3 and established image tagging/versioning conventions for repeatable deployment.

### Findings

- Docker Hub namespace for deployment is `rvncore/speedtest-tracker`.
- Published/current RouterOS-pulled tag: `rvncore/speedtest-tracker:multi-isp-exp-arm64`.
- RouterOS successfully pulled/extracted and started `app-speedtest`.
- Runtime container memory at first successful boot was about `83.4MiB` with `memory-high=128M`.
- VETH interface inside the container appeared as `veth-app-speedt`, not `eth0`, due RouterOS interface-name truncation.
- `SPEEDTEST_LITE_BIND_INTERFACE` was updated to `veth-app-speedt`.
- Source-IP validation passed:
  - `isp1` / CNVG / `192.168.99.250` visible.
  - `isp2` / PLDT / `192.168.99.251` visible.
- Manual profile runs completed and tagged results:
  - `isp1` / CNVG / `192.168.99.250`.
  - `isp2` / PLDT / `192.168.99.251`.
- Automatic scheduler completed `isp1` / CNVG as result `#7`.
- Automatic scheduler completed `isp2` / PLDT as result `#8`.
- Increasing RouterOS `memory-high` from `128M` to `160M` may have resolved early automatic-run failures.
- Earlier automatic attempts left untagged `waiting` rows, likely from initial setup instability, memory pressure, timeout, or scheduler warmup.

### Reviewer Feedback Summary

Runtime validation confirmed the core architecture: one RouterOS container, one VETH with multiple source IPs, profile-specific schedules, source-IP visibility, manual profile execution, and at least one automatic scheduled completion.

### Feedback Accepted

- Use semantic versioning plus build numbers for deployment images.
- Keep a moving experimental tag for convenience but prefer immutable version/build tags on RouterOS.
- Keep scheduler/Ookla timeout hardening as a patch-level improvement.

### Feedback Rejected

- Timestamp-only Docker tags as the primary deployment naming scheme.

### Adopted Changes

- Locally built timeout-hardened ARM64 image from current source.
- Added Ookla runtime hardening:
  - `app/Jobs/Ookla/RunSpeedtestJob.php`: job timeout raised from `120` to `300`.
  - `app/Jobs/Ookla/RunSpeedtestJob.php`: Symfony process timeout set to `300`.
  - `app/Jobs/Ookla/RunSpeedtestJob.php`: bind option/value logging added.
- Note: `routes/console.php` scheduler-level `->timeout(300)` was later found incompatible with this Laravel version and removed in build `b1.1`.

### Image Tags / Versioning

Versioning convention:

- `MAJOR.MINOR.PATCH` follows SemVer-style meaning:
  - `MAJOR`: breaking or incompatible architecture changes.
  - `MINOR`: backwards-compatible features.
  - `PATCH`: fixes, hardening, or small improvements.
- Build suffix `bX.Y` tracks build line and build iteration within the same version.
- Increment build major `X` for significant build/release-line changes; increment build minor `Y` for smaller rebuilds of the same line.
- Preferred immutable deployment tag format:
  - `rvncore/speedtest-tracker:<version>-b<X.Y>-mikrotik-lite-multi-isp-arm64`
- Convenience tags:
  - `rvncore/speedtest-tracker:<version>-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`

Historical local timeout-hardened build:

- Image ID: `a06581e660d5`.
- Local tags:
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`
  - `rvncore/speedtest-tracker:0.1.1-b1.0-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:latest`
- Status: build `b1.0` was later found broken on RouterOS because scheduler `Event::timeout` is unsupported. Do not deploy this build.
- Corrected build: `0.1.1-b1.1-mikrotik-lite-multi-isp-arm64`.

Manual push commands for corrected build:

```powershell
docker tag rvncore/speedtest-tracker:multi-isp-exp-arm64 rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64
docker tag rvncore/speedtest-tracker:multi-isp-exp-arm64 rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64
docker tag rvncore/speedtest-tracker:multi-isp-exp-arm64 rvncore/speedtest-tracker:latest
docker push rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64
docker push rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64
docker push rvncore/speedtest-tracker:multi-isp-exp-arm64
docker push rvncore/speedtest-tracker:latest
```

### Validation / Tests Performed

- RouterOS container reached running state with ARM64 image.
- `ip a` inside container showed:
  - `192.168.99.249/24`
  - `192.168.99.250/24`
  - `192.168.99.251/24`
- `php artisan speedtest-lite:validate-isp-profiles` reported both CNVG and PLDT visible.
- `php artisan schedule:list` showed upstream maintenance tasks plus profile schedules.
- Manual `php artisan speedtest-lite:run-isp-profile isp1` completed and tagged a CNVG result.
- Manual `php artisan speedtest-lite:run-isp-profile isp2` completed and tagged a PLDT result.
- Automatic scheduler completed CNVG result `#7`.
- Local syntax checks passed:
  - `php -l routes/console.php`
  - `php -l app/Jobs/Ookla/RunSpeedtestJob.php`
- Local Docker build passed for timeout-hardened image with `--provenance=false --load`.

### Remaining Risks / Follow-ups

- Confirm external/public IP or ISP differs per profile to prove source-policy routing end to end.
- Decide whether to deploy the timeout-hardened `0.1.1` build immediately or keep observing the current RouterOS image.
- Consider renaming RouterOS VETH to a shorter name such as `veth-speedtest` to avoid interface-name truncation surprises.
- Clean or mark abandoned `waiting` rows from early validation after scheduler behavior is fully confirmed.

---

## 2026-05-15 - Resume Checkpoint: Pending Items

### Reference ID

REF-CODELOAD-20260515-MIKROTIK-LITE-RESUME-CHECKPOINT

### Date/Time

2026-05-15T01:35:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Paused after successful RouterOS runtime validation and local timeout-hardened image build. Use this entry to resume later.

### Findings

- Current RouterOS deployment using `rvncore/speedtest-tracker:multi-isp-exp-arm64` is running.
- Automatic scheduled runs completed for both profiles after memory/runtime stabilization:
  - CNVG result `#7`.
  - PLDT result `#8`.
- RouterOS `memory-high=160M` may have resolved early automatic-run failures that left abandoned `waiting` rows.
- Timeout-hardened image `b1.0` was built and pushed manually, but was later found broken on RouterOS.
- Broken `b1.0` image ID: `a06581e660d5`.
- Historical local tags on the broken image:
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`
  - `rvncore/speedtest-tracker:0.1.1-b1.0-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:latest`
- Pushed Docker Hub tags now verified by `docker manifest inspect`:
  - `rvncore/speedtest-tracker:0.1.1-b1.0-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:latest`
- Both verified tags pointed to config digest `sha256:8c33eb11060727539d511aeffe3d87cacaa8dfae0f8d8a69944844c1479c2940`; this digest belongs to broken build `b1.0`.

### Reviewer Feedback Summary

No new reviewer feedback pending. Continue from operational/deployment tasks.

### Feedback Accepted

- Use SemVer-style image versions plus build numbers.
- Prefer immutable build tags for MikroTik deployment.
- Use `latest` as the convenient MikroTik/client repull tag while keeping immutable version/build tags for specific releases and rollback.

### Feedback Rejected

- Timestamp-only tags as the main deployment versioning scheme.
- Keeping timestamp tags now that SemVer plus build numbers are the preferred tracking scheme.

### Adopted Changes

- Documentation updated with runtime validation, timeout hardening, memory finding, and image tagging convention.
- Local source includes supported timeout hardening:
  - `app/Jobs/Ookla/RunSpeedtestJob.php`: job/process timeout `300s` and bind logging.
  - Scheduler-level `routes/console.php` timeout was removed in corrected build `b1.1` because the current Laravel scheduler `Event` does not support `timeout()`.

### Validation / Tests Performed

- RouterOS validation completed for source-IP visibility.
- Manual profile runs completed for CNVG and PLDT.
- Automatic scheduled runs completed for CNVG and PLDT.
- Local Docker build completed for timeout-hardened ARM64 image.
- Docker Hub manifest inspection completed for the immutable `0.1.1-b1.0` tag and `latest` tag.
- Removed obsolete local `b001` tag after switching to `bX.Y` build suffixes.
- Timestamp tag was already absent locally when checked.
- End-to-end source-policy routing confirmed from completed results:
  - CNVG `isp1` / source `192.168.99.250` / public IP `136.158.32.96` / ISP `Converge`.
  - PLDT `isp2` / source `192.168.99.251` / public IP `112.208.182.118` / ISP `PLDT`.
  - Repeated scheduled rows on `2026-05-19` show consistent per-profile routing.

### Remaining Risks / Follow-ups

- Push corrected build `b1.1`, then use `latest` as the normal MikroTik repull/deployment tag while retaining pinned version/build tags for rollback:
  - `rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64`
- Investigate web UI at `http://192.168.99.249` returning `500 Internal Server Error` after container deployment.
- Clean or mark abandoned early `waiting` rows after validation is complete.
- Consider renaming RouterOS VETH to a shorter name such as `veth-speedtest` to avoid interface-name truncation.
- Consider committing source/docs changes once reviewed.

---

## 2026-05-19 - Build b1.0 Broken, b1.1 Corrected

### Reference ID

REF-CODELOAD-20260519-MIKROTIK-LITE-BUILD-B1-1-FIX

### Date/Time

2026-05-19T00:00:00+08:00

### Project / Path

Container/speedtest-tracker

### Task / Change Summary

Tracked a boot failure in image build `0.1.1-b1.0` and built corrected local image `0.1.1-b1.1`.

### Findings

- Broken pushed tag:
  - `rvncore/speedtest-tracker:0.1.1-b1.0-mikrotik-lite-multi-isp-arm64`
- Broken local image ID:
  - `a06581e660d5`
- Failure observed on RouterOS container `app-speedtest-v011`.
- Runtime error:
  - `Method Illuminate\Console\Scheduling\Event::timeout does not exist.`
- Root cause:
  - `routes/console.php` used `->timeout(300)` on Laravel scheduler `Event`, but this application/Laravel version does not support that method.
- Impact:
  - Container boot failed while loading `routes/console.php`.
  - Image exited with status `1`.
- Corrective action:
  - Removed scheduler-level `->timeout(300)` from `routes/console.php`.
  - Kept supported timeout hardening in `app/Jobs/Ookla/RunSpeedtestJob.php`:
    - Job timeout `300`.
    - Symfony process timeout `300`.
    - Bind option/value logging.

### Reviewer Feedback Summary

No external reviewer feedback. This was validated from RouterOS runtime logs and local source inspection.

### Feedback Accepted

- Treat `0.1.1-b1.0` as broken and do not deploy it.
- Use build suffix increment from `b1.0` to `b1.1` for the corrected image.

### Feedback Rejected

- Keeping scheduler-level `->timeout(300)` as a hardening change; it is incompatible with the current scheduler API.

### Adopted Changes

- Updated `routes/console.php` to remove unsupported scheduler timeout call.
- Built corrected local ARM64 image.

### Image Tags / Build State

Corrected local image ID:

- `40644087e312`
- Full ID: `sha256:40644087e312a3a41479cd7894ba4e9bbbf11db0bec0ca7cb40984043f4a67ab`

Corrected local tags:

- `rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:latest`
- `rvncore/speedtest-tracker:multi-isp-exp-arm64`

Broken local tag retained for traceability until cleanup decision:

- `rvncore/speedtest-tracker:0.1.1-b1.0-mikrotik-lite-multi-isp-arm64`

### Validation / Tests Performed

- `php -l routes/console.php`: passed.
- `php -l app/Jobs/Ookla/RunSpeedtestJob.php`: passed.
- Local Docker build completed with:
  - `--platform linux/arm64`
  - `--provenance=false`
  - `--load`
- Local Docker image smoke tests completed after build:
  - PHP module check includes `intl`, `mbstring`, `pcntl`, `pdo_sqlite`, `sockets`, and `zip`.
  - `php artisan list` boots successfully and lists `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles`.

### Remaining Risks / Follow-ups

- Manually push corrected `b1.1` tags to Docker Hub.
- Repull/redeploy `latest` or pinned `0.1.1-b1.1` on MikroTik.
- Remove or deprecate broken `0.1.1-b1.0` tag from Docker Hub if it was pushed.
- Confirm corrected image boots on RouterOS without the scheduler `Event::timeout` error.

---

## 2026-05-19 15:48:43 +08:00 - Scheduler Timeout API Verification

### Reference

`REF-CODELOAD-20260519-LARAVEL-SCHEDULER-TIMEOUT-API-VERIFY`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Verified whether Laravel supports a scheduler-level timeout method for:

```php
Schedule::command(...)->timeout(...)
```

### Findings

- The invalid scheduler-level `->timeout(300)` call was introduced during the local timeout-hardening pass, not inherited from upstream source.
- The installed framework version is `Laravel Framework 13.8.0`.
- The local scheduler source does not expose `Illuminate\Console\Scheduling\Event::timeout()`.
- Official Laravel 13 scheduling documentation does not document a scheduler event `timeout()` method.
- Laravel timeout support exists in queue/job handling, not as a scheduler event chain in this installed version.

### Adopted Interpretation

- Keep timeout protection in `app/Jobs/Ookla/RunSpeedtestJob.php`:
  - job timeout: `public $timeout = 300;`
  - Ookla CLI process timeout: `$process->setTimeout(300);`
- Do not reintroduce scheduler-level `->timeout(300)` in `routes/console.php`.
- Treat build `0.1.1-b1.0` as broken because it included the unsupported scheduler API call.
- Treat build `0.1.1-b1.1` as the corrected build for this issue.

### Validation / Tests Performed

- `php artisan --version`
- Local source search under `vendor/laravel/framework/src/Illuminate/Console/Scheduling`
- Local source search under `vendor/laravel/framework/src/Illuminate/Queue`
- Official documentation check:
  - Laravel 13 task scheduling docs
  - Laravel 13 queue timeout docs

### Remaining Risks / Follow-ups

- Confirm the corrected `b1.1` image boots on RouterOS after push/redeploy.
- Keep documentation explicit that scheduler timeout and queue/process timeout are different mechanisms.

---

## 2026-05-19 15:50:16 +08:00 - Documentation Gap Fill

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-DOCS-GAP-FILL`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Filled the missing project documentation for the MikroTik Lite multi-interface build and removed the active history placeholder template from this file.

### Findings

- The active history file still contained a reusable `Template Entry` with `Pending` placeholders.
- The project had changelog entries and detailed history, but no concise operator-facing MikroTik Lite deployment/runbook document.
- The root `README.md` still only described the upstream Speedtest Tracker project and did not point to this fork variant's MikroTik Lite documentation.

### Adopted Changes

- Added a MikroTik Lite deployment/readiness README under `docker/mikrotik-lite/`.
- Added a root README note pointing to the MikroTik Lite multi-interface build documentation.
- Removed the dangling `Template Entry` placeholder from active CODELOAD history.

### Validation / Tests Performed

- Searched project documentation for stale `Pending`, `TODO`, `TBD`, timestamp-tag, and build-tag references.
- Confirmed build `b1.0` remains documented as broken and build `b1.1` remains documented as the corrected build.

### Remaining Risks / Follow-ups

- Confirm whether `rvncore/speedtest-tracker:latest` has been repointed to corrected build `b1.1` on Docker Hub.
- Confirm corrected `b1.1` runtime on RouterOS after repull/redeploy.
- Decide later whether to add a broader `docs/` directory if the variant grows beyond MikroTik Lite operations.

---

## 2026-05-19 16:16:34 +08:00 - Corrected ARM64 Build Executed

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-B1-1-BUILD-EXECUTED`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Built the corrected ARM64 MikroTik Lite image after confirming the unsupported scheduler-level timeout had been removed from source.

### Findings

- `routes/console.php` no longer contains scheduler-level `->timeout(...)`.
- Timeout protection remains in `app/Jobs/Ookla/RunSpeedtestJob.php` via:
  - `public $timeout = 300;`
  - `$process->setTimeout(300);`
- Corrected local image ID:
  - short: `40644087e312`
  - full: `sha256:40644087e312a3a41479cd7894ba4e9bbbf11db0bec0ca7cb40984043f4a67ab`
- Corrected local tags now point to the same image:
  - `rvncore/speedtest-tracker:0.1.1-b1.1-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
  - `rvncore/speedtest-tracker:multi-isp-exp-arm64`
  - `rvncore/speedtest-tracker:latest`
- Local Docker image listing reported `640MB`; Docker inspect reported uncompressed image size `130453221` bytes.

### Validation / Tests Performed

- `rg -- "->timeout\\(|setTimeout\\(|public \\$timeout|speedtest-lite:run-isp-profile" routes/console.php app/Jobs/Ookla/RunSpeedtestJob.php`
- `php -l routes/console.php`
- `php -l app/Jobs/Ookla/RunSpeedtestJob.php`
- `docker buildx build --no-cache --platform linux/arm64 --provenance=false --load`
- `docker run --rm --platform linux/arm64 --entrypoint php ... -m`
- `docker run --rm --platform linux/arm64 -e SPEEDTEST_LITE_ISP_PROFILES= ... php artisan list`

### Validation Result

- Docker build completed successfully.
- PHP module smoke test passed and includes required modules: `intl`, `mbstring`, `pcntl`, `pdo_sqlite`, `sockets`, and `zip`.
- Laravel boot smoke test passed.
- `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` are present.
- No `Laravel\Boost\BoostServiceProvider` boot failure observed.
- No scheduler `Event::timeout` boot failure observed.

### Remaining Risks / Follow-ups

- Push corrected local tags to Docker Hub.
- Verify Docker Hub manifests point to image/config for corrected `b1.1`, not broken `b1.0`.
- Repull/redeploy on RouterOS and confirm runtime boot plus web UI.

---

## 2026-05-19 18:23:54 +08:00 - Build b1.2 Vite Manifest Fix

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-B1-2-VITE-MANIFEST-FIX`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Investigated and fixed the RouterOS web UI `500 Internal Server Error` caused by missing Vite production assets in the MikroTik Lite image.

### Findings

- `wget http://127.0.0.1` inside the RouterOS container returned `HTTP/1.1 500 Internal Server Error`.
- RouterOS container logs showed `Illuminate\Foundation\ViteManifestNotFoundException`.
- Laravel was looking for `/var/www/html/public/build/manifest.json`.
- The MikroTik Lite Dockerfile installed Composer/PHP dependencies but did not run the frontend Vite production build.
- Build `0.1.1-b1.1` should be treated as incomplete: it fixed Laravel scheduler boot, but the web UI is broken.

### Adopted Changes

- Updated `docker/mikrotik-lite/Dockerfile.mikrotik-lite` builder stage:
  - added `nodejs` and `npm`
  - added `npm ci --no-audit --no-fund`
  - added `npm run build`
  - removed `node_modules` after asset build
- Built local corrected image as `0.1.1-b1.2-mikrotik-lite-multi-isp-arm64`.

### Image Tags / Build State

Current corrected local image ID:

- short: `1f437cc8552f`
- full: `sha256:1f437cc8552f59c7bc64ae338855b791562bfd4b606d3c060d0d5a89d39895ce`

Corrected local tags now point to `b1.2`:

- `rvncore/speedtest-tracker:0.1.1-b1.2-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:multi-isp-exp-arm64`
- `rvncore/speedtest-tracker:latest`

Known bad or incomplete builds:

- `0.1.1-b1.0`: broken by unsupported scheduler `Event::timeout`.
- `0.1.1-b1.1`: scheduler boot fixed, but web UI broken because `public/build/manifest.json` is missing.

### Validation / Tests Performed

- ARM64 Docker build completed with `--platform linux/arm64 --provenance=false --load`.
- Build output confirmed Vite generated:
  - `public/build/manifest.json`
  - `public/build/assets/app-*.css`
  - `public/build/assets/theme-*.css`
- Local container smoke test confirmed `/var/www/html/public/build/manifest.json` exists.
- Local container smoke test confirmed `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` remain available.

### Remaining Risks / Follow-ups

- Push `0.1.1-b1.2` and moving tags to Docker Hub.
- Repull/redeploy on RouterOS.
- Recheck `http://192.168.99.249` and confirm the web UI no longer returns `500`.
- Let scheduled `isp1` and `isp2` runs complete on `b1.2`.

---

## 2026-05-19 18:45:44 +08:00 - Profile-Aware UI Deferred

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-PROFILE-AWARE-UI-PENDING`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Recorded pending dashboard/results UI work after confirming the current web UI loads but remains profile-unaware.

### Findings

- Build `0.1.1-b1.2` resolves the web UI `500` caused by missing Vite production assets.
- The database already stores profile metadata through:
  - `results.isp_profile_key`
  - `results.isp_profile_name`
  - `results.source_ip`
- The profile runner tags completed rows after each ISP profile run.
- Existing dashboard widgets query completed results together and do not group or filter by ISP profile.
- Existing chart controls expose time range filters only, not profile selection.

### Current Version Scope

Keep `0.1.1-b1.2` focused on runtime correctness and polish:

- Laravel boots.
- Web UI loads.
- Vite manifest exists.
- Profile source IP validation passes.
- Scheduled profile runs execute.
- Results are stored and tagged in SQLite.

### Pending / TODO

- Add profile metadata columns to the Results table:
  - ISP/profile key
  - ISP/profile name
  - source IP
- Add Results table filters for ISP/profile and source IP.
- Add dashboard profile selector:
  - `All`
  - one option per enabled profile, such as `CNVG` and `PLDT`
- Update dashboard chart queries to filter by selected profile.
- Later improvement: show per-profile chart series on the same chart for comparison.
- Later improvement: add profile-aware summary cards for latest download/upload/ping per profile.

### Versioning Note

- Treat profile-aware dashboard/results UI as a feature update, not a patch-only polish change.
- Suggested next feature line:
  - `0.2.0-b1.0-mikrotik-lite-multi-isp-arm64`
- Keep `0.1.1-b1.2` as the current corrected runtime/web UI build.

### Remaining Risks / Follow-ups

- Confirm scheduled `isp1` and `isp2` runs complete on `0.1.1-b1.2`.
- Confirm browser access to `http://192.168.99.249` remains stable.
- Do not start the dashboard/profile UI feature until the current build is fully validated.

---

## 2026-05-19 18:50:05 +08:00 - Admin Credential and Web Stability Pending

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-ADMIN-WEB-STABILITY-PENDING`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Recorded two polish/follow-up items discovered during RouterOS web validation: default admin credential rotation and intermittent dashboard loading instability.

### Findings

- The default admin account is created during the initial users table migration from:
  - `ADMIN_EMAIL`, default `admin@example.com`
  - `ADMIN_PASSWORD`, default `password`
- Because the SQLite database already exists, changing env values now will not automatically update the existing admin user.
- Password reset path:
  - `php artisan app:user-reset-password`
- Web UI now returns `HTTP/1.1 200 OK` after the `b1.2` Vite manifest fix.
- Browser loading is not yet consistently stable after several dashboard loads.
- RouterOS logs show nginx warnings similar to:
  - `an upstream response is buffered to a temporary file /var/lib/nginx/tmp/fastcgi/... while reading upstream`
- No Laravel exception was shown for this new symptom.
- Current low-resource runtime settings include:
  - nginx `worker_processes 1`
  - nginx `worker_connections 256`
  - PHP-FPM `pm.max_children = 1`
  - PHP memory limit `64M`
  - RouterOS container `memory-high=160M`

### Pending / TODO

- Rotate/update the admin credentials before treating the deployment as stable.
- Decide whether to add `ADMIN_EMAIL` and `ADMIN_PASSWORD` examples to the MikroTik Lite README/env template.
- Investigate intermittent web loading/timeouts after repeated dashboard loads.
- Possible areas to review:
  - nginx FastCGI buffer sizes and temp-file buffering behavior
  - PHP-FPM child count and memory limit
  - dashboard query/result volume for 24h data
  - whether public dashboard should be profile/time-window optimized for MikroTik Lite

### Current Recommendation

- Do not change nginx/PHP-FPM memory and buffering settings until scheduled profile validation on `b1.2` is complete.
- Treat this as a web polish/stability item for the current build line unless it blocks basic admin/dashboard access.

### Additional Observation - 2026-05-19 18:52:00 +08:00

- Admin login succeeds.
- Dashboard page renders and shows statistics/last result cards.
- Side navigation pages such as Results, Users, API Tokens, Data Integration, Notifications, and Thresholds can take a long time to load.
- This suggests the remaining issue is not a hard Laravel boot failure. It is more likely low-resource admin UI latency, PHP-FPM request queuing, database/query cost, or nginx FastCGI buffering under RouterOS constraints.
- Keep this grouped with the web stability pending item until measured.

### Additional Observation - 2026-05-19 18:56:06 +08:00

- RouterOS showed container `memory-current=159.7MiB` while `memory-high=160MiB`.
- The container was running:
  - `php-fpm` master and one `php-fpm` worker
  - `php artisan schedule:run --no-interaction`
  - `php artisan speedtest-lite:run-isp-profile isp1`
  - `speedtest --ip=192.168.99.250`
  - a second `php artisan schedule:run --no-interaction`
- This suggests admin page latency is likely contention between web requests, scheduler, and active speedtest execution under a tight memory cap.
- The root directory name remained `root-0.1.1-b1.1`, but RouterOS image ID matched the pushed `b1.2` config digest prefix `7964284e...`; root-dir naming is cosmetic but should be cleaned up for clarity later.

### Pending / TODO Additions

- Prevent overlapping `schedule:run` invocations from cron if a profile speedtest takes longer than one minute.
- Re-evaluate `memory-high=160M`; consider testing `192M` after confirming RouterOS free memory behavior.
- Consider a Lite web/runtime split in behavior:
  - keep PHP-FPM at one worker if memory must stay minimal
  - avoid admin-heavy navigation during active speedtest runs
  - or increase PHP-FPM workers only if memory budget allows
- Rename/recreate RouterOS root directory to match the actual build, for example `root-0.1.1-b1.2`, while keeping the same `/config` mount for historical results.

### Grouped Follow-up Buckets - 2026-05-19 18:57:25 +08:00

#### Runtime Deployment Hygiene

- Recreate or rename the RouterOS container root directory so it matches the actual deployed build:
  - current misleading name: `root-0.1.1-b1.1`
  - desired name: `root-0.1.1-b1.2`
- Keep the same `/config` mount through `mountlists=mount-app-speedtest` so historical SQLite results are preserved.
- After the final interface decision, switch from temporary validation interface `veth-speedtest` to intended convention name `veth-app-speed`.
- Update `SPEEDTEST_LITE_BIND_INTERFACE` to match the final in-container interface name.

#### Scheduler and Resource Stability

- Prevent overlapping `schedule:run` invocations when a speedtest takes longer than one minute.
- Decide whether the internal cron should use a lock/mutex wrapper or whether profile scheduling should avoid minute-level overlap more aggressively.
- Re-test memory behavior after overlap mitigation.
- Consider testing `memory-high=192M` only if RouterOS remains stable and available memory is acceptable.

#### Web/Admin Performance

- Re-test admin side navigation when no speedtest is running.
- Compare page load behavior during an active Ookla run versus idle scheduler state.
- Review nginx FastCGI buffering only after confirming whether contention is the primary cause.
- Review PHP-FPM `pm.max_children` only after deciding the memory budget.
- Consider limiting expensive admin/dashboard queries for MikroTik Lite mode.

#### Security and Admin Access

- Rotate the default admin password.
- Consider documenting `ADMIN_EMAIL` and `ADMIN_PASSWORD` setup before first database creation.
- If the DB already exists, use `php artisan app:user-reset-password`.

#### Future Feature UI

- Keep profile-aware dashboard/results UI for a feature version, likely `0.2.0-b1.0`.
- Do not mix profile-aware UI work into the current `0.1.1-b1.2` runtime stabilization unless needed for validation.

---

## 2026-05-19 20:57:17 +08:00 - Build b1.3 Scheduler Lock

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-B1-3-SCHEDULER-LOCK`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Added scheduler overlap protection to reduce RouterOS memory/process contention when an Ookla speedtest runs longer than one cron interval.

### Findings

- RouterOS process inspection showed more than one `php artisan schedule:run --no-interaction` process while a profile speedtest was active.
- The container was near `memory-high=160M`, so overlapping scheduler passes compete with web/admin requests and the active Ookla CLI process.

### Adopted Changes

- Updated `docker/mikrotik-lite/entrypoint.sh` to generate `/tmp/run-scheduler-once.sh`.
- The wrapper uses lock directory `/tmp/speedtest-lite-schedule-run.lock`.
- If another scheduler pass is active, the next cron tick logs and exits:
  - `NOTICE: previous schedule:run is still active; skipping this tick.`
- Repointed local moving tags to build `0.1.1-b1.3`.

### Image Tags / Build State

Current corrected local image ID:

- short: `84560e33acd5`
- full: `sha256:84560e33acd5244920c39fb1851e53f243c0ad132923f44a730b8c58492edfd1`

Corrected local tags now point to `b1.3`:

- `rvncore/speedtest-tracker:0.1.1-b1.3-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:multi-isp-exp-arm64`
- `rvncore/speedtest-tracker:latest`

### Validation / Tests Performed

- `php -l app/Jobs/Ookla/RunSpeedtestJob.php`
- `php -l routes/console.php`
- ARM64 Docker build completed with `--platform linux/arm64 --provenance=false --load`.
- Local container smoke test confirmed scheduler wrapper text exists in `/usr/local/bin/entrypoint.sh`.
- Local container smoke test confirmed `/var/www/html/public/build/manifest.json` exists.
- Local container smoke test confirmed `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` remain available.

### Remaining Risks / Follow-ups

- Push `0.1.1-b1.3` and moving tags to Docker Hub.
- Repull/redeploy on RouterOS using a clear root dir such as `root-0.1.1-b1.3`.
- Confirm only one `schedule:run` remains active during long speedtests.
- Re-test admin navigation while idle and during active speedtest.

### RouterOS Validation Update - 2026-05-19 22:50:03 +08:00

- `b1.3` deployed on RouterOS and basic runtime checks passed:
  - Vite manifest present.
  - scheduler lock wrapper present.
  - source IP validation passed for `isp1` and `isp2`.
  - local web request returned `HTTP/1.1 200 OK`.
- Idle process list showed no stray scheduler or speedtest processes.
- During scheduled `isp1` run, process list showed exactly one scheduler chain:
  - `/tmp/run-scheduler-once.sh`
  - `php artisan schedule:run --no-interaction`
  - `php artisan speedtest-lite:run-isp-profile isp1`
- No duplicate `schedule:run` process was observed during this sample, so the overlap guard appears to be working.
- However, the web/admin page still stalled while the scheduled profile run was active.
- Interpretation: `b1.3` addresses scheduler overlap, but it does not fully prevent web/admin contention during active speedtest execution under the current memory/PHP-FPM/runtime limits.
- Next likely stabilization decision:
  - test a higher RouterOS `memory-high` such as `192M`, or
  - accept that admin navigation should be avoided during active speedtests on the single-container Lite runtime, or
  - plan a later web/runner split if interactive admin responsiveness during tests is required.

### RouterOS Validation Update - 2026-05-19 22:50:50 +08:00

- During the active `isp1` scheduled run, `top` showed CPU mostly idle, but both key PHP processes in `D` state:
  - `php-fpm: pool www`
  - `/usr/local/bin/php artisan speedtest-lite:run-isp-profile isp1`
- This points away from CPU saturation and toward I/O wait, storage wait, SQLite/database locking, FastCGI buffering, or process wait behavior.
- Because PHP-FPM currently has only one worker, any blocked web request can stall admin/dashboard navigation.
- Next diagnostic direction:
  - confirm whether the UI unblocks immediately after the scheduled run finishes
  - inspect SQLite result/batch state after the run
  - test whether raising `memory-high` alone improves the issue
  - consider a second PHP-FPM worker only if memory budget allows

---

## 2026-05-19 23:45:00 +08:00 - Build b1.4 Runtime Stabilization

### Reference

`REF-CODELOAD-20260519-MIKROTIK-LITE-B1-4-RUNTIME-STABILITY`

### Project / Path

`Container/speedtest-tracker`

### Task / Change Summary

Prepared the next MikroTik Lite stabilization image after RouterOS testing showed b1.3 could still leave stale scheduler locks and the admin UI could stall when the only PHP-FPM worker was blocked during active tests.

### Findings

- Build `b1.3` correctly prevented duplicate live `schedule:run` chains, but a killed/interrupted scheduler wrapper could leave `/tmp/speedtest-lite-schedule-run.lock` behind.
- RouterOS logs showed repeated:
  - `NOTICE: previous schedule:run is still active; skipping this tick.`
- Runtime process samples showed a single PHP-FPM worker could block admin/dashboard requests while speedtest/scheduler activity was in progress.
- Increasing RouterOS `memory-high` to `192M` improved observed admin responsiveness, making two PHP-FPM workers plausible for the next test build.

### Adopted Changes

- Updated `docker/mikrotik-lite/entrypoint.sh` scheduler wrapper to track the lock holder PID.
- Added stale lock handling for:
  - dead PID
  - zombie PID
  - missing/unreadable PID file
- Updated `docker/mikrotik-lite/php-fpm.conf`:
  - `pm.max_children = 2`
- Updated documentation to mark `0.1.1-b1.4` as the current corrected local build.

### Image Tags / Build State

Current corrected local image:

- short: `dd881fa2ce28`
- full: `sha256:dd881fa2ce28e3f48c1426c2a055009fa0d97dc98360a7267e0a661003805d80`
- size: `131MB`

Corrected local tags now point to `b1.4`:

- `rvncore/speedtest-tracker:0.1.1-b1.4-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:0.1.1-mikrotik-lite-multi-isp-arm64`
- `rvncore/speedtest-tracker:multi-isp-exp-arm64`
- `rvncore/speedtest-tracker:latest`

### Validation / Tests Performed

- ARM64 Docker build completed.
- Local image listing confirmed all moving tags point to image ID `dd881fa2ce28`.
- Local one-shot container smoke test confirmed:
  - `pm.max_children = 2`
  - stale-lock recovery strings exist in `/usr/local/bin/entrypoint.sh`
  - `/var/www/html/public/build/manifest.json` exists
  - `speedtest-lite:run-isp-profile` and `speedtest-lite:validate-isp-profiles` remain available

### Remaining Risks / Follow-ups

- Push `0.1.1-b1.4` and moving tags to Docker Hub.
- Repull/redeploy on RouterOS with a clear root dir such as `root-0.1.1-b1.4`, while preserving the existing `/config` mount and SQLite history.
- Confirm RouterOS `schedule:list` reflects the preferred 20-minute stagger if envlist values were changed:
  - `isp1`: `0,20,40 * * * *`
  - `isp2`: `10,30,50 * * * *`
- Validate stale-lock recovery after deployment by watching scheduler logs across at least one long scheduled test.
- Keep profile-aware dashboard/results UI as future feature work, likely `0.2.0-b1.0`.

### Deferred Next-Build Cleanup - 2026-05-20 +08:00

- RouterOS logs showed PHP startup warning:
  - `<b>Warning</b>: Zend OPcache can't be temporary enabled (it may be only disabled till the end of request) in <b>Unknown</b> on line <b>0</b><br />`
- Likely cause is `php_admin_value[opcache.enable_cli] = 1` in `docker/mikrotik-lite/php-fpm.conf`.
- `opcache.enable_cli` is CLI-scoped and should not be set through the PHP-FPM pool config.
- Hold this for the next build instead of rebuilding immediately while current `b1.4` RouterOS validation is still running.
- Proposed next-build fix:
  - keep `php_admin_value[opcache.enable] = 1`
  - remove `php_admin_value[opcache.enable_cli] = 1`
- Suggested next patch build:
  - `0.1.1-b1.5-mikrotik-lite-multi-isp-arm64`
- Also include the lighter 20-minute profile schedule defaults in the next patch build:
  - `SPEEDTEST_LITE_ISP1_CRON="0,20,40 * * * *"`
  - `SPEEDTEST_LITE_ISP2_CRON="10,30,50 * * * *"`
- RouterOS b1.4 testing surfaced dashboard stalls when the upstream GitHub latest-version check timed out:
  - `https://api.github.com/repos/alexjustesen/speedtest-tracker/releases/latest`
- Include a MikroTik Lite version-check guard in b1.5:
  - `SPEEDTEST_LITE_DISABLE_GITHUB_VERSION_CHECK=true`
  - `SPEEDTEST_LITE_GITHUB_REPOSITORY=rvncore/speedtest-tracker`
- Default MikroTik Lite builds should disable the GitHub version check to avoid blocking dashboard rendering on constrained or intermittently connected RouterOS networks.
- If re-enabled later, the repository target should be configurable and point to the fork/build source rather than the upstream app by default for Lite images.

### Timezone Configuration Note - 2026-05-20 +08:00

- RouterOS containers do not automatically inherit the MikroTik system timezone.
- The MikroTik Lite image already includes `tzdata`, so timezone can be controlled through env values without adding packages.
- Initial timezone defaults were added to `docker/mikrotik-lite/.env.mikrotik-lite` and documented them in `docker/mikrotik-lite/README.md`:
  - `TZ=Asia/Manila`
  - `APP_TIMEZONE=Asia/Manila`
  - `DISPLAY_TIMEZONE=Asia/Manila`
- For an already-running RouterOS container, add or set the same keys in `env-app-speedtest`, then restart the container.

### Timezone Correction - 2026-05-20 +08:00

- RouterOS UI showed existing result rows still displayed with the wrong local time after setting both `APP_TIMEZONE` and `DISPLAY_TIMEZONE` to `Asia/Manila`.
- Root cause: existing SQLite `created_at` values are timezone-less strings that were written under UTC app-time assumptions. Changing `APP_TIMEZONE` to Manila can cause Laravel/Eloquent to interpret old UTC strings as already-local Manila time instead of converting them.
- Corrected the recommended runtime values:
  - `TZ=Asia/Manila`
  - `APP_TIMEZONE=UTC`
  - `DISPLAY_TIMEZONE=Asia/Manila`
- Updated `docker/mikrotik-lite/.env.mikrotik-lite` and `docker/mikrotik-lite/README.md` accordingly.

### Timezone Validation Follow-up - 2026-05-20 +08:00

- RouterOS validation confirmed dashboard/chart display labels corrected after changing persisted `/config/.env` to:
  - `TZ=Asia/Manila`
  - `APP_TIMEZONE=UTC`
  - `DISPLAY_TIMEZONE=Asia/Manila`
- Added explicit RouterOS `envlist` examples and an in-container Laravel config verification command to `docker/mikrotik-lite/README.md`.
- Keep this as operator guidance for other users: `APP_TIMEZONE` is storage/app interpretation and should remain UTC; `DISPLAY_TIMEZONE` is the user-facing local timezone.

### RouterOS Naming Cleanup Plan - 2026-05-24 +08:00

- Proceed with cleanup of temporary RouterOS naming after b1.4 validation:
  - RouterOS interface: `veth-speedtest` -> `veth-app-speed`
  - container root dir: old/misleading root path -> `root-0.1.1-b1.4`
- Preserve existing `mount-app-speedtest` and `/config` mount so SQLite history and persistent app state remain intact.
- Keep container name as `app-speedtest-v011` for now to avoid extra operational churn.
- Update `SPEEDTEST_LITE_BIND_INTERFACE` to `veth-app-speed` after the interface rename.
- Revalidate after recreate:
  - `ip a`
  - `php artisan speedtest-lite:validate-isp-profiles`
  - `php artisan schedule:list`
  - local HTTP check

### Historical SQLite Cleanup - 2026-05-24 +08:00

- Cleaned old untagged abandoned `waiting` rows from the RouterOS SQLite database after b1.4 stabilization.
- Cleanup target:
  - `status='waiting'`
  - empty/null `isp_profile_key`
  - empty/null `isp_profile_name`
  - empty/null `source_ip`
- Pre-cleanup count:
  - `136` rows
- Latest matching row before cleanup:
  - id `928`
  - `2026-05-20 01:00:40`
- Post-cleanup result summary:
  - `isp1|completed|870|2026-05-14 18:32:29|2026-05-24 13:20:38`
  - `isp1|failed|6|2026-05-16 16:00:03|2026-05-19 14:34:39`
  - `isp2|completed|846|2026-05-14 18:33:22|2026-05-24 13:32:05`
- Tagged completed and failed profile rows were preserved.

### Documentation and Release Polish - 2026-05-24 +08:00

- Updated operator-facing MikroTik Lite README with:
  - current b1.4 build state
  - planned b1.5 patch items
  - validated RouterOS naming convention
  - 20-minute profile cadence
  - corrected timezone envlist examples using `env-app-speed`
- Updated root `CHANGELOG.md` and `docker/mikrotik-lite/CHANGELOG.md` with:
  - b1.4 RouterOS validation
  - naming cleanup
  - timezone validation
  - SQLite cleanup
  - planned b1.5 OPcache and GitHub version-check cleanup
- Historical older changelog entries were left intact even where they mention now-completed follow-ups, because they describe the state at that time.

### Admin Credential Rotation Documentation - 2026-05-24 +08:00

- Added MikroTik Lite README guidance for changing the default admin password after database initialization.
- Recommended in-container command:
  - `php artisan app:user-reset-password`
- Password values should not be stored in repo docs, CODELOAD history, RouterOS comments, or screenshots.

### b1.4 Observation Closeout - 2026-05-24 +08:00

- Closed the active b1.4 observation item after extended RouterOS runtime validation remained acceptable.
- Observed runtime nuances have already been converted into b1.5 patch scope:
  - OPcache CLI warning cleanup.
  - GitHub latest-version timeout guard.
  - 20-minute schedule defaults.
  - timezone defaults.
- No current blocker remains against using b1.4 while preparing the b1.5 patch build.

### b1.4 Final Health Snapshot - 2026-05-24 +08:00

- `speedtest-lite:validate-isp-profiles` passed:
  - `isp1` CNVG `192.168.99.250` visible, cron `0,20,40 * * * *`
  - `isp2` PLDT `192.168.99.251` visible, cron `10,30,50 * * * *`
- `schedule:list` confirmed:
  - upstream maintenance schedules still present
  - `sqlite-vacuum` still present
  - profile schedules on 20-minute stagger
- SQLite result summary:
  - `isp1|completed|872|2026-05-24 14:01:14`
  - `isp1|failed|6|2026-05-19 14:34:39`
  - `isp2|completed|847|2026-05-24 13:50:03`
- No untagged `waiting` bucket remained.
- Process list showed normal idle runtime:
  - nginx master/worker
  - crond
  - php-fpm master
  - two php-fpm pool workers
  - shell only
- Local HTTP check returned `HTTP/1.1 200 OK`.
- b1.4 is considered finalized for current runtime use.
