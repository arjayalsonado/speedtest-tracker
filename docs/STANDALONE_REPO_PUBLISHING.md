# Standalone Repository Publishing Checklist

This checklist is for turning the MikroTik Lite / ARM64 multi-interface variant into a standalone public repository.

## Goals

- Publish a clean project history without local AI worklogs.
- Preserve upstream attribution to Speedtest Tracker.
- Keep a clear process for syncing upstream security and bug-fix releases.
- Keep Docker Hub release tags traceable to source commits.

## Before Publishing

- Create the standalone repository as an empty repository.
- Import from a clean branch or clean-history export.
- Exclude local-only notes and generated workspace files:
  - `.ai/`
  - `.github/agents/`
  - local editor and agent state
- Keep public release notes in:
  - `CHANGELOG.md`
  - `docker/mikrotik-lite/CHANGELOG.md`
  - `docker/mikrotik-lite/README.md`
  - `docker/mikrotik-lite/DOCKERHUB_OVERVIEW.md`

## Documentation Cleanup

- Confirm README and Docker Hub overview describe the project as an independently maintained variant.
- Keep upstream attribution and link to:
  - `https://github.com/alexjustesen/speedtest-tracker`
- Keep the AI-assisted development disclosure.
- Avoid publishing operational secrets, API keys, passwords, session cookies, or private screenshots.
- Use example IPs and names that are clearly adjustable.

## Upstream Sync Workflow

- Keep an `upstream` remote pointed at the original project.
- Commit and push current validated work before merging upstream.
- Merge upstream into the local base branch first.
- Merge the updated base branch into the MikroTik Lite branch.
- Validate before producing a new image tag.

## Docker Image Workflow

- Commit source before building images.
- Build immutable version/SHA tags first.
- RouterOS-test the immutable tag.
- Move convenience tags only after validation:
  - `latest`
  - `multi-isp-exp-arm64`
  - version-family tags such as `0.1.1-mikrotik-lite-multi-isp-arm64`
- Protect build-specific tags with Docker Hub tag immutability.

Current Docker Hub immutable-tag pattern:

```regex
.*-b.*-mikrotik-lite-multi-isp-arm64
```

## Validation Checklist

- RouterOS container starts cleanly.
- `speedtest-lite:validate-isp-profiles` passes.
- `php artisan schedule:list` shows expected profile schedules.
- Web UI returns HTTP `200 OK`.
- Automatic profile runs complete.
- Untagged `waiting` rows do not grow after promotion.
- Docker Scout scan has no fixable critical findings.
