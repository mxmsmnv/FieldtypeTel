# Changelog

## [1.0.3] — 2026-05-08

### Fixed

- **`getInputfield()`** now copies all field-level settings (`field_initial_country`, `field_allow_dropdown`, `field_national_mode`, `field_separate_dial_code`, `field_show_dial_code`, `field_format_on_display`, `field_auto_placeholder`) from the `Field` object into the `InputfieldTel` instance. Previously per-field configuration was saved but never applied when rendering the input.
- **`savePageField()`** now calls `isDeleteValue()` and deletes the database row when the phone number is cleared, instead of inserting empty strings.
- **`hookSaveFromPost()`** when all POST values are empty, now deletes the row instead of inserting empty strings — fixes the case where `savePageField()` correctly deleted the row but the hook immediately recreated it.
- **`hookSaveFromPost()`** now validates the POSTed country code against the supported countries list before saving, consistent with `InputfieldTel::___processInput()`.
- **`InputfieldTel::___processInput()`** now calls `parent::___processInput()` to ensure ProcessWire change tracking works correctly.
- Removed dead variables (`$intlDigits`, `$e164Digits`, `$natDigits`, `$expectedPrefix`) from `hookSaveFromPost()`.
- Removed redundant `require_once 'TelValue.php'` call inside `init()` (file is already loaded at the top of `FieldtypeTel.module.php`).
- **JS `syncHiddenFields()`** replaced the `window.intlTelInputUtils` global check with a `utilsReady` closure flag. In intl-tel-input v18+, utils are loaded as an ES module and are not exposed as a global, so the previous check always failed — hidden fields always stored the raw typed value instead of formatted E.164/international/national.
- **`hookSaveFromPost()`** now checks `process instanceof ProcessPageEdit` before running, preventing accidental deletion of phone data when pages are saved programmatically via the API during any POST request.
- **`loadPageField()`** now catches `\PDOException` specifically instead of `\Exception`, and logs the error via `$this->wire('log')` instead of silently returning null.
- **JS event listeners**: replaced `keyup` with `input` so that paste, drag-drop and other non-keyboard edits also trigger hidden-field sync. Removed stale comment from form submit handler.

## [1.0.0] — 2026-04-27

Initial release.

### Features

- `FieldtypeTel` fieldtype storing four formats per phone number: E.164 (`data`), international (`intl`), national (`national`), and ISO2 country code (`country`).
- `InputfieldTel` inputfield powered by intl-tel-input v28.0.1 — bundled assets, no CDN dependency.
- `TelValue` value object with `e164`, `intl`, `national`, `country`, and `dialCode` properties.
- Module-level configuration: allowed countries, preferred countries, default country, separate dial code.
- Field-level configuration in the Details tab: per-field country default, national mode, allow dropdown, separate dial code, show dial code in input, format on display, auto placeholder.
- Full AdminThemeUikit theme integration — all colors and borders via `--pw-*` CSS variables with automatic light/dark mode support.
- Flag sprite (PNG + WebP, 1x + 2x) bundled from intl-tel-input v28.
- Saves directly from POST via `Pages::saved` hook, bypassing ProcessWire change-tracking for reliable persistence.
- Page selector support: `phone.country=us`, `phone*=+1202`.