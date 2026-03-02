<?php

// Find Magento root: explicit argument > walk up from cwd > walk up from script dir
function findMagentoRoot($startDir) {
	$dir = realpath($startDir);
	for ($i = 0; $i < 8; $i++) {
		if (file_exists($dir . '/bin/magento')) {
			return $dir;
		}
		$parent = dirname($dir);
		if ($parent === $dir) break;
		$dir = $parent;
	}
	return null;
}

$magentoRoot = isset($argv[1])
	? rtrim($argv[1], '/')
	: (findMagentoRoot(getcwd()) ?? findMagentoRoot(__DIR__));

$de_csv = fopen('de_DE.csv', 'r');
$phrases_csv = fopen('phrases.csv', 'r');
$de_new_csv = fopen('de_DE_new.csv', 'w');

$old = array();

while (($data = fgetcsv($de_csv)) !== false) {
	$key = $data[0];
	$old[$key] = true;
}

// Read existing module-level i18n/de_DE.csv translations from Magento installation
if ($magentoRoot) {
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
	echo "Magento root: $magentoRoot\n";
} else {
	echo "Warning: Magento root not found. Only comparing against de_DE.csv.\n";
	echo "Tip: Run as: php check_new.php /path/to/magento2\n";
}

echo count($old) . " existing translations loaded.\n";

$newCount = 0;
while (($data = fgetcsv($phrases_csv)) !== false) {
	$key = $data[0];
	if (array_key_exists($key, $old)) {
		continue;
	}
	$data[1] = '';
	fputcsv($de_new_csv, $data);
	$newCount++;
}

fclose($de_csv);
fclose($phrases_csv);
fclose($de_new_csv);

echo "$newCount missing translations written to de_DE_new.csv\n";
