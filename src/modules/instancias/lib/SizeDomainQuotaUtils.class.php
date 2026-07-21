<?php

abstract class SizeDomainQuotaUtils {

	public static function calculateQuota($folderPath, &$totalquota, &$totalDirectories, &$totalSize) {
		$dp = opendir($folderPath);

		do {
			$folderName = readdir($dp);

			if (is_dir("$folderPath/$folderName") && ($folderName != '.') && ($folderName != '..') && ($folderName != '')) {
				self::calculateQuota("$folderPath/$folderName", $totalquota, $totalDirectories, $totalSize);

				$totalDirectories++;
			} else if (($folderName != '.') && ($folderName != '..') && ($folderName != '')) {
				$totalSize = ($totalSize + filesize("$folderPath/$folderName"));
				$totalquota++;
			}
		} while ($folderName != false);

		closedir($dp);
	}

	public static function sizeDomainQuota($quota) {
		self::calculateQuota('.', $totalquota, $totalDirectories, $totalSize);

		$freeA = ($totalSize / 1024 * 1024);
		$freeA = ($freeA / 1024);
		$freeA = ($freeA / 1024);

		$exp = explode('.', $freeA);
		$freeN = substr($exp[1], 0, 2);
		$freeA = $exp[0] . '.' . $freeN;

		$freeB = ($quota - $freeA);

		$datosQuote = array();
		$datosQuote['instance_size'] = $freeA;
		$datosQuote['quote_all'] = $quota;
		$datosQuote['space_free'] = $freeB;
		$datosQuote['occupied_space'] = $totalquota;
		$datosQuote['dir'] = $totalDirectories;

		return $datosQuote;
	}

}

?>
