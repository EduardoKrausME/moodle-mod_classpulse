# mod_classpulse - Class pulse

Moodle activity for collecting a quick signal of learner understanding during a class.

The default response options are:

- 😕 I did not understand;
- 😐 More or less;
- 🙂 I understand;
- 😄 I master it.

Teachers can configure the question, allow or prevent learners from changing their response, enable anonymous mode, and choose the default chart used by the report. The aggregate report refreshes automatically while it is open and can be switched between pie, bar, and line charts.

## Anonymous mode privacy

When anonymous mode is enabled, `classpulse_votes.userid` is stored as `0`. To prevent multiple responses from the same user, the activity stores an activity-specific HMAC SHA-256 value in `respondenthash`. The teacher dashboard never receives this identifier and works only with aggregate counts.

This mechanism keeps the learner identity hidden from the teacher interface and aggregate report. Site administrators with privileged database and source-code access should still treat the identifier as a technical pseudonym rather than absolute cryptographic anonymity.

## Backup and restore

Activity configuration is included in backups. Identified responses are restored when the corresponding users are also restored. For anonymous activities, responses are not included in user-data backups, which avoids orphaned or duplicated records when identifiers change between environments.

A Portuguese version of this document is available in [README.pt_br.md](README.pt_br.md).
