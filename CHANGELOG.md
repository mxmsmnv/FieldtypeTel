# Changelog

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