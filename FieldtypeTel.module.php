<?php namespace ProcessWire;

require_once __DIR__ . '/TelValue.php';

/**
 * FieldtypeTel - International phone number fieldtype for ProcessWire.
 *
 * Stores phone numbers in four formats:
 *   - E.164        (data column)    — "+12025550123"    — use for tel: links & search
 *   - International (intl column)   — "+1 202-555-0123" — use for display (intl)
 *   - National     (national col.)  — "(202) 555-0123"  — use for display (default)
 *   - ISO2         (country column) — "us"              — use for filtering
 *
 * Powered by intl-tel-input v28.0.1 (MIT).
 *
 * @copyright 2025
 * @license MIT
 */
/**
 * TelValue - WireData object representing a single phone number value.
 *
 * Properties:
 *   $value->e164     — E.164 format:         "+12025550123"   (for tel: links)
 *   $value->intl     — International format: "+1 202-555-0123" (for display)
 *   $value->national — National format:      "(202) 555-0123" (for display, default)
 *   $value->country  — ISO2 country code:    "us"
 *   $value->dialCode — Dial code:            "1"
 *
 * Usage in templates:
 *   echo $page->phone;                    // national format (default)
 *   echo $page->phone->intl;             // international format
 *   echo $page->phone->e164;             // E.164
 *   echo $page->phone->country;          // iso2
 *   <a href="tel:<?= $page->phone->e164 ?>"><?= $page->phone ?></a>
 */
class FieldtypeTel extends Fieldtype implements Module, ConfigurableModule {

    // ── Module info ───────────────────────────────────────────────────────────

    public static function getModuleInfo(): array {
        return [
            'title'    => 'Phone',
            'summary'  => 'International phone number field powered by intl-tel-input.',
            'version'  => 103,
            'author'   => 'Maxim Semenov',
            'icon'     => 'phone',
            'href'     => 'https://github.com/mxmsmnv/FieldtypeTel',
            'installs' => 'InputfieldTel',
            'requires' => 'InputfieldTel',
        ];
    }

    // ── All supported countries (ISO2 => [dial, name]) ────────────────────────

    public static function getAllCountries(): array {
        return [
            'ac' => ['dial' => '247', 'name' => 'Ascension Island'],
            'ad' => ['dial' => '376', 'name' => 'Andorra'],
            'ae' => ['dial' => '971', 'name' => 'United Arab Emirates'],
            'af' => ['dial' => '93',  'name' => 'Afghanistan'],
            'ag' => ['dial' => '1',   'name' => 'Antigua & Barbuda'],
            'ai' => ['dial' => '1',   'name' => 'Anguilla'],
            'al' => ['dial' => '355', 'name' => 'Albania'],
            'am' => ['dial' => '374', 'name' => 'Armenia'],
            'ao' => ['dial' => '244', 'name' => 'Angola'],
            'ar' => ['dial' => '54',  'name' => 'Argentina'],
            'as' => ['dial' => '1',   'name' => 'American Samoa'],
            'at' => ['dial' => '43',  'name' => 'Austria'],
            'au' => ['dial' => '61',  'name' => 'Australia'],
            'aw' => ['dial' => '297', 'name' => 'Aruba'],
            'ax' => ['dial' => '358', 'name' => 'Åland Islands'],
            'az' => ['dial' => '994', 'name' => 'Azerbaijan'],
            'ba' => ['dial' => '387', 'name' => 'Bosnia & Herzegovina'],
            'bb' => ['dial' => '1',   'name' => 'Barbados'],
            'bd' => ['dial' => '880', 'name' => 'Bangladesh'],
            'be' => ['dial' => '32',  'name' => 'Belgium'],
            'bf' => ['dial' => '226', 'name' => 'Burkina Faso'],
            'bg' => ['dial' => '359', 'name' => 'Bulgaria'],
            'bh' => ['dial' => '973', 'name' => 'Bahrain'],
            'bi' => ['dial' => '257', 'name' => 'Burundi'],
            'bj' => ['dial' => '229', 'name' => 'Benin'],
            'bl' => ['dial' => '590', 'name' => 'St. Barthélemy'],
            'bm' => ['dial' => '1',   'name' => 'Bermuda'],
            'bn' => ['dial' => '673', 'name' => 'Brunei'],
            'bo' => ['dial' => '591', 'name' => 'Bolivia'],
            'bq' => ['dial' => '599', 'name' => 'Caribbean Netherlands'],
            'br' => ['dial' => '55',  'name' => 'Brazil'],
            'bs' => ['dial' => '1',   'name' => 'Bahamas'],
            'bt' => ['dial' => '975', 'name' => 'Bhutan'],
            'bw' => ['dial' => '267', 'name' => 'Botswana'],
            'by' => ['dial' => '375', 'name' => 'Belarus'],
            'bz' => ['dial' => '501', 'name' => 'Belize'],
            'ca' => ['dial' => '1',   'name' => 'Canada'],
            'cc' => ['dial' => '61',  'name' => 'Cocos (Keeling) Islands'],
            'cd' => ['dial' => '243', 'name' => 'Congo - Kinshasa'],
            'cf' => ['dial' => '236', 'name' => 'Central African Republic'],
            'cg' => ['dial' => '242', 'name' => 'Congo - Brazzaville'],
            'ch' => ['dial' => '41',  'name' => 'Switzerland'],
            'ci' => ['dial' => '225', 'name' => 'Côte d\'Ivoire'],
            'ck' => ['dial' => '682', 'name' => 'Cook Islands'],
            'cl' => ['dial' => '56',  'name' => 'Chile'],
            'cm' => ['dial' => '237', 'name' => 'Cameroon'],
            'cn' => ['dial' => '86',  'name' => 'China'],
            'co' => ['dial' => '57',  'name' => 'Colombia'],
            'cr' => ['dial' => '506', 'name' => 'Costa Rica'],
            'cu' => ['dial' => '53',  'name' => 'Cuba'],
            'cv' => ['dial' => '238', 'name' => 'Cape Verde'],
            'cw' => ['dial' => '599', 'name' => 'Curaçao'],
            'cx' => ['dial' => '61',  'name' => 'Christmas Island'],
            'cy' => ['dial' => '357', 'name' => 'Cyprus'],
            'cz' => ['dial' => '420', 'name' => 'Czechia'],
            'de' => ['dial' => '49',  'name' => 'Germany'],
            'dj' => ['dial' => '253', 'name' => 'Djibouti'],
            'dk' => ['dial' => '45',  'name' => 'Denmark'],
            'dm' => ['dial' => '1',   'name' => 'Dominica'],
            'do' => ['dial' => '1',   'name' => 'Dominican Republic'],
            'dz' => ['dial' => '213', 'name' => 'Algeria'],
            'ec' => ['dial' => '593', 'name' => 'Ecuador'],
            'ee' => ['dial' => '372', 'name' => 'Estonia'],
            'eg' => ['dial' => '20',  'name' => 'Egypt'],
            'eh' => ['dial' => '212', 'name' => 'Western Sahara'],
            'er' => ['dial' => '291', 'name' => 'Eritrea'],
            'es' => ['dial' => '34',  'name' => 'Spain'],
            'et' => ['dial' => '251', 'name' => 'Ethiopia'],
            'fi' => ['dial' => '358', 'name' => 'Finland'],
            'fj' => ['dial' => '679', 'name' => 'Fiji'],
            'fk' => ['dial' => '500', 'name' => 'Falkland Islands'],
            'fm' => ['dial' => '691', 'name' => 'Micronesia'],
            'fo' => ['dial' => '298', 'name' => 'Faroe Islands'],
            'fr' => ['dial' => '33',  'name' => 'France'],
            'ga' => ['dial' => '241', 'name' => 'Gabon'],
            'gb' => ['dial' => '44',  'name' => 'United Kingdom'],
            'gd' => ['dial' => '1',   'name' => 'Grenada'],
            'ge' => ['dial' => '995', 'name' => 'Georgia'],
            'gf' => ['dial' => '594', 'name' => 'French Guiana'],
            'gg' => ['dial' => '44',  'name' => 'Guernsey'],
            'gh' => ['dial' => '233', 'name' => 'Ghana'],
            'gi' => ['dial' => '350', 'name' => 'Gibraltar'],
            'gl' => ['dial' => '299', 'name' => 'Greenland'],
            'gm' => ['dial' => '220', 'name' => 'Gambia'],
            'gn' => ['dial' => '224', 'name' => 'Guinea'],
            'gp' => ['dial' => '590', 'name' => 'Guadeloupe'],
            'gq' => ['dial' => '240', 'name' => 'Equatorial Guinea'],
            'gr' => ['dial' => '30',  'name' => 'Greece'],
            'gt' => ['dial' => '502', 'name' => 'Guatemala'],
            'gu' => ['dial' => '1',   'name' => 'Guam'],
            'gw' => ['dial' => '245', 'name' => 'Guinea-Bissau'],
            'gy' => ['dial' => '592', 'name' => 'Guyana'],
            'hk' => ['dial' => '852', 'name' => 'Hong Kong SAR China'],
            'hn' => ['dial' => '504', 'name' => 'Honduras'],
            'hr' => ['dial' => '385', 'name' => 'Croatia'],
            'ht' => ['dial' => '509', 'name' => 'Haiti'],
            'hu' => ['dial' => '36',  'name' => 'Hungary'],
            'id' => ['dial' => '62',  'name' => 'Indonesia'],
            'ie' => ['dial' => '353', 'name' => 'Ireland'],
            'il' => ['dial' => '972', 'name' => 'Israel'],
            'im' => ['dial' => '44',  'name' => 'Isle of Man'],
            'in' => ['dial' => '91',  'name' => 'India'],
            'io' => ['dial' => '246', 'name' => 'British Indian Ocean Territory'],
            'iq' => ['dial' => '964', 'name' => 'Iraq'],
            'ir' => ['dial' => '98',  'name' => 'Iran'],
            'is' => ['dial' => '354', 'name' => 'Iceland'],
            'it' => ['dial' => '39',  'name' => 'Italy'],
            'je' => ['dial' => '44',  'name' => 'Jersey'],
            'jm' => ['dial' => '1',   'name' => 'Jamaica'],
            'jo' => ['dial' => '962', 'name' => 'Jordan'],
            'jp' => ['dial' => '81',  'name' => 'Japan'],
            'ke' => ['dial' => '254', 'name' => 'Kenya'],
            'kg' => ['dial' => '996', 'name' => 'Kyrgyzstan'],
            'kh' => ['dial' => '855', 'name' => 'Cambodia'],
            'ki' => ['dial' => '686', 'name' => 'Kiribati'],
            'km' => ['dial' => '269', 'name' => 'Comoros'],
            'kn' => ['dial' => '1',   'name' => 'St. Kitts & Nevis'],
            'kp' => ['dial' => '850', 'name' => 'North Korea'],
            'kr' => ['dial' => '82',  'name' => 'South Korea'],
            'kw' => ['dial' => '965', 'name' => 'Kuwait'],
            'ky' => ['dial' => '1',   'name' => 'Cayman Islands'],
            'kz' => ['dial' => '7',   'name' => 'Kazakhstan'],
            'la' => ['dial' => '856', 'name' => 'Laos'],
            'lb' => ['dial' => '961', 'name' => 'Lebanon'],
            'lc' => ['dial' => '1',   'name' => 'St. Lucia'],
            'li' => ['dial' => '423', 'name' => 'Liechtenstein'],
            'lk' => ['dial' => '94',  'name' => 'Sri Lanka'],
            'lr' => ['dial' => '231', 'name' => 'Liberia'],
            'ls' => ['dial' => '266', 'name' => 'Lesotho'],
            'lt' => ['dial' => '370', 'name' => 'Lithuania'],
            'lu' => ['dial' => '352', 'name' => 'Luxembourg'],
            'lv' => ['dial' => '371', 'name' => 'Latvia'],
            'ly' => ['dial' => '218', 'name' => 'Libya'],
            'ma' => ['dial' => '212', 'name' => 'Morocco'],
            'mc' => ['dial' => '377', 'name' => 'Monaco'],
            'md' => ['dial' => '373', 'name' => 'Moldova'],
            'me' => ['dial' => '382', 'name' => 'Montenegro'],
            'mf' => ['dial' => '590', 'name' => 'St. Martin'],
            'mg' => ['dial' => '261', 'name' => 'Madagascar'],
            'mh' => ['dial' => '692', 'name' => 'Marshall Islands'],
            'mk' => ['dial' => '389', 'name' => 'North Macedonia'],
            'ml' => ['dial' => '223', 'name' => 'Mali'],
            'mm' => ['dial' => '95',  'name' => 'Myanmar (Burma)'],
            'mn' => ['dial' => '976', 'name' => 'Mongolia'],
            'mo' => ['dial' => '853', 'name' => 'Macao SAR China'],
            'mp' => ['dial' => '1',   'name' => 'Northern Mariana Islands'],
            'mq' => ['dial' => '596', 'name' => 'Martinique'],
            'mr' => ['dial' => '222', 'name' => 'Mauritania'],
            'ms' => ['dial' => '1',   'name' => 'Montserrat'],
            'mt' => ['dial' => '356', 'name' => 'Malta'],
            'mu' => ['dial' => '230', 'name' => 'Mauritius'],
            'mv' => ['dial' => '960', 'name' => 'Maldives'],
            'mw' => ['dial' => '265', 'name' => 'Malawi'],
            'mx' => ['dial' => '52',  'name' => 'Mexico'],
            'my' => ['dial' => '60',  'name' => 'Malaysia'],
            'mz' => ['dial' => '258', 'name' => 'Mozambique'],
            'na' => ['dial' => '264', 'name' => 'Namibia'],
            'nc' => ['dial' => '687', 'name' => 'New Caledonia'],
            'ne' => ['dial' => '227', 'name' => 'Niger'],
            'nf' => ['dial' => '672', 'name' => 'Norfolk Island'],
            'ng' => ['dial' => '234', 'name' => 'Nigeria'],
            'ni' => ['dial' => '505', 'name' => 'Nicaragua'],
            'nl' => ['dial' => '31',  'name' => 'Netherlands'],
            'no' => ['dial' => '47',  'name' => 'Norway'],
            'np' => ['dial' => '977', 'name' => 'Nepal'],
            'nr' => ['dial' => '674', 'name' => 'Nauru'],
            'nu' => ['dial' => '683', 'name' => 'Niue'],
            'nz' => ['dial' => '64',  'name' => 'New Zealand'],
            'om' => ['dial' => '968', 'name' => 'Oman'],
            'pa' => ['dial' => '507', 'name' => 'Panama'],
            'pe' => ['dial' => '51',  'name' => 'Peru'],
            'pf' => ['dial' => '689', 'name' => 'French Polynesia'],
            'pg' => ['dial' => '675', 'name' => 'Papua New Guinea'],
            'ph' => ['dial' => '63',  'name' => 'Philippines'],
            'pk' => ['dial' => '92',  'name' => 'Pakistan'],
            'pl' => ['dial' => '48',  'name' => 'Poland'],
            'pm' => ['dial' => '508', 'name' => 'St. Pierre & Miquelon'],
            'pr' => ['dial' => '1',   'name' => 'Puerto Rico'],
            'ps' => ['dial' => '970', 'name' => 'Palestinian Territories'],
            'pt' => ['dial' => '351', 'name' => 'Portugal'],
            'pw' => ['dial' => '680', 'name' => 'Palau'],
            'py' => ['dial' => '595', 'name' => 'Paraguay'],
            'qa' => ['dial' => '974', 'name' => 'Qatar'],
            're' => ['dial' => '262', 'name' => 'Réunion'],
            'ro' => ['dial' => '40',  'name' => 'Romania'],
            'rs' => ['dial' => '381', 'name' => 'Serbia'],
            'ru' => ['dial' => '7',   'name' => 'Russia'],
            'rw' => ['dial' => '250', 'name' => 'Rwanda'],
            'sa' => ['dial' => '966', 'name' => 'Saudi Arabia'],
            'sb' => ['dial' => '677', 'name' => 'Solomon Islands'],
            'sc' => ['dial' => '248', 'name' => 'Seychelles'],
            'sd' => ['dial' => '249', 'name' => 'Sudan'],
            'se' => ['dial' => '46',  'name' => 'Sweden'],
            'sg' => ['dial' => '65',  'name' => 'Singapore'],
            'sh' => ['dial' => '290', 'name' => 'St. Helena'],
            'si' => ['dial' => '386', 'name' => 'Slovenia'],
            'sj' => ['dial' => '47',  'name' => 'Svalbard & Jan Mayen'],
            'sk' => ['dial' => '421', 'name' => 'Slovakia'],
            'sl' => ['dial' => '232', 'name' => 'Sierra Leone'],
            'sm' => ['dial' => '378', 'name' => 'San Marino'],
            'sn' => ['dial' => '221', 'name' => 'Senegal'],
            'so' => ['dial' => '252', 'name' => 'Somalia'],
            'sr' => ['dial' => '597', 'name' => 'Suriname'],
            'ss' => ['dial' => '211', 'name' => 'South Sudan'],
            'st' => ['dial' => '239', 'name' => 'São Tomé & Príncipe'],
            'sv' => ['dial' => '503', 'name' => 'El Salvador'],
            'sx' => ['dial' => '1',   'name' => 'Sint Maarten'],
            'sy' => ['dial' => '963', 'name' => 'Syria'],
            'sz' => ['dial' => '268', 'name' => 'Eswatini'],
            'tc' => ['dial' => '1',   'name' => 'Turks & Caicos Islands'],
            'td' => ['dial' => '235', 'name' => 'Chad'],
            'tg' => ['dial' => '228', 'name' => 'Togo'],
            'th' => ['dial' => '66',  'name' => 'Thailand'],
            'tj' => ['dial' => '992', 'name' => 'Tajikistan'],
            'tk' => ['dial' => '690', 'name' => 'Tokelau'],
            'tl' => ['dial' => '670', 'name' => 'Timor-Leste'],
            'tm' => ['dial' => '993', 'name' => 'Turkmenistan'],
            'tn' => ['dial' => '216', 'name' => 'Tunisia'],
            'to' => ['dial' => '676', 'name' => 'Tonga'],
            'tr' => ['dial' => '90',  'name' => 'Türkiye'],
            'tt' => ['dial' => '1',   'name' => 'Trinidad & Tobago'],
            'tv' => ['dial' => '688', 'name' => 'Tuvalu'],
            'tw' => ['dial' => '886', 'name' => 'Taiwan'],
            'tz' => ['dial' => '255', 'name' => 'Tanzania'],
            'ua' => ['dial' => '380', 'name' => 'Ukraine'],
            'ug' => ['dial' => '256', 'name' => 'Uganda'],
            'us' => ['dial' => '1',   'name' => 'United States'],
            'uy' => ['dial' => '598', 'name' => 'Uruguay'],
            'uz' => ['dial' => '998', 'name' => 'Uzbekistan'],
            'va' => ['dial' => '39',  'name' => 'Vatican City'],
            'vc' => ['dial' => '1',   'name' => 'St. Vincent & Grenadines'],
            've' => ['dial' => '58',  'name' => 'Venezuela'],
            'vg' => ['dial' => '1',   'name' => 'British Virgin Islands'],
            'vi' => ['dial' => '1',   'name' => 'U.S. Virgin Islands'],
            'vn' => ['dial' => '84',  'name' => 'Vietnam'],
            'vu' => ['dial' => '678', 'name' => 'Vanuatu'],
            'wf' => ['dial' => '681', 'name' => 'Wallis & Futuna'],
            'ws' => ['dial' => '685', 'name' => 'Samoa'],
            'xk' => ['dial' => '383', 'name' => 'Kosovo'],
            'ye' => ['dial' => '967', 'name' => 'Yemen'],
            'yt' => ['dial' => '262', 'name' => 'Mayotte'],
            'za' => ['dial' => '27',  'name' => 'South Africa'],
            'zm' => ['dial' => '260', 'name' => 'Zambia'],
            'zw' => ['dial' => '263', 'name' => 'Zimbabwe'],
        ];
    }

    // ── Module-level config defaults ──────────────────────────────────────────

    public static function getDefaultData(): array {
        return [
            'only_countries'      => [],
            'preferred_countries' => ['us', 'gb'],
            'initial_country'     => 'us',
            'separate_dial_code'  => 0,
        ];
    }

    public function __construct() {
        parent::__construct();
        foreach (self::getDefaultData() as $key => $value) {
            $this->$key = $value;
        }
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    public function init(): void {
        parent::init();
        $this->addHookAfter('Pages::saved', $this, 'hookSaveFromPost');
        $this->addHookAfter('ProcessField::processInput', $this, 'hookProcessFieldInput');
    }

    public function hookSaveFromPost(HookEvent $event): void {
        $page = $event->arguments(0);
        if(!$page instanceof Page) return;
        if(!$this->wire('process') instanceof ProcessPageEdit) return;
        $input = $this->wire('input');
        if(!$input || !$input->requestMethod('POST')) return;

        foreach($page->template->fieldgroup as $field) {
            if(!$field->type instanceof FieldtypeTel) continue;

            $name     = $field->name;
            $e164     = (string)($input->post($name . '_e164')    ?? '');
            $intl     = (string)($input->post($name . '_intl')    ?? '');
            $national = (string)($input->post($name . '_national') ?? '');
            $country  = (string)($input->post($name . '_country')  ?? '');

            $san      = $this->wire('sanitizer');
            $e164     = $san->text($e164);
            $intl     = $san->text($intl);
            $national = $san->text($national);
            $country  = $san->alphanumeric($country);

            // Validate country against known list
            $all = self::getAllCountries();
            if ($country && !isset($all[$country])) $country = '';

            // If all values empty — delete the row and move on
            if($e164 === '' && $intl === '' && $national === '') {
                $db    = $this->wire('database');
                $table = $db->escapeTable($field->getTable());
                $stmt  = $db->prepare("DELETE FROM `{$table}` WHERE pages_id=:pid");
                $stmt->bindValue(':pid', $page->id, \PDO::PARAM_INT);
                $stmt->execute();
                continue;
            }

            // Normalize: strip all non-digit chars from raw number for E.164 assembly
            $digits   = preg_replace('/[^0-9]/', '', $e164);
            $dialCode = isset($all[$country]) ? $all[$country]['dial'] : '';

            // If e164 looks unformatted (no leading +dialCode), rebuild it
            if($dialCode && $digits) {
                if(str_starts_with($digits, $dialCode)) {
                    $e164 = '+' . $digits;
                } else {
                    $e164 = '+' . $dialCode . $digits;
                }
            } elseif($digits) {
                $e164 = '+' . $digits;
            }

            // If JS didn't format properly, derive intl/national from e164 server-side
            if(!($intl && str_starts_with($intl, '+'))) {
                if($dialCode && str_starts_with($e164, '+' . $dialCode)) {
                    $national = substr($e164, 1 + strlen($dialCode));
                } else {
                    $national = ltrim($e164, '+');
                }
                $intl = $dialCode ? '+' . $dialCode . ' ' . $national : $e164;
            }

            $db    = $this->wire('database');
            $table = $db->escapeTable($field->getTable());
            $sql   = "INSERT INTO `{$table}` (pages_id, `data`, `intl`, `national`, `country`)
                      VALUES(:pid, :e164, :intl, :national, :country)
                      ON DUPLICATE KEY UPDATE
                        `data`=VALUES(`data`), `intl`=VALUES(`intl`),
                        `national`=VALUES(`national`), `country`=VALUES(`country`)";

            $stmt = $this->wire('database')->prepare($sql);
            $stmt->bindValue(':pid',      $page->id, \PDO::PARAM_INT);
            $stmt->bindValue(':e164',     $e164,     \PDO::PARAM_STR);
            $stmt->bindValue(':intl',     $intl,     \PDO::PARAM_STR);
            $stmt->bindValue(':national', $national, \PDO::PARAM_STR);
            $stmt->bindValue(':country',  $country,  \PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    public function hookProcessFieldInput(HookEvent $event): void {
        $field = $event->arguments(0);
        if(!$field instanceof Field) return;
        if(!$field->type instanceof FieldtypeTel) return;

        $input = $this->wire('input');
        $checkboxKeys = [
            'field_allow_dropdown',
            'field_national_mode',
            'field_show_dial_code',
            'field_format_on_display',
        ];
        foreach($checkboxKeys as $key) {
            $field->set($key, (int) $input->post->$key);
        }
    }

        // ── Value lifecycle ───────────────────────────────────────────────────────

    public function getBlankValue(Page $page, Field $field): TelValue {
        return new TelValue();
    }

    public function sanitizeValue(Page $page, Field $field, $value) {
        if ($value instanceof TelValue) return $value;
        $tel = $this->getBlankValue($page, $field);
        if (is_array($value)) {
            $san = $this->wire('sanitizer');
            if (!empty($value['e164']))     $tel->e164     = $san->text($value['e164']);
            if (!empty($value['intl']))     $tel->intl     = $san->text($value['intl']);
            if (!empty($value['national'])) $tel->national = $san->text($value['national']);
            if (!empty($value['country']))  $tel->country  = $san->alphanumeric($value['country']);
        }
        return $tel;
    }

    public function ___wakeupValue(Page $page, Field $field, $value): TelValue {
        $tel = $this->getBlankValue($page, $field);
        if (!is_array($value)) return $tel;

        if (isset($value['data']))     $tel->e164     = $value['data'];
        if (isset($value['intl']))     $tel->intl     = $value['intl'];
        if (isset($value['national'])) $tel->national = $value['national'];
        if (isset($value['country']))  $tel->country  = $value['country'];

        // Derive dialCode from country
        if ($tel->country) {
            $all = self::getAllCountries();
            if (isset($all[$tel->country])) {
                $tel->dialCode = $all[$tel->country]['dial'];
            }
        }

        return $tel;
    }

    public function ___sleepValue(Page $page, Field $field, $value): array {
        if (!$value instanceof TelValue) return [];
        $san = $this->wire('sanitizer');
        return [
            'data'     => $san->text($value->e164),
            'intl'     => $san->text($value->intl),
            'national' => $san->text($value->national),
            'country'  => $san->alphanumeric($value->country),
        ];
    }

    public function isDeleteValue(Page $page, Field $field, $value): bool {
        return $value instanceof TelValue && $value->isEmpty();
    }

    // ── Inputfield ────────────────────────────────────────────────────────────

    public function getInputfield(Page $page, Field $field): InputfieldTel {
        /** @var InputfieldTel $f */
        $f = $this->modules->get('InputfieldTel');
        foreach (InputfieldTel::getDefaultFieldConfig() as $key => $default) {
            $val = $field->get($key);
            $f->set($key, ($val !== null && $val !== '') ? $val : $default);
        }
        return $f;
    }

    public function ___getCompatibleFieldtypes(Field $field): ?Fieldtypes {
        return null;
    }

    // ── Markup output (Lister, etc.) ──────────────────────────────────────────

    public function ___markupValue(Page $page, Field $field, $value = null, $property = ''): string {
        if (!$value instanceof TelValue || $value->isEmpty()) return '';
        $display = $value->national ?: $value->intl ?: $value->e164;
        $e164    = htmlspecialchars($value->e164, ENT_QUOTES, 'UTF-8');
        $display = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
        return "<a href=\"tel:{$e164}\">{$display}</a>";
    }

    // ── Page selector support ─────────────────────────────────────────────────

    public function getMatchQuery($query, $table, $subfield, $operator, $value) {
        $col = match($subfield) {
            'country'  => 'country',
            'intl'     => 'intl',
            'national' => 'national',
            default    => 'data', // e164
        };

        if ($this->wire('database')->isOperator($operator)) {
            return parent::getMatchQuery($query, $table, $col, $operator, $value);
        }
        $ft = new DatabaseQuerySelectFulltext($query);
        $ft->match($table, $col, $operator, $value);
        return $query;
    }

    // ── Database schema ───────────────────────────────────────────────────────

    public function getDatabaseSchema(Field $field): array {
        // Minimal schema so PW schema-diff machinery does not touch our columns.
        // Actual table is managed in ___createField / ___deleteField.
        $schema = parent::getDatabaseSchema($field);
        $schema['data'] = "varchar(20) NOT NULL DEFAULT '' COMMENT 'E.164'";
        unset($schema['keys']['data']);
        return $schema;
    }

    public function ___createField(Field $field): bool {
        $db    = $this->wire('database');
        $table = $db->escapeTable($field->getTable());
        $db->exec("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `pages_id` INT UNSIGNED  NOT NULL,
                `data`     VARCHAR(20)   NOT NULL DEFAULT '' COMMENT 'E.164, e.g. +12025550123',
                `intl`     VARCHAR(50)   NOT NULL DEFAULT '' COMMENT 'International, e.g. +1 202-555-0123',
                `national` VARCHAR(50)   NOT NULL DEFAULT '' COMMENT 'National, e.g. (202) 555-0123',
                `country`  CHAR(2)       NOT NULL DEFAULT '' COMMENT 'ISO2, e.g. us',
                PRIMARY KEY (`pages_id`),
                KEY `data`    (`data`),
                KEY `country` (`country`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return true;
    }

    public function ___deleteField(Field $field): bool {
        $db    = $this->wire('database');
        $table = $db->escapeTable($field->getTable());
        $db->exec("DROP TABLE IF EXISTS `{$table}`");
        return true;
    }

    public function getLoadQueryAutojoin(Field $field, DatabaseQuerySelect $query): ?DatabaseQuerySelect {
        return null;
    }

    public function loadPageField(Page $page, Field $field) {
        $db    = $this->wire('database');
        $table = $field->getTable();
        try {
            $stmt = $db->prepare("SELECT `data`, `intl`, `national`, `country` FROM `{$table}` WHERE pages_id=:pid");
            $stmt->bindValue(':pid', $page->id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->wire('log')->error("FieldtypeTel: loadPageField failed for page {$page->id}: " . $e->getMessage());
            return null;
        }
        if (!$row) {
            return null;
        }
        return [
            'data'     => $row['data'],
            'intl'     => $row['intl'],
            'national' => $row['national'],
            'country'  => $row['country'],
        ];
    }

    public function savePageField(Page $page, Field $field): bool {
        $value = $page->get($field->name);
        if (!$value instanceof TelValue) return true;

        if ($this->isDeleteValue($page, $field, $value)) {
            return $this->deletePageField($page, $field);
        }

        $table = $field->getTable();
        $sql   = "INSERT INTO `{$table}` (pages_id, `data`, `intl`, `national`, `country`)
                  VALUES(:pid, :e164, :intl, :national, :country)
                  ON DUPLICATE KEY UPDATE
                    `data`=VALUES(`data`),
                    `intl`=VALUES(`intl`),
                    `national`=VALUES(`national`),
                    `country`=VALUES(`country`)";

        $stmt = $this->wire('database')->prepare($sql);
        $stmt->bindValue(':pid',      $page->id,        \PDO::PARAM_INT);
        $stmt->bindValue(':e164',     $value->e164,     \PDO::PARAM_STR);
        $stmt->bindValue(':intl',     $value->intl,     \PDO::PARAM_STR);
        $stmt->bindValue(':national', $value->national, \PDO::PARAM_STR);
        $stmt->bindValue(':country',  $value->country,  \PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }

    // ── Module-level config ───────────────────────────────────────────────────

    public function getModuleConfigInputfields(array $data) {
        $data = array_merge(self::getDefaultData(), $data);

        $inputfields = new InputfieldWrapper();
        $all         = self::getAllCountries();

        /** @var InputfieldAsmSelect $f */
        $f = $this->modules->get('InputfieldAsmSelect');
        $f->attr('name', 'only_countries');
        $f->label = 'Allowed Countries';
        $f->description = 'Restrict the country dropdown to these countries. Leave empty to allow all.';
        foreach ($all as $iso2 => $info) {
            $f->addOption($iso2, "{$info['name']} (+{$info['dial']})");
        }
        $f->attr('value', $data['only_countries'] ?: []);
        $inputfields->add($f);

        /** @var InputfieldAsmSelect $f */
        $f = $this->modules->get('InputfieldAsmSelect');
        $f->attr('name', 'preferred_countries');
        $f->label = 'Preferred Countries';
        $f->description = 'These countries appear at the top of the dropdown, separated by a divider.';
        foreach ($all as $iso2 => $info) {
            $f->addOption($iso2, "{$info['name']} (+{$info['dial']})");
        }
        $f->attr('value', $data['preferred_countries'] ?: []);
        $inputfields->add($f);

        /** @var InputfieldSelect $f */
        $f = $this->modules->get('InputfieldSelect');
        $f->attr('name', 'initial_country');
        $f->label = 'Default Country';
        $f->description = 'Pre-selected country when the field is empty.';
        $f->columnWidth = 50;
        $f->addOption('', '— Auto-detect —');
        foreach ($all as $iso2 => $info) {
            $f->addOption($iso2, "{$info['name']} (+{$info['dial']})");
        }
        $f->attr('value', $data['initial_country']);
        $inputfields->add($f);

        /** @var InputfieldCheckbox $f */
        $f = $this->modules->get('InputfieldCheckbox');
        $f->attr('name', 'separate_dial_code');
        $f->label = 'Show dial code separately';
        $f->description = 'Display the selected country\'s dial code next to the flag, outside the input.';
        $f->attr('checked', !empty($data['separate_dial_code']) ? 'checked' : '');
        $f->attr('value', 1);
        $inputfields->add($f);

        return $inputfields;
    }

    public function ___getConfigInputfields(Field $field) {
        $inputfields = parent::___getConfigInputfields($field);
        $all         = self::getAllCountries();
        $defaults    = InputfieldTel::getDefaultFieldConfig();

        $get = function(string $key) use ($field, $defaults) {
            $val = $field->get($key);
            // Explicitly stored value (including 0) takes priority over defaults
            if($val !== null && $val !== '') return $val;
            return $defaults[$key] ?? null;
        };

        // 1. Default Country
        /** @var InputfieldSelect $f */
        $f = $this->modules->get('InputfieldSelect');
        $f->attr('name', 'field_initial_country');
        $f->label = 'Default Country';
        $f->description = 'Pre-selected country when the field is empty. Overrides the module-level default.';
        $f->columnWidth = 50;
        $f->addOption('', '— Inherit from module —');
        foreach ($all as $iso2 => $info) {
            $f->addOption($iso2, "{$info['name']} (+{$info['dial']})");
        }
        $f->attr('value', $get('field_initial_country') ?: '');
        $inputfields->add($f);

        // 2. Allow dropdown
        /** @var InputfieldCheckbox $f */
        $f = $this->modules->get('InputfieldCheckbox');
        $f->attr('name', 'field_allow_dropdown');
        $f->label = 'Allow country dropdown';
        $f->columnWidth = 50;
        $f->attr('checked', $get('field_allow_dropdown') ? 'checked' : '');
        $f->attr('value', 1);
        $inputfields->add($f);

        // 3. National mode
        /** @var InputfieldCheckbox $f */
        $f = $this->modules->get('InputfieldCheckbox');
        $f->attr('name', 'field_national_mode');
        $f->label = 'National mode';
        $f->description = 'Enter national numbers (e.g. 0201 234567) instead of international.';
        $f->columnWidth = 34;
        $f->attr('checked', $get('field_national_mode') ? 'checked' : '');
        $f->attr('value', 1);
        $inputfields->add($f);

        // 4. Separate dial code
        /** @var InputfieldSelect $f */
        $f = $this->modules->get('InputfieldSelect');
        $f->attr('name', 'field_separate_dial_code');
        $f->label = 'Separate dial code';
        $f->description = 'Show dial code next to the flag, outside the input.';
        $f->columnWidth = 33;
        $f->addOption('',  '— Inherit from module —');
        $f->addOption('1', 'Yes');
        $f->addOption('0', 'No');
        $f->attr('value', $get('field_separate_dial_code') ?? '');
        $inputfields->add($f);

        // 5. Show dial code in input
        /** @var InputfieldCheckbox $f */
        $f = $this->modules->get('InputfieldCheckbox');
        $f->attr('name', 'field_show_dial_code');
        $f->label = 'Show dial code in input';
        $f->description = 'Display the dial code inside the input field, e.g. "+1 (202) 555-0123".';
        $f->columnWidth = 33;
        $f->attr('checked', $get('field_show_dial_code') ? 'checked' : '');
        $f->attr('value', 1);
        $inputfields->add($f);

        // 6. Format on display
        /** @var InputfieldCheckbox $f */
        $f = $this->modules->get('InputfieldCheckbox');
        $f->attr('name', 'field_format_on_display');
        $f->label = 'Format number as user types';
        $f->description = 'Auto-format the number with spaces and brackets while typing.';
        $f->columnWidth = 50;
        $f->attr('checked', $get('field_format_on_display') ? 'checked' : '');
        $f->attr('value', 1);
        $inputfields->add($f);

        // 7. Auto placeholder
        /** @var InputfieldSelect $f */
        $f = $this->modules->get('InputfieldSelect');
        $f->attr('name', 'field_auto_placeholder');
        $f->label = 'Auto Placeholder';
        $f->columnWidth = 50;
        $f->addOption('polite',     'Polite — only if no placeholder set');
        $f->addOption('aggressive', 'Aggressive — always override placeholder');
        $f->addOption('off',        'Off — no auto placeholder');
        $f->attr('value', $get('field_auto_placeholder') ?: 'polite');
        $inputfields->add($f);

        return $inputfields;
    }
}