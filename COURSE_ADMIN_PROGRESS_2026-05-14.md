# Course Admin Progress - 2026-05-14

## Focus

Fix the admin Create/Edit Course lesson-to-module workflow without changing the database structure or rebuilding the course system.

## Completed Today

- Reworked lesson tab filtering so lesson tabs are rebuilt from the selected module's lesson list.
- Switched active lesson selection away from tab indexes and onto stable lesson keys.
- Kept saved lessons anchored to their real `lessons.id`.
- Added lesson payload fields for `id`, `course_id`, `course_module_id`, `title`, and `sort_order`.
- Updated save logic so existing lessons are updated by `lesson.id`.
- Changed module assignment persistence so a submitted real `course_module_id` wins over stale module keys.
- Kept `module_key` only as a bridge for newly created modules that do not have a database ID until save.
- Removed module-title fallback from lesson module assignment.
- Ensured preview/member lesson ordering can be rebuilt from module relationships using `lessons.course_module_id`.

## Current Rule

Lessons belong to modules through `lessons.course_module_id`.

Do not use display labels, tab indexes, sort order, or array position as the relationship source of truth.

## Verification Added

- Added a feature test that moves an existing lesson from one module to another using the real `course_module_id`, even when the submitted `module_key` is stale.
- The test confirms the lesson leaves the old module and appears under the new module.

## Next Checks

- Open an existing course in admin.
- Move a saved lesson from Module 1 to Module 4.
- Save, refresh, and confirm the lesson remains under Module 4.
- Open preview and confirm the sidebar groups the lesson under Module 4.

## Member Lesson Page / Flow Builder Update

- Removed the Course Discussion card from the member lesson page.
- Kept the Ask In Community button.
- Replaced completed lesson "OK" labels with a check mark.
- Updated Mark Complete so it redirects to the next lesson when one exists.
- Changed member lesson rendering so any saved flow block suppresses old fixed lesson fields.
- Simplified new Lesson Flow Builder blocks to Text, Image, Video, PDF, and Presentation.
- Replaced Canva embed behavior with Presentation resource/open-card behavior.
- Presentation uploads use local Laravel storage through existing lesson content block file storage.
