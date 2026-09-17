# Miracle Mile Shops — Build Log
Version: 1.6

Repo for the miraclemileshopslv.com WordPress site (WP Engine install `miraclemilesh`).
`themes/` holds the Beaver Builder parent and child themes as downloaded from the live site;
`plugins/` is reserved for custom plugin code; `incident-2026-09-17/` is the security incident
record. Full incident narrative, timeline, and change log: `incident-2026-09-17/INCIDENT-REPORT.md`.

## Milestone 1 — 2026-09-17 malware incident: diagnose, neutralize, document (opened 2026-09-17)
Site went down with a PHP parse error; cause was an attacker-injected "Asset Cache Proxy" malware
(ClickFix visitor kit + admin backdoor) in the parent theme and, separately, in a must-use plugin.
- [x] 1.1 Diagnose the outage from the WPE error log — done 2026-09-17. Parse error at
  `bb-theme/functions.php:102`, a stray `<?php` inside pasted malware, not an update or a typo.
- [x] 1.2 Audit the downloaded themes for backdoors — done 2026-09-17. Only the functions.php
  block; child theme clean. Injected lines 102–174 removed from the local copy (`themes/bb-theme/functions.php`).
- [x] 1.3 Server-side audit over SSH (read-only) — done 2026-09-17. Found the live mu-plugin copy
  `wp-content/mu-plugins/asset-cache.php` (+ .bak), the backdoor login key, the July attacker admin
  residue (user 14 `adminuser`), attacker `robots.txt`, Aug 15 Sucuri cleanup stubs. Four subagent
  audits (plugins, database, uploads, timeline) found no further active payload.
- [x] 1.4 Neutralize — done 2026-09-17 18:48 UTC. Removed both mu-plugin files and the zero-byte
  stub, deleted malware transients, purged WPE caches. Verified externally: script tag gone,
  `/asset-cache/fjs` 404, marker grep empty.
- [x] 1.5 Account and notification changes — done 2026-09-17 18:55–19:00 UTC. `admin_email`,
  WSAL digest and Instagram Feed notices moved from the former vendor (noel@techwoodconsulting.com)
  to access@classiccityconsulting.com; recovery-mode key deleted; orphaned attacker usermeta
  purged; attacker robots.txt, wp_index_ad.php stub and .tmb removed; sessions destroyed for users
  2, 7, 11, 13.
- [x] 1.6 Evidence and report — done 2026-09-17. `incident-2026-09-17/` holds the malware samples
  (as .txt), payload, logs of every command run, before/after snapshots, and the full report.
- [ ] 1.7 Hardening follow-ups (owner: Chris) — password resets + 2FA, salt rotation, WPE portal
  SFTP/SSH key review and log request, Wordfence WAF out of learning mode + checksum scans on,
  replace Sucuri-touched premium plugin files, remove inactive plugins/themes, client PII
  notification decision. Details in `INCIDENT-REPORT.md` §5.
