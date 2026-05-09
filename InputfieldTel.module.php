<?php namespace ProcessWire;

/**
 * InputfieldTel - Inputfield for FieldtypeTel.
 *
 * Renders intl-tel-input v28.0.1 in the ProcessWire admin.
 * Syncs four hidden inputs on change: _e164, _intl, _national, _country.
 */
class InputfieldTel extends Inputfield implements Module {

    public static function getModuleInfo(): array {
        return [
            'title'    => 'Phone Inputfield',
            'summary'  => 'Inputfield for FieldtypeTel — renders intl-tel-input.',
            'version'  => 104,
            'author'   => 'Maxim Semenov',
            'icon'     => 'phone',
            'href'     => 'https://github.com/mxmsmnv/FieldtypeTel',
            'requires' => 'FieldtypeTel',
        ];
    }

    // ── Field-level config defaults ───────────────────────────────────────────

    public static function getDefaultFieldConfig(): array {
        return [
            'field_initial_country'      => '',
            'field_allow_dropdown'       => 1,
            'field_national_mode'        => 1,
            'field_separate_dial_code'   => '',
            'field_auto_placeholder'     => 'polite',
            'field_show_dial_code'       => 0,   // showSelectedDialCode — dial code inside input
            'field_format_on_display'    => 1,   // formatOnDisplay — auto-format as user types
        ];
    }

    // ── Init ──────────────────────────────────────────────────────────────────

    public function init(): void {
        parent::init();
        foreach (self::getDefaultFieldConfig() as $key => $value) {
            $this->set($key, $value);
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function ___render(): string {
        $this->renderAssets();

        $name  = $this->attr('name');
        $value = $this->attr('value');

        $e164    = '';
        $intl    = '';
        $national = '';
        $country = '';

        if ($value instanceof TelValue) {
            $e164    = $value->e164;
            $intl    = $value->intl;
            $national = $value->national;
            $country = $value->country;
        }

        $itiOpts  = $this->buildItiOptions();
        $optsJson = json_encode($itiOpts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $assetsUrl = $this->wire('config')->urls->get('FieldtypeTel') . 'assets/iti/';

        // Display value in the visible input: national → intl → e164
        $displayValue   = $this->get('field_show_dial_code') ? ($intl ?: $e164 ?: $national) : ($national ?: $intl ?: $e164);
        $displayEscaped = htmlspecialchars($displayValue, ENT_QUOTES, 'UTF-8');

        $e164Escaped    = htmlspecialchars($e164,    ENT_QUOTES, 'UTF-8');
        $intlEscaped    = htmlspecialchars($intl,    ENT_QUOTES, 'UTF-8');
        $natEscaped     = htmlspecialchars($national, ENT_QUOTES, 'UTF-8');
        $countryEscaped = htmlspecialchars($country, ENT_QUOTES, 'UTF-8');

        $out  = "<div class='InputfieldTelWrapper' id='wrap_{$name}'>";

        // Visible ITI input
        $out .= "<input type='tel'
            id='iti_{$name}'
            class='InputfieldTelInput uk-input'
            value='{$displayEscaped}'
            autocomplete='tel'>";

        // Hidden storage fields — all four formats
        $out .= "<input type='hidden' name='{$name}_e164'    id='{$name}_e164'    value='{$e164Escaped}'>";
        $out .= "<input type='hidden' name='{$name}_intl'    id='{$name}_intl'    value='{$intlEscaped}'>";
        $out .= "<input type='hidden' name='{$name}_national' id='{$name}_national' value='{$natEscaped}'>";
        $out .= "<input type='hidden' name='{$name}_country'  id='{$name}_country'  value='{$countryEscaped}'>";

        $out .= "</div>";

        $out .= "
<script>
(function() {
    var assetsUrl    = " . json_encode($assetsUrl) . ";
    var opts         = {$optsJson};
    var storedCountry = " . json_encode($country) . ";

    var inputEl = document.getElementById('iti_{$name}');
    if (!inputEl) return;

    function initIti() {
        var utilsReady = false;

        opts.loadUtils = function() {
            return import(assetsUrl + 'utils.js').then(function(m) {
                utilsReady = true;
                if (inputEl.value.trim()) {
                    syncHiddenFields();
                }
                return m;
            });
        };

        // Render dropdown in body so it appears above sticky Save button
        opts.useFullscreenPopup = false;
        opts.dropdownContainer = document.body;

        var iti = window.intlTelInput(inputEl, opts);

        if (storedCountry) {
            iti.setCountry(storedCountry);
        }

        function syncHiddenFields() {
            var e164El    = document.getElementById('{$name}_e164');
            var intlEl    = document.getElementById('{$name}_intl');
            var natEl     = document.getElementById('{$name}_national');
            var countryEl = document.getElementById('{$name}_country');

            var raw = inputEl.value.trim();

            if (raw === '') {
                e164El.value = intlEl.value = natEl.value = countryEl.value = '';
                return;
            }

            var countryData = iti.getSelectedCountryData();
            countryEl.value = countryData ? (countryData.iso2 || '') : '';

            if (utilsReady) {
                e164El.value = iti.getNumber('E164') || raw;
                intlEl.value = iti.getNumber('INTERNATIONAL') || raw;
                natEl.value  = iti.getNumber('NATIONAL') || raw;
            } else {
                // utils not loaded yet — store raw, will be re-synced once utils resolve
                e164El.value = raw;
                intlEl.value = raw;
                natEl.value  = raw;
            }
        }

        inputEl.addEventListener('input',         syncHiddenFields);
        inputEl.addEventListener('change',        syncHiddenFields);
        inputEl.addEventListener('blur',          syncHiddenFields);
        inputEl.addEventListener('countrychange', syncHiddenFields);

        var form = inputEl.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                syncHiddenFields();
            });
        }

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIti);
    } else {
        initIti();
    }
})();
</script>";

        return $out;
    }

    // ── Process POST ──────────────────────────────────────────────────────────

    public function ___processInput(WireInputData $input): self {
        parent::___processInput($input);

        $name = $this->attr('name');
        $san  = $this->wire('sanitizer');

        $e164    = $san->text($input->{$name . '_e164'}    ?? '');
        $intl    = $san->text($input->{$name . '_intl'}    ?? '');
        $national = $san->text($input->{$name . '_national'} ?? '');
        $country = $san->alphanumeric($input->{$name . '_country'} ?? '');

        $parts = FieldtypeTel::normalizePhoneParts($e164, $intl, $national, $country);
        $e164 = $parts['e164'];
        $intl = $parts['intl'];
        $national = $parts['national'];
        $country = $parts['country'];

        $tel = new TelValue();
        $tel->e164    = $e164;
        $tel->intl    = $intl;
        $tel->national = $national;
        $tel->country = $country;
        $all = FieldtypeTel::getAllCountries();
        if ($country && isset($all[$country])) {
            $tel->dialCode = $all[$country]['dial'];
        }

        $this->attr('value', $tel);

        return $this;
    }

    // ── Assets ────────────────────────────────────────────────────────────────

    protected function renderAssets(): void {
        $config  = $this->wire('config');
        $base    = $config->urls->get('FieldtypeTel') . 'assets/iti/';

        $config->styles->add($base . 'intlTelInput.min.css');
        $config->scripts->add($base . 'intlTelInput.min.js');

        if (!$config->get('_InputfieldTelStyles')) {
            $config->set('_InputfieldTelStyles', true);
            $config->styles->add($config->urls->get('FieldtypeTel') . 'assets/InputfieldTel.css');
        }
    }

    // ── Build ITI options ─────────────────────────────────────────────────────

    protected function buildItiOptions(): array {
        $moduleCfg = $this->wire('modules')->getConfig('FieldtypeTel');
        $moduleCfg = array_merge(FieldtypeTel::getDefaultData(), $moduleCfg ?: []);

        $opts = [];

        $onlyCountries = $moduleCfg['only_countries'] ?? [];
        if (!empty($onlyCountries)) {
            $opts['onlyCountries'] = array_values($onlyCountries);
        }

        $preferred = $moduleCfg['preferred_countries'] ?? [];
        if (!empty($preferred)) {
            $opts['countryOrder'] = array_values($preferred);
        }

        // Initial country: field override → module config
        $fieldInitial = $this->get('field_initial_country');
        if ($fieldInitial) {
            $opts['initialCountry'] = $fieldInitial;
        } elseif (!empty($moduleCfg['initial_country'])) {
            $opts['initialCountry'] = $moduleCfg['initial_country'];
        }

        // Separate dial code: field override → module config
        $fieldSeparate = $this->get('field_separate_dial_code');
        if ($fieldSeparate !== '') {
            $opts['separateDialCode'] = (bool) $fieldSeparate;
        } else {
            $opts['separateDialCode'] = !empty($moduleCfg['separate_dial_code']);
        }

        if (!$this->get('field_allow_dropdown')) {
            $opts['allowDropdown'] = false;
        }

        if (!$this->get('field_national_mode') || $this->get('field_show_dial_code')) {
            $opts['nationalMode'] = false;
        }

        $opts['autoPlaceholder'] = $this->get('field_auto_placeholder') ?: 'polite';

        // Format number as user types (default on)
        if (!$this->get('field_format_on_display')) {
            $opts['formatOnDisplay'] = false;
        }

        return $opts;
    }

}
