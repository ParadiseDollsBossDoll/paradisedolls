# Boss Doll Creators AI Router

This repository uses `docs/ai` as the project memory for AI-assisted work.

## Bootstrap

For quick questions, read only this file and the specific file the user mentions.

For code changes, read these first:

1. `docs/ai/00-ai-project-map.md`
2. `docs/ai/02-current-state.md`
3. `docs/ai/05-ai-rules.md`
4. The relevant entry in `docs/ai/01-feature-index.md`

Then inspect only the listed code files for the feature being changed. Expand the search only when those files reveal another dependency.

## Context Rules

- Do not scan the whole repository by default.
- Do not load logs, archives, generated assets, `node_modules`, `vendor`, `.env`, or uploaded/storage files unless directly required.
- Treat code as the source of truth when documentation and implementation disagree.
- Keep durable decisions in `docs/ai/03-decisions-log.md`.
- Keep meaningful completed work summaries in `docs/ai/04-change-log.md`.
- Update `docs/ai/02-current-state.md` when the active project state changes.

## Project Notes

- Visible brand name is Boss Doll Creators.
- Retain Paradise Dolls only in the existing domain/email/social URLs, infrastructure identifiers, and legal agreement wording until replacements are confirmed.
- Production domain is `https://getrichwithparadisedolls.com`.
- Production server path is `/var/www/paradisedollz`.
- Laravel, Blade, Vite, MySQL, queues, scheduler, and Reverb are the main stack.
- Never commit `.env` files, credentials, API keys, private uploads, or personal Obsidian workspace state.
