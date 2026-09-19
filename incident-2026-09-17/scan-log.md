# Scan log — miraclemileshopslv.com

Status board per the `/site-scan` playbook. One row per milestone; updated the moment a milestone finishes.
Times UTC. The 2026-09-17 incident itself predates this board; its milestones are recorded in
`INCIDENT-REPORT.md` §4 and `BUILD_LOG.md` Milestone 1.

## Rescan 2026-09-18 (day-1 watch, Round 1 only)

| Round | Milestone | Agent (model) | Status | Finding in one line | Evidence file |
|---|---|---|---|---|---|
| 1 | M1.6 live site from outside | main session | done 14:5x | 200; scripts only from self, Klaviyo, GTM, satis.fi, gtranslate; 0 markers; `/asset-cache/fjs` 404; robots.txt = Yoast default; dump 502 over HTTP | (chat) |
| 1 | M1.1–M1.5, M1.9 filesystem hot spots | H1 (Haiku) | done 15:0x, QA'd | mu-plugins = 6 WPE files only; 0 marker/obfuscation/Cyrillic hits; both functions.php stock (101 / 30 lines, one `<?php`); 16 uploads/languages PHP files all 0 bytes; only WPE platform files written today; core checksums OK. QA note: agent truncated theme list (4 themes on disk, 2 inactive) | `rescan-2026-09-18-h1.txt` |
| 1 | M1.7–M1.8 accounts + settings | H2 (Haiku) | done 15:1x, QA'd | 4 expected admins, no orphan meta, no app passwords; admin_email correct; recovery_keys empty; 0 marker hits in options/posts/postmeta/HFCM/redirects; Wordfence WAF on, 0 new issues, 0 logins; WSAL shows only Laura's edits and Chris's plugin removals. QA note: agent misread recovery_keys as present | `rescan-2026-09-18-h2.txt` |
| 1 | Sonnet classification pass | — | skipped | no unexplained hits from H1/H2 | |

Round 1 result 2026-09-18: **clean**. Next watch per playbook M3.6: day 3 (2026-09-20) and day 7 (2026-09-24).
