<?php
/**
 * AddToTx - Προσθέτει τα υπολοιπόμενα αρχεία μετάφρασης στο Transifex
 *
 * @author		Νικόλαος Κ. Διονυσόπουλος <nicholas@akeebabackup.com>
 * @copyright	(c) 2013, Joomla.gr Μεταφραστική Ομάδα
 * @license		GNU GPL v2 or later
 */

$txproject = 'j3corelang_gr';
$root = __DIR__;

/**
 * Υπάρχει η μετάφραση ήδη στο .tx/config?
 *
 * @param   string  $key    Το κλειδί του αρχείου μετάφρασης που θα ελεγχθεί
 * @param   string  $proto  Το πρότυπο ονομασίας του αρχείου μετάφρασης (παρακάμπτει το κλειδί)
 *
 * @return  boolean Αληθές αν το κλειδί υπάρχει ήδη
 */
function does_translation_exist($key, $proto = null)
{
	global $root;

	// Προσωρινή μνήμη
	static $translations = null;
	static $file_protos = null;

	// Φόρτωση προσωρινής μνήμης από το αρχείο διαμόρφωσης του Transifex
	if (is_null($translations))
	{
		$rawData = parse_ini_file($root . '/.tx/config', true);
		$translations = array_keys($rawData);
		$file_protos = array();
		if (is_null($translations))
		{
			$translations = array();
		}
		else
		{
			foreach($rawData as $section => $data)
			{
				if (!isset($data['file_filter']))
				{
					continue;
				}
				$file_protos[] = $data['file_filter'];
			}
		}
	}

	// Υπάρχει το κλειδί;
	if (in_array($key, $translations))
	{
		return true;
	}
	// Αν όχι, υπάρχει το πρότυπο ονομασίας του αρχείου;
	elseif(!empty($proto))
	{
		return in_array($proto, $file_protos);
	}

	// Μπα, δεν υπάρχει τίποτα :(
	return false;
}

function post_tx_init()
{
	global $root;
	$rawData = parse_ini_file($root . '/.tx/config', true);

	$fixed = array();
	foreach ($rawData as $key => $data)
	{
		if ($key == 'main')
		{
			if (!isset($data['lang_map']))
			{
				$data['lang_map'] = "af_ZA: af-ZA, am_ET: am-ET, ar_AE: ar-AE, ar_BH: ar-BH, ar: ar-DZ, ar_EG: ar-EG, ar_IQ: ar-IQ, ar_JO: ar-JO, ar_KW: ar-KW, ar_LB: ar-LB, ar_LY: ar-LY, ar_MA: ar-MA, ar_OM: ar-OM, ar_QA: ar-QA, ar_SA: ar-SA, ar_SY: ar-SY, ar_TN: ar-TN, ar_YE: ar-YE, arn_CL: arn-CL, as_IN: as-IN, az_AZ: az-AZ, ba_RU: ba-RU, be_BY: be-BY, bg_BG: bg-BG, bn_BD: bn-BD, bn_IN: bn-IN, bo_CN: bo-CN, br_FR: br-FR, bs_BA: bs-BA, ca_ES: ca-ES, co_FR: co-FR, cs_CZ: cs-CZ, cy_GB: cy-GB, da_DK: da-DK, de_AT: de-AT, de_CH: de-CH, de_DE: de-DE, de_LI: de-LI, de_LU: de-LU, dsb_DE: dsb-DE, dv_MV: dv-MV, el_GR: el-GR, en_AU: en-AU, en_BZ: en-BZ, en_CA: en-CA, en_GB: en-GB, en_IE: en-IE, en_IN: en-IN, en_JM: en-JM, en_MY: en-MY, en_NZ: en-NZ, en_PH: en-PH, en_SG: en-SG, en_TT: en-TT, en_US: en-US, en_ZA: en-ZA, en_ZW: en-ZW, es_AR: es-AR, es_BO: es-BO, es_CL: es-CL, es_CO: es-CO, es_CR: es-CR, es_DO: es-DO, es_EC: es-EC, es_ES: es-ES, es_GT: es-GT, es_HN: es-HN, es_MX: es-MX, es_NI: es-NI, es_PA: es-PA, es_PE: es-PE, es_PR: es-PR, es_PY: es-PY, es_SV: es-SV, es_US: es-US, es_UY: es-UY, es_VE: es-VE, et_EE: et-EE, eu_ES: eu-ES, fa_IR: fa-IR, fi_FI: fi-FI, fil_PH: fil-PH, fo_FO: fo-FO, fr_BE: fr-BE, fr_CA: fr-CA, fr_CH: fr-CH, fr_FR: fr-FR, fr_LU: fr-LU, fr_MC: fr-MC, fy_NL: fy-NL, ga_IE: ga-IE, gd_GB: gd-GB, gl_ES: gl-ES, gsw_FR: gsw-FR, gu_IN: gu-IN, ha_NG: ha-NG, he_IL: he-IL, hi_IN: hi-IN, hr_BA: hr-BA, hr_HR: hr-HR, hsb_DE: hsb-DE, hu_HU: hu-HU, hy_AM: hy-AM, id_ID: id-ID, ig_NG: ig-NG, ii_CN: ii-CN, is_IS: is-IS, it_CH: it-CH, it_IT: it-IT, iu_CA: iu-CA, ja_JP: ja-JP, ka_GE: ka-GE, kk_KZ: kk-KZ, kl_GL: kl-GL, km_KH: km-KH, kn_IN: kn-IN, ko_KR: ko-KR, kok_IN: kok-IN, ky_KG: ky-KG, lb_LU: lb-LU, lo_LA: lo-LA, lt_LT: lt-LT, lv_LV: lv-LV, mi_NZ: mi-NZ, mk_MK: mk-MK, ml_IN: ml-IN, mn_CN: mn-CN, mn_MN: mn-MN, moh_CA: moh-CA, mr_IN: mr-IN, ms_BN: ms-BN, ms_MY: ms-MY, mt_MT: mt-MT, nb_NO: nb-NO, ne_NP: ne-NP, nl_BE: nl-BE, nl_NL: nl-NL, nn_NO: nn-NO, nso_ZA: nso-ZA, oc_FR: oc-FR, or_IN: or-IN, pa_IN: pa-IN, pl_PL: pl-PL, prs_AF: prs-AF, ps_AF: ps-AF, pt_BR: pt-BR, pt_PT: pt-PT, qut_GT: qut-GT, quz_BO: quz-BO, quz_EC: quz-EC, quz_PE: quz-PE, rm_CH: rm-CH, ro_RO: ro-RO, ru_RU: ru-RU, rw_RW: rw-RW, sa_IN: sa-IN, sah_RU: sah-RU, se_FI: se-FI, se_NO: se-NO, se_SE: se-SE, si_LK: si-LK, sk_SK: sk-SK, sl_SI: sl-SI, sma_NO: sma-NO, sma_SE: sma-SE, smj_NO: smj-NO, smj_SE: smj-SE, smn_FI: smn-FI, sms_FI: sms-FI, sq_AL: sq-AL, sr_BA: sr-BA, sr_CS: sr-CS, sr_ME: sr-ME, sr_RS: sr-RS, sv_FI: sv-FI, sv_SE: sv-SE, sw_KE: sw-KE, syr_SY: syr-SY, ta_IN: ta-IN, te_IN: te-IN, tg_TJ: tg-TJ, th_TH: th-TH, tk_TM: tk-TM, tn_ZA: tn-ZA, tr_TR: tr-TR, tt_RU: tt-RU, tzm_DZ: tzm-DZ, ug_CN: ug-CN, uk_UA: uk-UA, ur_PK: ur-PK, uz_UZ: uz-UZ, vi_VN: vi-VN, wo_SN: wo-SN, xh_ZA: xh-ZA, yo_NG: yo-NG, zh_CN: zh-CN, zh_HK: zh-HK, zh_MO: zh-MO, zh_SG: zh-SG, zh_TW: zh-TW, zu_ZA: zu-ZA";
			}
			$fixed['main'] = $data;
			continue;
		}
		else
		{
			if (!isset($data['type']))
			{
				$data['type'] = 'INI';
			}
			$fixed[$key] = $data;
		}
	}

	$out = '';
	foreach($fixed as $section => $data)
	{
		$out .= "[$section]\n";
		foreach ($data as $k => $v)
		{
			$out .= "$k=$v\n";
		}
		$out .= "\n";
	}

	file_put_contents($root . '/.tx/config', $out);
}

// Μπάνερ!
echo <<< ENDBANNER
AddToTx - Προσθέτει τα υπολοιπόμενα αρχεία μετάφρασης στο Transifex
--------------------------------------------------------------------------------
Copyright (c) 2013, Joomla.gr Μεταφραστική Ομάδα
Διανέμεται υπό τους όρους της άδειας χρήσης GNU GPL v2 ή νεότερης έκδοσης

Επεξεργασία σε εξέλιξη...

ENDBANNER;

// Αρχικοποίηση
@unlink('tx.sh');
$runstats = array(
	'processed'		=> 0,
	'new'			=> 0,
	'existing'		=> 0,
);

// Επεξεργασία των τριών περιοχών (admin, site και install)
foreach (array('admin', 'site', 'install') as $area)
{
	$areaDir = $root . '/' . $area . '/en-GB';

	// Επεξεργασία του κάθε αρχείου
	$di = new DirectoryIterator($areaDir);
	foreach ($di as $oFile)
	{
		// Αν δεν είναι αρχείο, εγκατέλειψε
		if (!$oFile->isFile())
		{
			continue;
		}

		// Πάρε το όνομα του αρχείου
		$filename = $oFile->getFilename();

		// Αν δεν είναι αρχείο INI πάμε παρακάτω
		if (substr($filename, -4) != '.ini')
		{
			continue;
		}

		// Ενημέρωση στατιστικών
		$runstats['processed']++;

		// Υπολογισμός του κλειδιού του αρχείου και του πρότυπου ονόματος
		$file_proto = basename($filename);
		$file_proto = substr($file_proto, 5);
		$slug = $area . str_replace('.', '_', $file_proto);
		$file_proto = $area . '/<lang>/<lang>' . $file_proto;
		//$file_proto = substr($file_proto, strlen($root)+1);

		// Αν δεν υπάρχει ήδη στο Trasifex, πρόσθεσέ το
		if(!does_translation_exist($txproject.'.'.$slug, $file_proto))
		{
			$runstats['new']++;
			echo "+++ $slug\n";

			$cmd = "/usr/local/bin/tx set --auto-local -t INI -r $txproject.$slug '$file_proto' --source-lang en-GB";
			$cmd .= ' --execute';

			/**/
			passthru($cmd);
			/**/

			/**
			$fp = fopen('tx.sh', 'at');
			fwrite($fp, $cmd."\n");
			fclose($fp);
			/**/
		}
		else
		{
			$runstats['existing']++;
		}
	}
}

// Αν προσθέσαμε γλώσσες τότε διόρθωσε το αρχείο .tx/config
if ($runstats['new'] > 0)
{
	post_tx_init();
}

// Γράψε τα αποτελέσματα
echo "Προστέθηκαν {$runstats['new']}\n";
echo "Υπήρχαν ήδη {$runstats['existing']}\n";
echo "Σύνολο      {$runstats['processed']}\n";