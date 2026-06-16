# FieldtypeTel

International phone number fieldtype for ProcessWire, powered by [intl-tel-input v28](https://github.com/jackocnr/intl-tel-input) (bundled — no CDN dependency).

**Author:** Maxim Semenov  
**Website:** [smnv.org](https://smnv.org)  
**Email:** [maxim@smnv.org](mailto:maxim@smnv.org)

If this project helps your work, consider supporting future development: [GitHub Sponsors](https://github.com/sponsors/mxmsmnv) or [smnv.org/sponsor](https://smnv.org/sponsor/).  

## Features

- Country picker with flag icons and search
- Stores four formats per number:
  - **E.164** (`+12025550123`) — for `tel:` links and API use
  - **International** (`+1 202-555-0123`) — for international display
  - **National** (`(202) 555-0123`) — for local display (default)
  - **ISO2 country code** (`us`) — for filtering and selectors
- Fully themed to AdminThemeUikit — light and dark mode via `--pw-*` CSS variables
- Module-level config: restrict countries, preferred countries, default country, separate dial code
- Field-level config in the Details tab: all settings per-field

## Requirements

- ProcessWire 3.0.200+
- PHP 8.2+
- AdminThemeUikit

## Installation

1. Copy the `FieldtypeTel` folder into `/site/modules/`
2. Go to **Modules → Refresh**, then install **FieldtypeTel**
3. InputfieldTel installs automatically

## Module Configuration

Go to **Modules → Configure → FieldtypeTel**:

| Setting | Description |
|---|---|
| Allowed Countries | Restrict the dropdown to specific countries (empty = all) |
| Preferred Countries | Shown at the top of the dropdown with a divider |
| Default Country | Pre-selected when the field is empty |
| Show dial code separately | Display the dial code next to the flag, outside the input |

## Field Configuration

All field settings are in the **Details** tab when editing a field:

| Setting | Default | Description |
|---|---|---|
| Default Country | — inherit | Overrides the module default for this field |
| Allow country dropdown | ✓ | Disable for fixed-country fields |
| National mode | ✓ | Enter numbers without the country prefix |
| Separate dial code | — inherit | Override module setting per-field |
| Show dial code in input | ☐ | Show dial code inside the input: `+1 (202) 555-0123` |
| Format number as user types | ✓ | Auto-format with spaces and brackets while typing |
| Auto Placeholder | polite | polite / aggressive / off |

## Usage in Templates

```php
$phone = $page->phone; // TelValue object

// Default output — national format
echo $phone;                    // (202) 555-0123

// All formats
echo $phone->national;          // (202) 555-0123
echo $phone->intl;              // +1 202-555-0123
echo $phone->e164;              // +12025550123
echo $phone->country;           // us
echo $phone->dialCode;          // 1

// tel: link
echo "<a href='tel:{$phone->e164}'>{$phone}</a>";

// Empty check
if (!$phone->isEmpty()) { ... }
```

## Page Selectors

```php
// Find pages with an Australian phone number
$pages->find("phone.country=au");

// Find by E.164 prefix
$pages->find("phone*=+61");
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT

## Author

Maxim Semenov — [smnv.org](https://smnv.org) — maxim@smnv.org  
GitHub: [@mxmsmnv](https://github.com/mxmsmnv)