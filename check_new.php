<?php

$magentoRoot = $argv[1] ?? null;

$de_csv = fopen('de_DE.csv', 'r');
$phrases_csv = fopen('phrases.csv', 'r');
$de_new_csv = fopen('de_DE_new.csv', 'w');

$old = array();

while (($data = fgetcsv($de_csv)) !== false) {
	$key = $data[0];
	$old[$key] = true;
}

// Also read existing module-level i18n/de_DE.csv translations from Magento installation
if ($magentoRoot) {
	$magentoRoot = rtrim($magentoRoot, '/');
	$patterns = [
		$magentoRoot . '/app/code/*/*/i18n/de_DE.csv',
		$magentoRoot . '/vendor/*/*/i18n/de_DE.csv',
		$magentoRoot . '/app/design/*/*/*/i18n/de_DE.csv',
		$magentoRoot . '/app/i18n/*/*/de_DE.csv',
	];
	foreach ($patterns as $pattern) {
		$files = glob($pattern);
		foreach ($files as $file) {
			$module_csv = fopen($file, 'r');
			if ($module_csv) {
				while (($data = fgetcsv($module_csv)) !== false) {
					$key = $data[0];
					$old[$key] = true;
				}
				fclose($module_csv);
			}
		}
	}
	echo count($old) . " existing translations found (language pack + modules)\n";
}

while (($data = fgetcsv($phrases_csv)) !== false) {
	$key = $data[0];
	if (array_key_exists($key, $old)) {
		continue;
	}
	$data[1] = '';
	fputcsv($de_new_csv, $data);
}

fclose($de_csv);
fclose($phrases_csv);
fclose($de_new_csv);
