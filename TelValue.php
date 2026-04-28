<?php namespace ProcessWire;

/**
 * TelValue — value object for FieldtypeTel.
 *
 * Properties:
 *   ->e164     "+12025550123"    E.164 — use for tel: links
 *   ->intl     "+1 202-555-0123" International format
 *   ->national "(202) 555-0123"  National format (default __toString)
 *   ->country  "us"              ISO2 country code
 *   ->dialCode "1"               Dial code without +
 */
class TelValue {

    public string $e164     = '';
    public string $intl     = '';
    public string $national = '';
    public string $country  = '';
    public string $dialCode = '';

    public function isEmpty(): bool {
        return $this->e164 === '';
    }

    /**
     * Best available display value.
     * Prefers properly formatted national, falls back to intl, then e164.
     * If national/intl look unformatted (equal to raw digits), returns intl with dial code.
     */
    public function __toString(): string {
        // If national is set and looks formatted (not just digits+dashes), use it
        if ($this->national && $this->national !== $this->e164) {
            return $this->national;
        }
        // intl with + prefix is always readable
        if ($this->intl && str_starts_with($this->intl, '+')) {
            return $this->intl;
        }
        if ($this->e164) return $this->e164;
        return '';
    }

    public function get(string $key): string {
        return $this->$key ?? '';
    }

    public function set(string $key, string $value): void {
        if (property_exists($this, $key)) $this->$key = $value;
    }
}