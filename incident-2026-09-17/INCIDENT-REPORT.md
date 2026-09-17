# Miracle Mile Shops (miraclemileshopslv.com) — Security Incident Report and Change Log

**Incident date:** 2026-09-17
**Install:** WP Engine `miraclemilesh` (https://www.miraclemileshopslv.com)
**Prepared by:** Classic City Consulting (Chris LaFay) with Claude Code, 2026-09-17
**Status at end of day:** malware removed and verified gone; site up; entry point not yet proven (see §6)

All times below are given as **UTC (ET)**. WP Engine's servers and logs run on UTC; ET is UTC-4.

---

## 1. Summary

The public site went down at 17:04 UTC (1:04 PM ET) on 2026-09-17 with a PHP parse error in the Beaver Builder parent theme's `functions.php`. The error was caused by an attacker pasting malware into that file with a stray `<?php` tag. The outage was the only reason the infection was noticed.

Investigation showed a second, working copy of the same malware installed as a **must-use plugin** (`wp-content/mu-plugins/asset-cache.php`) two hours earlier. Must-use plugins load on every request and cannot be deactivated from the WordPress admin. This copy:

- injected `<script src="/asset-cache/fjs">` into the `<head>` of every page;
- proxied `/asset-cache/*` requests through the site's own domain to an attacker gateway, so the malicious script appeared to come from miraclemileshopslv.com;
- hid itself from the WordPress plugin list;
- contained a **backdoor**: any URL with `?k=<secret>` logged the visitor in as the first administrator, or created a new administrator (`wp_upd`) if none existed.

The script served to visitors is a **"ClickFix"-style kit**: it fingerprints the visitor, can display a fullscreen fake verification overlay, copies an attacker-supplied command to the visitor's clipboard (the "paste this to verify you are human" scam that installs malware on Windows PCs), harvests browser localStorage, sessionStorage and cookies, and references a malware download at `nutmann.club/p/wm.zip`.

**Exposure window for visitors:** 15:00 UTC (11:00 AM ET) to 18:50 UTC (2:50 PM ET) on 2026-09-17, about 3 hours 50 minutes. The site was actually down (parse error) from 17:04 to 17:52 UTC, so the script was served for roughly 2 hours 50 minutes of that window.

This was the **second** compromise of this site. Forensic residue shows an attacker administrator account (`adminuser`) created 2026-07-29, a file-manager plugin installed the same day, an ad-injection payload and SEO-cloaking `robots.txt`, and a Sucuri cleanup on 2026-08-15 that blanked files rather than fully removing the intrusion. The September attacker used the same technique (mu-plugin plus theme `functions.php`).

---

## 2. What was found

### 2.1 Active malware (removed 18:48 UTC)

| Path | Size | Written (UTC) | md5 |
|---|---|---|---|
| `wp-content/mu-plugins/asset-cache.php` | 9,483 B | 2026-09-17 15:00:15 | `80fe1db1ea46b64a4ae1a55c53923c22` |
| `wp-content/mu-plugins/asset-cache.php.bak` | 9,483 B | 2026-09-17 15:00:11 | `80fe1db1ea46b64a4ae1a55c53923c22` (identical) |
| `wp-content/themes/bb-theme/functions.php` (lines 102–174) | 7,023 B infected | ~2026-09-17 17:04 | restored to stock 17:52 UTC |

Fake header: "WP Asset Cache 1.0.3 by WP Performance". Russian-language inline comments. Command-and-control:

- `https://vilialobos.lol/ads.php` (gateway address, base64)
- `http://194.59.30.132:8080/api/wpcfg?key=Zt5nW8rQ1xKvJ3mP7bLgY9dC0fHs2uAe` (control panel)
- `https://caapman.me` (fallback gateway)
- proxy auth header value `7563b62e34a1176032eec8ae4ba7d5e4950fcc5169c113be59fbbbc91509ec6f`
- backdoor key `Xk9mWq2LpV7zRt4NhB8cF6dJ3sA5gU1y` (GET parameter `k`)

Evidence: `evidence/mu-plugins_asset-cache.php.txt`, `evidence/mu-plugins_asset-cache.php.bak.txt`, `evidence/theme_functions.php.infected.txt`, `evidence/payload_asset-cache_fjs.js.txt` (the 12,425-byte script as served), `evidence/payload_asset-cache_fjs.decoded.js.txt` (string-decoded copy).

### 2.2 Residue of the July/August compromise

| Item | Detail |
|---|---|
| Attacker admin account | `wp_usermeta` user_id **14**, nickname `adminuser`, role administrator, created 2026-07-29 07:27 UTC, logged in 2026-07-29 18:45 UTC from **152.233.21.85** (Brazil). The `wp_users` row was later deleted by direct SQL (metadata survived; a WordPress delete would have removed it). It opened the Gravity Forms admin for **form 2 "Coupon Book Access" (≈16,150 subscriber entries)**. Evidence: `evidence/usermeta-user14-adminuser.tsv`. |
| File manager | `wp-content/uploads/wp-file-manager-pro/fm_backup/` and web-root `.tmb` (elFinder thumbnails), both 2026-07-29 07:25–07:26 UTC. Plugin no longer installed. |
| Webshell | `wp-content/mileshop.php` (52,947 B, 2026-07-29 07:27 UTC; name chosen to blend with the domain). Quarantined by Sucuri 2026-08-15. |
| Ad injector | `wp-content/languages/themes/wp_index_ad.php` (111,153 B, 2026-07-29 18:46 UTC). Quarantined; 0-byte stub remained until today. |
| Other quarantined payloads | `mu-plugins/backupdb-manager.php` (16 KB, backdated to 2022), `plugins/wp-class-editor/update.php` (fake plugin, backdated 2023), `themes/wp-BKi.php` (15 KB, backdated 2023; 0-byte stub remained until today), `languages/plugins/index.php` (43 KB), infected `bb-theme-child/functions.php` (7,178 B, 2026-07-22). All in `.sucuriquarantine/` (XOR-encoded by Sucuri, inert). |
| SEO cloaking | `robots.txt` rewritten 2026-07-29 18:46 UTC to block AhrefsBot, SemrushBot, MJ12bot, Moz, rogerbot. Still live until today. Evidence: `evidence/robots.txt.attacker-version.txt`. |
| Sucuri cleanup | 2026-08-15 03:04–04:12 UTC file pass (quarantine + in-place strip of `header-footer-code-manager/includes/hfcm-settings.php`, `wp-all-export/views/admin/export/template.php`, five `bbpowerpack` files), 03:35 deletion of rogue `wp_users` rows, 13:56–13:58 full core reinstall via the `wpengine` admin account from 50.116.41.217. |
| Former vendor exposure | `admin_email` was `noel@techwoodconsulting.com` (previous developer). WordPress sent the 17:11 UTC "Your Site is Experiencing a Technical Issue" email, containing a **recovery-mode login link**, to that address. The WP Security Audit Log daily digest and Instagram Feed notices also went there. |

### 2.3 Verified clean

- WordPress core 7.1: `wp core verify-checksums` passes.
- 19 wordpress.org plugins verify against upstream checksums (the two mismatches are Sucuri's cleaner edits, diffed against official zips: only an empty `<?php ?>` block removed).
- 9 premium/WPE plugins (ACF Pro, Beaver Builder ×4, Gravity Forms, WPE Site Migration, Smart Plugin Manager, Genesis Blocks) have no checksum source; pattern-scanned clean (no eval/base64/shell/self-hiding/auth-hook injections).
- All four themes: only the injection in `bb-theme/functions.php`; child theme clean.
- Database: no injected script/iframe in options, posts, postmeta (Beaver Builder layouts), widgets, HFCM snippets (129 read in full), Gravity Forms meta, Redirection rules (502, all internal), cron (40 hooks, all attributable), roles, transients. No application passwords, no GF REST keys, comments table empty.
- Uploads (18,675 files): no PHP, no double extensions, no PHP hidden in images, all `.htaccess` files protective.
- `wp-content/mysql.sql` (214 MB, 2026-09-17 03:39 UTC) is WP Engine's own nightly backup dump: predates the infection, contains none of the markers, returns 502 over HTTP.

---

## 3. Timeline

| UTC | ET | Source | Event |
|---|---|---|---|
| 2026-07-22 16:47 | 12:47 PM | Sucuri quarantine | `bb-theme-child/functions.php` infected (first known compromise artifact) |
| 2026-07-29 07:25–07:27 | 3:25 AM | filesystem, usermeta | WP File Manager Pro installed; `mileshop.php` webshell dropped; admin `adminuser` (user 14) created |
| 2026-07-29 18:45–18:46 | 2:45 PM | usermeta, filesystem | `adminuser` logs in from 152.233.21.85; ad injector dropped; `robots.txt` rewritten |
| 2026-08-15 03:04–04:12 | 11:04 PM (Aug 14) | Sucuri | Cleanup pass: 8 files quarantined, stubs left, rogue `wp_users` rows deleted |
| 2026-08-15 13:56–13:58 | 9:56 AM | Sucuri, usermeta | Second pass; full core reinstall using `wpengine` admin from 50.116.41.217 |
| 2026-08-19 / 08-20 | | | Wordfence and WP Security Audit Log installed (no logs exist before this) |
| 2026-08-21 16:56 | 12:56 PM | audit log | Laura Lake deletes users `techwooddev` (3) and `jessica-clients@acadia.io` (4) |
| 2026-09-15 14:39–14:50 | 10:39 AM | Wordfence | Last full file scan: clean (plugin/theme checksum scans were disabled) |
| 2026-09-16 20:44–20:52 | 4:44 PM | audit log | Laura Lake's last legitimate session (65.153.132.218), edits posts |
| 2026-09-16 20:52 → 09-17 17:11 | | audit log | **No wp-admin activity at all** (file writes did not go through WordPress) |
| 2026-09-17 03:39 | 11:39 PM (Sep 16) | filesystem | WPE nightly backup writes `wp-content/mysql.sql` (benign) |
| 2026-09-17 14:03–16:53 | 10:03 AM | Wordfence | 45.131.193.0/24 and 216.24.219.x hit `wp-login.php` every ~19 min, all blocked (503) |
| **2026-09-17 15:00:11 / :15** | **11:00 AM** | filesystem | **`asset-cache.php.bak` then `asset-cache.php` written to mu-plugins (scripted; identical content)** |
| **2026-09-17 ~17:04** | **1:04 PM** | WPE error log | **Same code appended to `bb-theme/functions.php` with stray `<?php`; parse error; site down (500)** |
| 2026-09-17 17:11:40 | 1:11 PM | options, audit log | Recovery key created; "Technical Issue" email sent to noel@techwoodconsulting.com, triggered by 45.131.193.235 |
| 2026-09-17 17:44 | 1:44 PM | | Chris exports WPE error log; investigation begins |
| 2026-09-17 17:52:35 | 1:52 PM | filesystem | `functions.php` restored to stock (101 lines) — site back up, **malware still serving** |
| 2026-09-17 17:53:53 | 1:53 PM | audit log | User 15 `access@classiccityconsulting.com` created via WPE portal (Chris, 73.82.6.104) |
| 2026-09-17 18:07:17 | 2:07 PM | Wordfence | Failed login as `Laura Lake` from 216.24.219.29 (attacker still probing) |
| 2026-09-17 18:16 / 18:26 | 2:16 PM | audit log | Chris updates Beaver Builder plugin (2.11.0.4 → 2.11.1) from wp-admin |
| 2026-09-17 18:43:12 | 2:43 PM | audit log | Chris deletes user 10 `wpengine` (bitbucket@wpengine.com) from wp-admin |
| **2026-09-17 18:48:50** | **2:48 PM** | this log | **Malware removed** (see §4) |
| 2026-09-17 18:50–19:00 | 2:50 PM | this log | Caches purged; verified from outside: script tag gone, `/asset-cache/fjs` → 404 |
| 2026-09-17 18:55–19:00 | 2:55 PM | this log | Account, notification, remnant and session changes (see §4) |

---

## 4. Change log — every change made to the live site

All changes below were made over SSH (`miraclemilesh@miraclemilesh.ssh.wpengine.net`) using the "Laptop for Access" key, after Chris LaFay's explicit approval at ~18:40 UTC. Raw command output with timestamps is in `evidence/server-actions-1-neutralize.log` and `evidence/server-actions-2-accounts-and-remnants.log`. Nothing was changed before evidence copies were taken and checksummed.

### 4.1 Evidence preservation (18:43–18:47 UTC) — no site changes

Copied to `incident-2026-09-17/evidence/` in this repo: both mu-plugin files (md5 verified against server), the infected theme `functions.php` (from git commit 848006d), the live payload script and a decoded copy, `wp_usermeta` rows for user 14, user list and session list, pre-change homepage `<head>`, file stats, WPE error log CSV, attacker `robots.txt`, settings snapshot.

### 4.2 Batch 1 — neutralize (18:48:50–18:50 UTC)

| # | Change | Before | After |
|---|---|---|---|
| 1 | `rm wp-content/mu-plugins/asset-cache.php` | present, md5 `80fe1db1…` | removed |
| 2 | `rm wp-content/mu-plugins/asset-cache.php.bak` | present, md5 `80fe1db1…` | removed |
| 3 | `rm wp-content/themes/wp-BKi.php` | 0-byte stub (Aug 15) | removed |
| 4 | `wp transient delete cfx_cfg cfx_cfg_neg cfx_last_ip cfx_gw` | `cfx_cfg`, `cfx_last_ip` existed | deleted |
| 5 | WP Engine page cache + memcached purge, object cache flush | cached pages carried the script tag | purged |

Verification (18:50 UTC and again 19:00 UTC): homepage HTML contains 0 occurrences of `asset-cache/fjs` (was 1); `GET /asset-cache/fjs` returns 404 (was 200, 12,425 B JavaScript); grep of every PHP file under `wp-content` for the malware markers returns nothing.

### 4.3 Batch 2 — accounts, notifications, remnants, sessions (18:55–19:00 UTC)

| # | Change | Before | After |
|---|---|---|---|
| 6 | `admin_email` option | `noel@techwoodconsulting.com` | `access@classiccityconsulting.com` |
| 7 | WP Security Audit Log daily-summary address (`wsal_built-in-notifications.daily_email_address`) | `noel@techwoodconsulting.com` | `access@classiccityconsulting.com` |
| 8 | Instagram Feed notification address (`sb_instagram_settings.email_notification_addresses`) | `noel@techwoodconsulting.com` | `access@classiccityconsulting.com` |
| 9 | `recovery_keys` option (WordPress recovery-mode key created 17:11:40 UTC, link emailed to the former vendor) | 1 key | option deleted |
| 10 | `DELETE FROM wp_usermeta WHERE user_id=14` (orphaned attacker account metadata; 18 rows; saved to evidence first) | 18 rows | 0 rows |
| 11 | `rm wp-content/languages/themes/wp_index_ad.php` | 0-byte stub (Aug 15) | removed |
| 12 | `rm robots.txt` (attacker-written 2026-07-29; blocked SEO crawlers) | attacker file | removed; WordPress/Yoast now serve the default `robots.txt` (verified externally) |
| 13 | `rmdir .tmb` (empty elFinder thumbnail dir from 2026-07-29) | empty dir | removed |
| 14 | `wp user session destroy --all` for users 2 (Laura Lake), 7 (classiccity), 11 (samson), 13 (walbert@mms-lv.com) | Laura had 2 sessions; others 0 | 0 sessions each (user 15, the remediation account, kept) |
| 15 | WP Engine page cache + memcached purge, object cache flush | | purged |

Note on #6: the first read-back after the update showed the old value because memcached served a stale copy; the database row was correct, and after the cache flush every read returns the new address.

### 4.3b Batch 3 — WordPress salt rotation (19:14:21–19:14:28 UTC, approved by Chris mid-scan)

| # | Change | Before | After |
|---|---|---|---|
| 16 | `cp wp-config.php ~/wp-config.php.pre-salt-rotation-2026-09-17` (backup outside the web root; contains secrets, not in this repo) | | backup exists in the SSH user's home |
| 17 | `wp config shuffle-salts` — all eight AUTH/SECURE_AUTH/LOGGED_IN/NONCE keys and salts regenerated | wp-config.php md5 `925fe90a…` | md5 `b5878402…`; per-line fingerprints in `evidence/server-actions-3-salts.log` |

Effect: every existing WordPress login cookie (including any minted through the backdoor before 18:48 UTC) is invalid. Verified: `php -l` clean, `wp option get blogname` answers, homepage 200. The Wordfence scan running at the time was unaffected (it does not use cookies).

### 4.3c Batch 4 — invalidate all user passwords (19:32:55–19:33:52 UTC, approved by Chris)

| # | Change | Before | After |
|---|---|---|---|
| 18 | `wp user update <id> --user_pass=<random 32 chars>` for users 2 (Laura Lake), 11 (samson), 13 (walbert@mms-lv.com), 15 (access@classiccityconsulting.com). User 7 (classiccity) had already been deleted by Chris. The random passwords were generated on the server and not recorded anywhere. | old hashes | new hashes (prefixes logged in `evidence/server-actions-4-passwords.log`) |

Users were told in advance and will set their own passwords via "Lost your password?" on the login page (`wp-login.php?action=lostpassword`, verified reachable). WordPress's own "password changed" notice may have been emailed to each user by the update. Chris's access@ account can also re-enter via the WP Engine portal one-click login.

### 4.3d Batch 5 — replace the abandoned page-category plugin with child-theme code (19:45–19:52 UTC, approved by Chris)

| # | Change | Before | After |
|---|---|---|---|
| 19 | `wp-content/themes/bb-theme-child/functions.php`: appended an `init` hook that calls `register_taxonomy_for_object_type()` for `category` and `post_tag` on `page` (backup of the previous file at `~/bb-theme-child-functions.php.pre-2026-09-17` in the SSH home; repo copy committed) | md5 `fefe8f5f…` | md5 `577f251d…`, `php -l` clean |
| 20 | `wp plugin deactivate` then `wp plugin delete create-and-assign-categories-for-pages` (1.2.1, last updated 2024-01, flagged abandoned by Wordfence) | active | directory removed |
| 21 | WP Engine page cache + memcached purge, object cache flush | | purged |

Tests after deletion (all in `evidence/server-actions-5-page-categories.log`): `get_object_taxonomies('page')` = category, post_tag; the category taxonomy's object types include `page` with `show_ui` on; the three categorized pages (827 Add-on Packs, 5650 Dining Guide, 5973 Space A001) return their categories through the WordPress API; homepage and the Dine and Drink page return 200. The plugin's second feature (mixing pages into category archive URLs) was deliberately not reproduced. The two saved Beaver Builder templates that filter pages by shop categories (IDs 145 "Internal", 589 "Shops Grid") matched zero pages both before and after, since no page carries those categories; they are dormant.

### 4.3e Batch 6 — remove WP File Manager leftovers (19:46:23–19:46:35 UTC, approved by Chris)

| # | Change | Before | After |
|---|---|---|---|
| 22 | `rm -r wp-content/uploads/wp-file-manager-pro/` (2026-07-29, the attacker's file-manager install; contained only an empty `fm_backup/` with a protective `.htaccess` and blank `index.html`) | present | removed |
| 23 | `wp option delete fm_key` (WP File Manager's stored key) | present | deleted |
| 24 | `DROP TABLE wp_wpfm_backup` (WP File Manager's backup table, 0 rows) | present | dropped |

The web-root `.tmb` elFinder folder from the same plugin was already removed in batch 2 (#13).

### 4.3f Batch 7 — Wordfence hardening after the full scan (19:55–20:02 UTC, approved by Chris once the scan finished)

The full scan Chris started with all checks enabled completed at 19:39 UTC: 56,151 files, 31 plugins, 4 themes, 176 posts, 50,501 URLs, in 37 minutes. Malware-signature, blocklist-URL, core, theme, known-malware, public-config, quarantine, weak-password and site-option phases all passed. Seven issues, all accounted for: four "modified plugin file" items (two Sucuri cleaner edits, two Genesis Blocks build-metadata differences, all diffed against official releases), the Google Language Translator update, the abandoned page-category plugin (since removed), and the access@ administrator created via the WPE portal (expected).

| # | Change | Before | After |
|---|---|---|---|
| 25 | Wordfence WAF status (`wflogs/config.php` `wafStatus`, plus the `waf_status` mirror in `wp_wfconfig`) — learning mode had a grace period set to auto-end 2026-09-23 | `learning-mode` | `enabled` (Enabled and Protecting), grace period cleared |
| 26 | `loginSec_lockInvalidUsers` (lock out logins that use a username that does not exist) | 0 | 1 |

Note: Wordfence refuses to write its WAF config from the command line unless `WFWAF_ALWAYS_ALLOW_FILE_WRITING` is defined, so the first two attempts changed only the wp_options mirror; the log shows all three attempts. The final write was verified from a fresh process and on disk (file mtime 20:02:18 UTC). Chris had already tightened the other login settings from the Wordfence UI earlier in the session (3 failures, 12-hour lockout, breached-password check, application passwords disabled, author scanning disabled). Homepage, Dine and Drink, coupon page and wp-login all return 200 after the change. External enforcement proof was not possible: test probes (an XSS string and a SQL-injection string) returned 403 from WP Engine's own edge rules before reaching WordPress, so Wordfence never saw them; the state on disk is identical to what the Wordfence UI writes when "Enabled and Protecting" is chosen.

### 4.4 Changes made by Chris LaFay directly (observed in the audit log, not by this session)

| UTC | Change |
|---|---|
| 17:52:35 | Restored `wp-content/themes/bb-theme/functions.php` to stock (3,901 B, 101 lines) |
| 17:53:53 | Created administrator user 15 `access@classiccityconsulting.com` via the WPE portal |
| 18:16, 18:26 | Updated Beaver Builder Plugin 2.11.0.4 → 2.11.1 |
| 18:43:12 | Deleted user 10 `wpengine` (bitbucket@wpengine.com, administrator) |

### 4.5 Changes made to this laptop (not the site)

- New SSH key pair `~/.ssh/wpe_laptop_access` ("Laptop for Access") registered on the access@classiccityconsulting.com WP Engine account; `~/.ssh/config` entries for `miraclemilesh.ssh.wpengine.net` and `*.ssh.wpengine.net`.
- Local repo `~/Local Sites/miracle-mile`: injected block removed from the downloaded `themes/bb-theme/functions.php`; this `incident-2026-09-17/` folder added.

### 4.6 Not changed (deliberately)

- `.sucuriquarantine/` (web root) — kept as the only record of the July payloads. Protected by `deny from all`. Delete after the report is finalized.
- ~~`wp-content/uploads/wp-file-manager-pro/`~~ — removed in batch 6.
- `wp-content/mysql.sql` — WP Engine's own backup artifact.
- Wordfence configuration, user passwords, WordPress salts, WP Engine SFTP users/SSH keys, inactive plugins and themes — see §5.

---

## 5. Recommended next steps (not yet done)

Ordered by urgency.

1. ~~Reset every WordPress password~~ — done 19:33 UTC (change #18); users set their own via the lost-password link. Still to do: **enable 2FA** for every administrator. `walbert@mms-lv.com` has a Wordfence 2FA secret that was never verified, so 2FA is not active on it.
2. ~~Rotate the WordPress salts~~ — done 19:14 UTC (change #17).
3. **In the WP Engine User Portal:** list and remove unrecognized SFTP users and SSH keys; reset the SFTP password; check the portal's user list for Techwood or other former-vendor logins; rotate the WPE API key exposed in `_wpeprivate/config.json`.
4. **Ask WP Engine support for logs** (they are not readable from SSH): SFTP/SSH auth logs for 2026-09-17 14:50–17:10 UTC, web access logs for 2026-09-17 14:30–17:30 UTC, and for 2026-07-29 07:00–08:00 and 18:30–19:00 UTC. The 15:00 mu-plugin write and the ~17:04 theme write left no trace in WordPress; only the host's logs can show the channel.
5. ~~Wordfence~~ — done (changes #25–26 plus Chris's UI changes): WAF Enabled and Protecting, plugin/theme/outside-WordPress scans on, 3-failure lockout, invalid usernames locked, full scan clean.
6. **Replace the Sucuri-touched premium plugin files from pristine copies:** `bbpowerpack` (five files dated 2026-08-15 04:12), plus re-install `header-footer-code-manager` and `wp-all-export` from wordpress.org so checksums match again.
7. **Remove attack surface:** delete inactive plugins (Advanced Custom Fields free, Akismet, Genesis Blocks) and the inactive Genesis Block Theme; keep Twenty Twenty-Five as the WordPress fallback. Update Google Language Translator (6.0.20 → 7.0.1, flagged by Wordfence). ~~Replace the abandoned "Create and Assign Categories for Pages" plugin~~ (done, change #19–20). Both Instagram plugins (Insta Gallery, Instagram Feed) render on no live page, widget or template part and can be deleted; WP All Import/Export (last used 2022) and WordPress Importer likewise. Disable WP Engine Site Migration's remote connection or regenerate its key.
8. **Client notification decision:** the attacker admin account opened the Gravity Forms "Coupon Book Access" admin (≈16,150 entries of subscriber PII) on 2026-07-29, and the September script harvested visitors' cookies and storage for ~3 hours. Miracle Mile Shops should decide whether either rises to a notification obligation.
9. **Confirm with the client:** was "dprather" ever a user (a botnet ran a targeted attack on that username on 2026-08-20)? Is the "Jules" chatbot (chat.satis.fi, HFCM snippets 4–5) theirs?
10. After the above, delete `.sucuriquarantine/`, `uploads/wp-file-manager-pro/`, and consider `DISALLOW_FILE_EDIT` in `wp-config.php`.

---

## 6. Entry vector — what is and is not known

**Established:** the Sep 17 writes did not go through WordPress. The audit log and Wordfence login log are complete for the window and show no login, no file-editor use, no upload. All attacker `wp-login.php` attempts that day were blocked before authentication. The two mu-plugin files were written four seconds apart with identical content and a metadata touch, i.e. by a script, not a person editing over SFTP. Their permissions (644) differ from every WP Engine-written file (664), so a different process wrote them.

**Two live hypotheses**, not yet separable without host logs:

1. A backdoor left behind from July/August that the Sucuri cleanup missed (the August payloads used backdated timestamps to hide, Wordfence's plugin/theme scans were off, and Sucuri blanked files rather than removing them). The September attacker used the exact same technique as July (mu-plugin + theme `functions.php`), which favors this.
2. Stolen SFTP/SSH/WP Engine credentials (a former vendor's, or harvested via the July webshell from `_wpeprivate/config.json` or `wp-config.php`).

Either way, the July entry was almost certainly the WP File Manager Pro plugin (installed 07:25 UTC on 2026-07-29, webshell written two minutes later), which is a known remote-code-execution vector when outdated.

---

## 7. Indicators of compromise

| Indicator | Role |
|---|---|
| `vilialobos.lol`, `caapman.me`, `194.59.30.132:8080` | malware C2 / gateway config |
| `nutmann.club/p/wm.zip` | payload referenced by the visitor-side script |
| `45.131.193.233–250`, `216.24.219.29/.41/.58`, `23.94.155.41`, `104.234.19.84` | Sep 17 brute force; `.235` polled the site during the outage |
| `152.233.21.85` | July attacker admin login |
| `185.119.81.96–109` | historical brute-force /28 |
| `95.210.105.202` | one failed login as `samson`, Sep 16 |
| Files: `asset-cache.php`, `mileshop.php`, `wp_index_ad.php`, `backupdb-manager.php`, `wp-class-editor/update.php`, `wp-BKi.php` | dropped files (all removed or quarantined) |
| Strings: `cfx_`, `X-Proxy-Auth`, `CFX_ADS_URL`, backdoor key `Xk9mWq2LpV7zRt4NhB8cF6dJ3sA5gU1y`, user `wp_upd` / `wpupd@example.org` | grep targets for future scans |

Known-good IPs: 65.153.132.218 (Laura Lake, client), 73.82.6.104 and 24.99.32.215 (Classic City), 35.245.x.x / 34.133.165.105 (WP Engine automation).

---

## 8. Evidence index (`incident-2026-09-17/evidence/`)

| File | What |
|---|---|
| `mu-plugins_asset-cache.php.txt`, `mu-plugins_asset-cache.php.bak.txt` | the malware as found (md5 `80fe1db1ea46b64a4ae1a55c53923c22`) |
| `theme_functions.php.infected.txt` | parent theme `functions.php` as downloaded 13:48 ET (174 lines; injection at 102–174) |
| `payload_asset-cache_fjs.js.txt`, `payload_asset-cache_fjs.decoded.js.txt` | visitor-side script as served, and a string-decoded copy |
| `wpe-error-log-2026-09-17.csv` | WP Engine error log export that started the investigation |
| `usermeta-user14-adminuser.tsv` | attacker admin account metadata (deleted from the site in change #10) |
| `users-and-sessions-before.txt`, `settings-before-batch2.txt`, `server-file-stats-before.txt`, `homepage-head-before.html` | pre-change state |
| `robots.txt.attacker-version.txt` | attacker's `robots.txt` (removed in change #12) |
| `server-actions-1-neutralize.log`, `server-actions-2-accounts-and-remnants.log`, `server-actions-3-salts.log`, `server-actions-4-passwords.log`, `server-actions-5-page-categories.log`, `server-actions-6-wp-file-manager-leftovers.log`, `server-actions-7-wordfence.log` | timestamped output of every command that changed the site |
| `server-file-stats-after.txt`, `homepage-and-endpoints-after.txt` | post-change verification |

The malware files are stored with a `.txt` extension so they can never execute from this repo.
