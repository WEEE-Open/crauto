<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$loggedin = Authentication::isLoggedIn();
if ($loggedin) {
	$template = Template::create();
	$template->addData(['authenticated' => $loggedin, 'isAdmin' => $loggedin && Authentication::isAdmin()], 'navbar');
	echo $template->render('403');
	exit;
}

// Lauree
// Array.prototype.slice.call(document.getElementById('d_html').querySelectorAll('a[href]')).map(a => a.textContent.trim()).filter(text => text !== 'qui').map(a => a.split(' ').map(w => w[0].toUpperCase() + w.substr(1).toLowerCase()).join(' ')).reduce((str, a) => str + '\n' + a, '')
// Dottorati
// Array.prototype.slice.call(document.getElementById('d_html').querySelectorAll('a[href]')).map(a => a.textContent.trim()).filter(text => text !== 'qui').map(a => a.split(' ').map(w => w[0].toUpperCase() + w.substr(1).toLowerCase()).join(' ')).map(a => 'Dottorato in ' + a).reduce((str, a) => str + '\n' + a, '')
// and some manual fixes.
$degreeCourses = [
	'Architecture',
	'Architecture Construction City',
	'Architecture For The Sustainability Design',
	'Architettura',
	'Architettura Costruzione Città',
	'Architettura Per Il Progetto Sostenibile',
	'Architettura Per Il Restauro E Valorizzazione Del Patrimonio',
	'Automotive Engineering',
	'Civil Engineering',
	'Communications And Computer Networks Engineering',
	'Computer Engineering',
	'Design E Comunicazione Visiva',
	'Design Sistemico',
	'Dottorato in Ambiente E Territorio',
	'Dottorato in Architettura. Storia E Progetto',
	'Dottorato in Beni Architettonici E Paesaggistici',
	'Dottorato in Beni Culturali',
	'Dottorato in Bioingegneria E Scienze Medico-chirurgiche',
	'Dottorato in Energetica',
	'Dottorato in Fisica',
	'Dottorato in Gestione, Produzione E Design',
	'Dottorato in Ingegneria Aerospaziale',
	'Dottorato in Ingegneria Biomedica',
	'Dottorato in Ingegneria Chimica',
	'Dottorato in Ingegneria Civile E Ambientale',
	'Dottorato in Ingegneria Elettrica, Elettronica E Delle Comunicazioni',
	'Dottorato in Ingegneria Informatica E Dei Sistemi',
	'Dottorato in Ingegneria Meccanica',
	'Dottorato in Ingegneria Per La Gestione Delle Acque E Del Territorio',
	'Dottorato in Matematica Pura E Applicata',
	'Dottorato in Metrologia',
	'Dottorato in Scienza E Tecnologia Dei Materiali',
	'Dottorato in Storia Dell\'architettura E Dell\'urbanistica',
	'Dottorato in Urban And Regional Development',
	'Electronic And Communications Engineering',
	'Electronic Engineering',
	'Engineering And Management',
	'ICT For Smart Societies',
	'Ingegneria Aerospaziale',
	'Ingegneria Biomedica',
	'Ingegneria Chimica E Alimentare',
	'Ingegneria Chimica E Dei Processi Sostenibili',
	'Ingegneria Civile',
	'Ingegneria Dei Materiali',
	'Ingegneria Del Cinema E Dei Mezzi Di Comunicazione',
	'Ingegneria Dell\'autoveicolo',
	'Ingegneria Della Produzione Industriale',
	'Ingegneria Della Produzione Industriale E Dell\'innovazione Tecnologica',
	'Ingegneria Edile',
	'Ingegneria Elettrica',
	'Ingegneria Elettronica',
	'Ingegneria Energetica',
	'Ingegneria Energetica E Nucleare',
	'Ingegneria Fisica',
	'Ingegneria Gestionale',
	'Ingegneria Gestionale L-8',
	'Ingegneria Gestionale L-9',
	'Ingegneria Informatica',
	'Ingegneria Matematica',
	'Ingegneria Meccanica',
	'Ingegneria Per L\'ambiente E Il Territorio',
	'Matematica Per L\'ingegneria',
	'Mechanical Engineering',
	'Mechatronic Engineering',
	'Nanotechnologies For Icts',
	'Petroleum And Mining Engineering',
	'Physics Of Complex Systems',
	'Pianificazione Territoriale, Urbanistica E Paesaggistico-ambientale',
	'Progettazione Delle Aree Verdi E Del Paesaggio',
	'Territorial, Urban, Environmental And Landscape Planning',
];
$degreeCourses = array_combine($degreeCourses, $degreeCourses);
// ISO 3166 names
$countries = [
	'AF' => 'Afghanistan',
	'AX' => 'Åland Islands',
	'AL' => 'Albania',
	'DZ' => 'Algeria',
	'AS' => 'American Samoa',
	'AD' => 'Andorra',
	'AO' => 'Angola',
	'AI' => 'Anguilla',
	'AQ' => 'Antarctica',
	'AG' => 'Antigua and Barbuda',
	'AR' => 'Argentina',
	'AM' => 'Armenia',
	'AW' => 'Aruba',
	'AU' => 'Australia',
	'AT' => 'Austria',
	'AZ' => 'Azerbaijan',
	'BS' => 'Bahamas',
	'BH' => 'Bahrain',
	'BD' => 'Bangladesh',
	'BB' => 'Barbados',
	'BY' => 'Belarus',
	'BE' => 'Belgium',
	'BZ' => 'Belize',
	'BJ' => 'Benin',
	'BM' => 'Bermuda',
	'BT' => 'Bhutan',
	'BO' => 'Bolivia (Plurinational State of)',
	'BQ' => 'Bonaire, Sint Eustatius and Saba',
	'BA' => 'Bosnia and Herzegovina',
	'BW' => 'Botswana',
	'BV' => 'Bouvet Island',
	'BR' => 'Brazil',
	'IO' => 'British Indian Ocean Territory',
	'BN' => 'Brunei Darussalam',
	'BG' => 'Bulgaria',
	'BF' => 'Burkina Faso',
	'BI' => 'Burundi',
	'CV' => 'Cabo Verde',
	'KH' => 'Cambodia',
	'CM' => 'Cameroon',
	'CA' => 'Canada',
	'KY' => 'Cayman Islands',
	'CF' => 'Central African Republic',
	'TD' => 'Chad',
	'CL' => 'Chile',
	'CN' => 'China',
	'CX' => 'Christmas Island',
	'CC' => 'Cocos (Keeling) Islands',
	'CO' => 'Colombia',
	'KM' => 'Comoros',
	'CG' => 'Congo',
	'CD' => 'Congo, Democratic Republic of the',
	'CK' => 'Cook Islands',
	'CR' => 'Costa Rica',
	'CI' => 'Côte d\'Ivoire',
	'HR' => 'Croatia',
	'CU' => 'Cuba',
	'CW' => 'Curaçao',
	'CY' => 'Cyprus',
	'CZ' => 'Czechia',
	'DK' => 'Denmark',
	'DJ' => 'Djibouti',
	'DM' => 'Dominica',
	'DO' => 'Dominican Republic',
	'EC' => 'Ecuador',
	'EG' => 'Egypt',
	'SV' => 'El Salvador',
	'GQ' => 'Equatorial Guinea',
	'ER' => 'Eritrea',
	'EE' => 'Estonia',
	'SZ' => 'Eswatini',
	'ET' => 'Ethiopia',
	'FK' => 'Falkland Islands (Malvinas)',
	'FO' => 'Faroe Islands',
	'FJ' => 'Fiji',
	'FI' => 'Finland',
	'FR' => 'France',
	'GF' => 'French Guiana',
	'PF' => 'French Polynesia',
	'TF' => 'French Southern Territories',
	'GA' => 'Gabon',
	'GM' => 'Gambia',
	'GE' => 'Georgia',
	'DE' => 'Germany',
	'GH' => 'Ghana',
	'GI' => 'Gibraltar',
	'GR' => 'Greece',
	'GL' => 'Greenland',
	'GD' => 'Grenada',
	'GP' => 'Guadeloupe',
	'GU' => 'Guam',
	'GT' => 'Guatemala',
	'GG' => 'Guernsey',
	'GN' => 'Guinea',
	'GW' => 'Guinea-Bissau',
	'GY' => 'Guyana',
	'HT' => 'Haiti',
	'HM' => 'Heard Island and McDonald Islands',
	'VA' => 'Holy See',
	'HN' => 'Honduras',
	'HK' => 'Hong Kong',
	'HU' => 'Hungary',
	'IS' => 'Iceland',
	'IN' => 'India',
	'ID' => 'Indonesia',
	'IR' => 'Iran (Islamic Republic of)',
	'IQ' => 'Iraq',
	'IE' => 'Ireland',
	'IM' => 'Isle of Man',
	'IL' => 'Israel',
	'IT' => 'Italy',
	'JM' => 'Jamaica',
	'JP' => 'Japan',
	'JE' => 'Jersey',
	'JO' => 'Jordan',
	'KZ' => 'Kazakhstan',
	'KE' => 'Kenya',
	'KI' => 'Kiribati',
	'KP' => 'Korea (Democratic People\'s Republic of)',
	'KR' => 'Korea, Republic of',
	'KW' => 'Kuwait',
	'KG' => 'Kyrgyzstan',
	'LA' => 'Lao People\'s Democratic Republic',
	'LV' => 'Latvia',
	'LB' => 'Lebanon',
	'LS' => 'Lesotho',
	'LR' => 'Liberia',
	'LY' => 'Libya',
	'LI' => 'Liechtenstein',
	'LT' => 'Lithuania',
	'LU' => 'Luxembourg',
	'MO' => 'Macao',
	'MG' => 'Madagascar',
	'MW' => 'Malawi',
	'MY' => 'Malaysia',
	'MV' => 'Maldives',
	'ML' => 'Mali',
	'MT' => 'Malta',
	'MH' => 'Marshall Islands',
	'MQ' => 'Martinique',
	'MR' => 'Mauritania',
	'MU' => 'Mauritius',
	'YT' => 'Mayotte',
	'MX' => 'Mexico',
	'FM' => 'Micronesia (Federated States of)',
	'MD' => 'Moldova, Republic of',
	'MC' => 'Monaco',
	'MN' => 'Mongolia',
	'ME' => 'Montenegro',
	'MS' => 'Montserrat',
	'MA' => 'Morocco',
	'MZ' => 'Mozambique',
	'MM' => 'Myanmar',
	'NA' => 'Namibia',
	'NR' => 'Nauru',
	'NP' => 'Nepal',
	'NL' => 'Netherlands',
	'NC' => 'New Caledonia',
	'NZ' => 'New Zealand',
	'NI' => 'Nicaragua',
	'NE' => 'Niger',
	'NG' => 'Nigeria',
	'NU' => 'Niue',
	'NF' => 'Norfolk Island',
	'MK' => 'North Macedonia',
	'MP' => 'Northern Mariana Islands',
	'NO' => 'Norway',
	'OM' => 'Oman',
	'PK' => 'Pakistan',
	'PW' => 'Palau',
	'PS' => 'Palestine, State of',
	'PA' => 'Panama',
	'PG' => 'Papua New Guinea',
	'PY' => 'Paraguay',
	'PE' => 'Peru',
	'PH' => 'Philippines',
	'PN' => 'Pitcairn',
	'PL' => 'Poland',
	'PT' => 'Portugal',
	'PR' => 'Puerto Rico',
	'QA' => 'Qatar',
	'RE' => 'Réunion',
	'RO' => 'Romania',
	'RU' => 'Russian Federation',
	'RW' => 'Rwanda',
	'BL' => 'Saint Barthélemy',
	'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
	'KN' => 'Saint Kitts and Nevis',
	'LC' => 'Saint Lucia',
	'MF' => 'Saint Martin (French part)',
	'PM' => 'Saint Pierre and Miquelon',
	'VC' => 'Saint Vincent and the Grenadines',
	'WS' => 'Samoa',
	'SM' => 'San Marino',
	'ST' => 'Sao Tome and Principe',
	'SA' => 'Saudi Arabia',
	'SN' => 'Senegal',
	'RS' => 'Serbia',
	'SC' => 'Seychelles',
	'SL' => 'Sierra Leone',
	'SG' => 'Singapore',
	'SX' => 'Sint Maarten (Dutch part)',
	'SK' => 'Slovakia',
	'SI' => 'Slovenia',
	'SB' => 'Solomon Islands',
	'SO' => 'Somalia',
	'ZA' => 'South Africa',
	'GS' => 'South Georgia and the South Sandwich Islands',
	'SS' => 'South Sudan',
	'ES' => 'Spain',
	'LK' => 'Sri Lanka',
	'SD' => 'Sudan',
	'SR' => 'Suriname',
	'SJ' => 'Svalbard and Jan Mayen',
	'SE' => 'Sweden',
	'CH' => 'Switzerland',
	'SY' => 'Syrian Arab Republic',
	'TW' => 'Taiwan, Province of China',
	'TJ' => 'Tajikistan',
	'TZ' => 'Tanzania, United Republic of',
	'TH' => 'Thailand',
	'TL' => 'Timor-Leste',
	'TG' => 'Togo',
	'TK' => 'Tokelau',
	'TO' => 'Tonga',
	'TT' => 'Trinidad and Tobago',
	'TN' => 'Tunisia',
	'TR' => 'Turkey',
	'TM' => 'Turkmenistan',
	'TC' => 'Turks and Caicos Islands',
	'TV' => 'Tuvalu',
	'UG' => 'Uganda',
	'UA' => 'Ukraine',
	'AE' => 'United Arab Emirates',
	'GB' => 'United Kingdom of Great Britain and Northern Ireland',
	'US' => 'United States of America',
	'UM' => 'United States Minor Outlying Islands',
	'UY' => 'Uruguay',
	'UZ' => 'Uzbekistan',
	'VU' => 'Vanuatu',
	'VE' => 'Venezuela (Bolivarian Republic of)',
	'VN' => 'Viet Nam',
	'VG' => 'Virgin Islands (British)',
	'VI' => 'Virgin Islands (U.S.)',
	'WF' => 'Wallis and Futuna',
	'EH' => 'Western Sahara',
	'YE' => 'Yemen',
	'ZM' => 'Zambia',
	'ZW' => 'Zimbabwe',
];
$province = [
	'AG' => 'Agrigento (AG)',
	'AL' => 'Alessandria (AL)',
	'AN' => 'Ancona (AN)',
	'AO' => 'Aosta (AO)',
	'AP' => 'Ascoli (Piceno (AP)',
	'AQ' => 'L\'Aquila (AQ)',
	'AR' => 'Arezzo (AR)',
	'AT' => 'Asti (AT)',
	'AV' => 'Avellino (AV)',
	'BA' => 'Bari (BA)',
	'BG' => 'Bergamo (BG)',
	'BI' => 'Biella (BI)',
	'BL' => 'Belluno (BL)',
	'BN' => 'Benevento (BN)',
	'BO' => 'Bologna (BO)',
	'BR' => 'Brindisi (BR)',
	'BS' => 'Brescia (BS)',
	'BT' => 'Barletta-Andria-Trani (BT)',
	'BZ' => 'Bolzano (BZ)',
	'CA' => 'Cagliari (CA)',
	'CB' => 'Campobasso (CB)',
	'CE' => 'Caserta (CE)',
	'CH' => 'Chieti (CH)',
	'CL' => 'Caltanissetta (CL)',
	'CN' => 'Cuneo (CN)',
	'CO' => 'Como (CO)',
	'CR' => 'Cremona (CR)',
	'CS' => 'Cosenza (CS)',
	'CT' => 'Catania (CT)',
	'CZ' => 'Catanzaro (CZ)',
	'EN' => 'Enna (EN)',
	'FC' => 'Forlì-Cesena (FC)',
	'FE' => 'Ferrara (FE)',
	'FG' => 'Foggia (FG)',
	'FI' => 'Firenze (FI)',
	'FM' => 'Fermo (FM)',
	'FR' => 'Frosinone (FR)',
	'GE' => 'Genova (GE)',
	'GO' => 'Gorizia (GO)',
	'GR' => 'Grosseto (GR)',
	'IM' => 'Imperia (IM)',
	'IS' => 'Isernia (IS)',
	'KR' => 'Crotone (KR)',
	'LC' => 'Lecco (LC)',
	'LE' => 'Lecce (LE)',
	'LI' => 'Livorno (LI)',
	'LO' => 'Lodi (LO)',
	'LT' => 'Latina (LT)',
	'LU' => 'Lucca (LU)',
	'MB' => 'Monza e Brianza (MB)',
	'MC' => 'Macerata (MC)',
	'ME' => 'Messina (ME)',
	'MI' => 'Milano (MI)',
	'MN' => 'Mantova (MN)',
	'MO' => 'Modena (MO)',
	'MS' => 'Massa-Carrara (MS)',
	'MT' => 'Matera (MT)',
	'NA' => 'Napoli (NA)',
	'NO' => 'Novara (NO)',
	'NU' => 'Nuoro (NU)',
	'OR' => 'Oristano (OR)',
	'PA' => 'Palermo (PA)',
	'PC' => 'Piacenza (PC)',
	'PD' => 'Padova (PD)',
	'PE' => 'Pescara (PE)',
	'PG' => 'Perugia (PG)',
	'PI' => 'Pisa (PI)',
	'PN' => 'Pordenone (PN)',
	'PO' => 'Prato (PO)',
	'PR' => 'Parma (PR)',
	'PT' => 'Pistoia (PT)',
	'PU' => 'Pesaro e Urbino (PU)',
	'PV' => 'Pavia (PV)',
	'PZ' => 'Potenza (PZ)',
	'RA' => 'Ravenna (RA)',
	'RC' => 'Reggio Calabria (RC)',
	'RE' => 'Reggio Emilia (RE)',
	'RG' => 'Ragusa (RG)',
	'RI' => 'Rieti (RI)',
	'RM' => 'Roma (RM)',
	'RN' => 'Rimini (RN)',
	'RO' => 'Rovigo (RO)',
	'SA' => 'Salerno (SA)',
	'SI' => 'Siena (SI)',
	'SO' => 'Sondrio (SO)',
	'SP' => 'La Spezia (SP)',
	'SR' => 'Siracusa (SR)',
	'SS' => 'Sassari (SS)',
	'SU' => 'Sud Sardegna (SU)',
	'SV' => 'Savona (SV)',
	'TA' => 'Taranto (TA)',
	'TE' => 'Teramo (TE)',
	'TN' => 'Trento (TN)',
	'TO' => 'Torino (TO)',
	'TP' => 'Trapani (TP)',
	'TR' => 'Terni (TR)',
	'TS' => 'Trieste (TS)',
	'TV' => 'Treviso (TV)',
	'UD' => 'Udine (UD)',
	'VA' => 'Varese (VA)',
	'VB' => 'Verbano-Cusio-Ossola (VB)',
	'VC' => 'Vercelli (VC)',
	'VE' => 'Venezia (VE)',
	'VI' => 'Vicenza (VI)',
	'VR' => 'Verona (VR)',
	'VT' => 'Viterbo (VT)',
	'VV' => 'Vibo Valentia (VV)',
];

$error = null;

$template = Template::create();
$template->addData(['authenticated' => $loggedin, 'isAdmin' => $loggedin && Authentication::isAdmin()], 'navbar');

try {
	$ldap = new Ldap(
		CRAUTO_LDAP_URL,
		CRAUTO_LDAP_BIND_DN,
		CRAUTO_LDAP_PASSWORD,
		CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN,
		CRAUTO_LDAP_STARTTLS
	);
	if (isset($_GET['invite'])) {
		$defaultAttributes = $ldap->getInvitedUser($_GET['invite'], CRAUTO_LDAP_INVITES_DN);
		if ($defaultAttributes === null) {
			$template = Template::create();
			echo $template->render('403', ['error' => 'Invalid invite code']);
			exit;
		}
	} else {
		$template = Template::create();
		echo $template->render('403', ['error' => 'Missing invite code']);
		exit;
	}
} catch (LdapException | ValidationException $e) {
	$error = $e->getMessage();
	echo $template->render('500', ['error' => $error]);
	exit;
}

// Invite code is valid and $defaultAttributes is available, if getting here
try {
	if (isset($_POST) && !empty($_POST)) {
		Validation::handleUserRegisterPost($_POST, Validation::ALLOWED_ATTRIBUTES_REGISTER, $ldap, $degreeCourses, $countries, $province);
		$ldap->deleteInvite(CRAUTO_LDAP_INVITES_DN, $_GET['invite']);
		http_response_code(303);
		$_SESSION['register_done'] = true;
		header('Location: register_done.php');
		exit;
	}
} catch (LdapException | ValidationException $e) {
	$error = $e->getMessage();
}


if (isset($defaultAttributes['degreecourse'])) {
	if (!isset($degreeCourses[$defaultAttributes['degreecourse']])) {
		unset($defaultAttributes['degreecourse']);
	}
}

echo $template->render('registerform', ['error' => $error, 'degreeCourses' => $degreeCourses, 'countries' => $countries, 'province' => $province, 'attributes' => $defaultAttributes]);
