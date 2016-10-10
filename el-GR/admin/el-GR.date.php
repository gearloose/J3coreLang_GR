<?php
/**
 * @copyright      Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class el_GRDate extends JDate
{
	/**
	 * Αλλάζει την κλήση των μηνών από ονομαστική σε γενική. Δηλαδή μετατρέπει την ημερομηνία από "1 Ιανουάριος 2016" σε
	 * "1 Ιανουαρίου 2016".
	 *
	 * @param   string  $format
	 * @param   bool    $local
	 * @param   bool    $translate
	 *
	 * @return  string
	 */
	public function format($format, $local = false, $translate = true)
	{
		$return = parent::format($format, $local, $translate);

		if ((strpos($format, 'd') !== false) || (strpos($format, 'j') !== false))
		{
			$orig_months = array("άριος", "άρτιος", "ίλιος", "άιος", "ύνιος", "ύλιος", "ύγουστος", "έμβριος", "ώβριος");
			$new_months  = array("αρίου", "αρτίου", "ιλίου", "αΐου", "υνίου", "υλίου", "υγούστου", "εμβρίου", "ωβρίου");

			$return = str_replace($orig_months, $new_months, $return);
		}

		return $return;
	}

}
