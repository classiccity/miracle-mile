# Miracle Mile Shops — Build Log
Version: 1.13

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
- [x] 1.7 Rotate WordPress salts — done 2026-09-17 19:14 UTC via `wp config shuffle-salts`, backup of
  wp-config.php kept in the SSH home dir. All cookies invalidated. Wordfence scan (running with all
  checks enabled) flagged only four modified plugin files, all explained as Sucuri edits / build metadata.
- [x] 1.8 Invalidate all user passwords — done 2026-09-17 19:33 UTC. Random passwords set for users 2,
  11, 13, 15 (user 7 already deleted by Chris); users reset their own via lost-password. Not emailed by us.
- [x] 1.9 Replace "Create And Assign Categories For Pages" plugin with child-theme code — done
  2026-09-17 19:52 UTC. Two `register_taxonomy_for_object_type` calls appended to
  `themes/bb-theme-child/functions.php`, uploaded, plugin deactivated and deleted, tests pass
  (taxonomies on page, categorized pages keep their terms, metabox registered). Archive-mixing
  behavior intentionally not reproduced.
- [x] 1.10 Remove WP File Manager leftovers — done 2026-09-17 19:46 UTC. Uploads folder, `fm_key`
  option and empty `wp_wpfm_backup` table removed.
- [x] 1.11 Wordfence hardening — done 2026-09-17 20:02 UTC after the full scan (all checks on) came back
  clean apart from explained items. WAF switched from learning mode to Enabled and Protecting (needed
  `WFWAF_ALWAYS_ALLOW_FILE_WRITING` for the CLI write), invalid-username lockout on.
- [x] 1.12 Replace Custom Post Type UI with `inc/post-types.php`; split functions.php into `inc/`
  includes — done 2026-09-17 20:30 UTC. Args captured from the plugin at runtime; before/after
  registration diff is zero; every CPT single, archive and REST endpoint 200 after deletion.
  Child theme layout is now: functions.php (loader) + inc/{theme-options, post-types,
  page-taxonomies, acf-readonly-fields, gravity-forms-coupon-counter}.php.
- [x] 1.13 Plugin surface reduction (Chris, via wp-admin) — done 2026-09-18 00:50 UTC. Deleted ACF free,
  Akismet, Genesis Blocks, Panorama, both Instagram plugins, WordPress Importer, WP All Import,
  WP All Export, WPE Site Migration; updated Google Language Translator to 7.0.1. Decisions backed
  by the layout usage audit (`incident-2026-09-17/evidence/plugin-usage-audit.json`). 19 active
  plugins remain; inventory in INCIDENT-REPORT §4.4b.
- [ ] 1.14 Remaining cleanup — delete Genesis Block Theme and FlowPaper Lite; remove the two dead
  `[panorama]` shortcodes from the "Views" page (182); 2FA check for walbert; WPE portal audit.
  SFTP/SSH key review and log request, Wordfence WAF out of learning mode + checksum scans on,
  replace Sucuri-touched premium plugin files, remove inactive plugins/themes, client PII
  notification decision. Details in `INCIDENT-REPORT.md` §5.
