<?php

$today = new DateTime();
$prevtho = $today->modify('first day of previous month')->format('Y-m');

if ($today->format('m') === '01') {
	$prevtho = $today->modify('-1 year')->format('Y-m');
}


$atalakit	= (isset($_POST['atalakit'])	and $_POST['atalakit'] == "atalakit")	? true					: false;
$osap		= (isset($_POST['osap']) 		and !empty($_POST['osap']))				? $_POST['osap']		: false;
$tablazat	= (isset($_POST['tablazat'])	and !empty($_POST['tablazat']))			? $_POST['tablazat']	: false;

$ukod		= (isset($_POST['ukod']) 		and !empty($_POST['ukod']))				? $_POST['ukod']		: 11;

$percek		= (isset($_POST['percek'])		and !empty($_POST['percek']))			? $_POST['percek']		: 60;
$tev		= (isset($_POST['tev'])			and !empty($_POST['tev']))				? $_POST['tev']			: date('Y', strtotime($prevtho));
$tho		= (isset($_POST['tho'])			and !empty($_POST['tho']))				? $_POST['tho']			: date('m', strtotime($prevtho));

$torzs		= (isset($_POST['torzs'])		and !empty($_POST['torzs']))			? $_POST['torzs']		: '';
$sztorzs	= (isset($_POST['torzs'])		and !empty($_POST['sztorzs']))			? $_POST['sztorzs']		: $torzs;

$jnev		= (isset($_POST['jnev'])		and !empty($_POST['jnev']))				? $_POST['jnev']		: '';
$jbeosztas	= (isset($_POST['jbeosztas'])	and !empty($_POST['jbeosztas']))		? $_POST['jbeosztas']	: '';
$jtelefon	= (isset($_POST['jtelefon'])	and !empty($_POST['jtelefon']))			? $_POST['jtelefon']	: '';
$jemail		= (isset($_POST['jemail'])		and !empty($_POST['jemail']))			? $_POST['jemail']		: '';

$knev		= (isset($_POST['knev'])		and !empty($_POST['knev']))				? $_POST['knev']		: '';
$kbeosztas	= (isset($_POST['kbeosztas'])	and !empty($_POST['kbeosztas']))		? $_POST['kbeosztas']	: '';
$ktelefon	= (isset($_POST['ktelefon'])	and !empty($_POST['ktelefon']))			? $_POST['ktelefon']	: '';
$kemail		= (isset($_POST['kemail'])		and !empty($_POST['kemail']))			? $_POST['kemail']		: '';

$megjegyzes	= (isset($_POST['megjegyzes'])	and !empty($_POST['megjegyzes']))		? $_POST['megjegyzes']	: '';

$formatum	= false;
$hiba		= false;
$fej		= false;
$html		= true;
$csv		= false;



if ($atalakit) {

	if (isset($_POST['tablazat'])) {
		$tablazat = $_POST['tablazat'];

		// Tabulátorral tagolt szöveg felbontása sorokra és cellákra
		$rows = explode("\n", $tablazat);
		$data = array();

		// A sorokat oszlopokra bontjuk, és eltároljuk az oszlopok számát
		$numColumns = 0;
		foreach ($rows as $row) {
			$cell = explode("\t", $row);
			$numColumns = max($numColumns, count($cell));
			$data[] = $cell;
		}

		$rows=$data;
		// Korrigáljuk a számoszlopokat, hogy a megfelelő formátumúak legyenek
		for ($i = 0; $i < count($rows); $i++) {
			if (isset($rows[$i][5])) {
				$rows[$i][5] = preg_replace('/[^0-9,.]/', '', $rows[$i][5]);
			} else {
				$rows[$i][5] = '';
			}
		
			if (isset($rows[$i][6])) {
				$rows[$i][6] = preg_replace('/[^0-9,.]/', '', $rows[$i][6]);
			} else {
				$rows[$i][6] = '';
			}
		
			if (isset($rows[$i][7])) {
				$rows[$i][7] = preg_replace('/[^0-9,.]/', '', preg_replace('/[\n\r]/', '', $rows[$i][7]));
			} else {
				$rows[$i][7] = '';
			}
		
			if (isset($rows[$i][8])) {
				$rows[$i][8] = preg_replace('/[\n\r]/', '', $rows[$i][8]);
			} else {
				$rows[$i][8] = '';
			}
		}
		$data=$rows;


		/**
		 *  Az OSAP 2012 sablon esetén, ha az alábbi mezkők értékei megegyeznek, más sorokban is:
		 * 	[0] Harmonizációs kód
		 * 	[3] Feladó tagállam / Rendeltetési tagállam
		 * 	[4] Származási ország
		 * 
		 * Akkor összeadjuk a számos mezők értékeit, a többinél az 1. sort vesszük alapul:
		 * 	[5] Összes nettó tömeg (kg)
		 * 	[6] Összes mennyiség (db)
		 * 	[7] Számlázott összeg (Ft)
		 * 
		 */

		if($osap == 2012) {
			$rows=$data;
			$mergedRows = [];
			$processedIndices = [];
			
			for ($i = 0; $i < count($rows); $i++) {
				if (!in_array($i, $processedIndices)) {
					$currentRow = $rows[$i];
					$mergedRow = $currentRow;
					$isValid = true;
			
					for ($j = $i + 1; $j < count($rows); $j++) {
						if (!in_array($j, $processedIndices)) {
							$compareRow = $rows[$j];
							if ($currentRow[0] === $compareRow[0] && $currentRow[3] === $compareRow[3] && $currentRow[4] === $compareRow[4]) {
								if (isset($compareRow[5]) && is_numeric($compareRow[5])) {
									$mergedRow[5] += $compareRow[5];
								} else {
									$isValid = false;
									break;
								}
								if (isset($compareRow[6]) && is_numeric($compareRow[6])) {
									$mergedRow[6] += $compareRow[6];
								} else {
									$isValid = false;
									break;
								}
								if (isset($compareRow[7]) && is_numeric($compareRow[7])) {
									$mergedRow[7] += $compareRow[7];
								} else {
									$isValid = false;
									break;
								}
								$processedIndices[] = $j;
							}
						}
					}
			
					if ($isValid) {
						$mergedRows[] = $mergedRow;
						$processedIndices[] = $i;
					}
				}
			}

			$data=$mergedRows;
		}
		
		/**
		 *  Az OSAP 2010 sablon esetén, ha az alábbi mezkők értékei megegyeznek, más sorokban is:
		 * 	[0] Harmonizációs kód
		 * 	[3] Feladó tagállam / Rendeltetési tagállam
		 * 	[4] Származási ország
		 *  [8] Partner adószáma
		 * 
		 * Akkor összeadjuk a számos mezők értékeit, a többinél az 1. sort vesszük alapul:
		 * 	[5] Összes nettó tömeg (kg)
		 * 	[6] Összes mennyiség (db)
		 * 	[7] Számlázott összeg (Ft)
		 * 
		 */

		 if($osap == 2010) {
			$rows=$data;
			$mergedRows = [];
			$processedIndices = [];
			
			for ($i = 0; $i < count($rows); $i++) {
				if (!in_array($i, $processedIndices)) {
					$currentRow = $rows[$i];
					$mergedRow = $currentRow;
					$isValid = true;
			
					for ($j = $i + 1; $j < count($rows); $j++) {
						if (!in_array($j, $processedIndices)) {
							$compareRow = $rows[$j];
							if ($currentRow[0] === $compareRow[0] && $currentRow[3] === $compareRow[3] && $currentRow[4] === $compareRow[4] && $currentRow[8] === $compareRow[8]) {
								if (isset($compareRow[5]) && is_numeric($compareRow[5])) {
									$mergedRow[5] += $compareRow[5];
								} else {
									$isValid = false;
									break;
								}
								if (isset($compareRow[6]) && is_numeric($compareRow[6])) {
									$mergedRow[6] += $compareRow[6];
								} else {
									$isValid = false;
									break;
								}
								if (isset($compareRow[7]) && is_numeric($compareRow[7])) {
									$mergedRow[7] += $compareRow[7];
								} else {
									$isValid = false;
									break;
								}
								$processedIndices[] = $j;
							}
						}
					}
			
					if ($isValid) {
						$mergedRows[] = $mergedRow;
						$processedIndices[] = $i;
					}
				}
			}

			$data=$mergedRows;
		}

		/**
		 * OSAP 2010 - Export / Kiszállítás : 9 feldolgozandó oszlop
		 * 
		 * # 2010 INTRASTAT Kiszállítás CSV SABLON [INTRASTAT Dispatches, CSV file];;;;;;;;;;;;;;
		 * #----------------------------------------------------------------;;;;;;;;;;;;;;
		 * #FEJEZET(karakter): Fejezet azonosító, 0 - Elõlap [Chapter ID, 0 - Preface];;;;;;;;;;;;;;
		 * #SORREND(numerikus 3 karakter): Elõlap kötelezõen 1 [Numeric, 3 characters, must be 1];;;;;;;;;;;;;;
		 * #MC01 (4 karakter): OSAP száma - kötelezõen 2010 [OSAP number, must be 2010];;;;;;;;;;;;;;
		 * #M003_G (8 karakter): Gazdasági szervezet törzsszáma [tax ID number of organization];;;;;;;;;;;;;;
		 * #M003 (8 karakter): Szakosodott egység törzsszáma (ha nincs, akkor a gazdasági szervezet törzsszáma) [Specialized unit, or tax ID nr];;;;;;;;;;;;;;
		 * #MEV (2 karakter): Tárgyév két hosszan (23) [tev in two characters];;;;;;;;;;;;;;
		 * #MHO (2 karakter): Tárgyhó két hosszan (01, 02, .., 12) [tho must be two characters WITH LEADING ZERO];;;;;;;;;;;;;;
		 * #JHNEV: A kérdõívet jóváhagyó vezetõ neve [Name of executive];;;;;;;;;;;;;;
		 * #JBEOSZTAS: A kérdõívet jóváhagyó vezetõ beosztása [Status of executive];;;;;;;;;;;;;;
		 * #JTEL: A kérdõívet jóváhagyó vezetõ telefonszáma [Phone of executive];;;;;;;;;;;;;;
		 * #JEMAIL: A kérdõívet jóváhagyó vezetõ e-mail címe [E-mail of executive];;;;;;;;;;;;;;
		 * #KNEV: A kérdõívet kitöltõ neve [Name of contact person];;;;;;;;;;;;;;
		 * #KBEOSZTAS: A kérdõívet kitöltõ beosztása [Status of contact person];;;;;;;;;;;;;;
		 * #KTEL: A kérdõívet kitöltõ telefonszáma [Phone of contact person];;;;;;;;;;;;;;
		 * #KEMAIL: A kérdõívet kitöltõ e-mail címe [E-mail of contact person];;;;;;;;;;;;;;
		 * #MEGJEGYZES(max. 500 karakter): [Comment, max. 500 characters];;;;;;;;;;;;;;
		 * #VGEA002(numerikus max. 5 karakter): Kérdõív kitöltésére fordított idõ percekben;;;;;;;;;;;;;;
		 * # [Numeric, max. 5 characters, time spent filling in the questionnaire in minutes];;;;;;;;;;;;;;
		 * #----------------------------------------------------------------;;;;;;;;;;;;;;
		 * #FEJEZET(karakter): Fejezet azonosító, 1 – Kiszállítás [Chapter ID, 1 - Dispatches];;;;;;;;;;;;;;
		 * #SORREND(numerikus 3 karakter): Kiszállítás fejezet ismétlõdés sorrendje, 1-gyel kezdõdik [Repeat order nr of Dispatches chapter];;;;;;;;;;;;;;
		 * #T_SORSZ(5 karakter): Tétel sorszáma VEZETÕ NULLÁKKAL[Serial number WITH LEADING ZEROES];;;;;;;;;;;;;;
		 * #TEKOD(8 karakter): Termék kódja [Commodity code];;;;;;;;;;;;;;
		 * #UKOD(2 karakter): Ügyletkód [Nature of transaction];;;;;;;;;;;;;;
		 * #RTA(2 karakter): Rendeltetési tagállam [Member State of destination];;;;;;;;;;;;;;
		 * #SZAORSZ(2 karakter): Származási ország [Country of origin];;;;;;;;;;;;;;
		 * #KGM(numerikus(14,3)karakter): Nettó tömeg(kg), 1 kg alatt 3 tizedesjegyig kell megadni tizedesponttal, 1 kg felett egész kg-ra kell kerekíteni;;;;;;;;;;;;;;
		 * # [Quantity in net mass(kg) is to be declared with three decimals (e.g.0.003); above 1 kg it is to be rounded to kgs];;;;;;;;;;;;;
		 * #KIEGME(numerikus(14,3)karakter): Mennyiség kiegészítõ mértékegységben. A KN-ben megjelölt termékekre kötelezõ ;;;;;;;;;;;;;;
		 * # [Quantity in supplementary units. Only where a supplementary unit is specified to the commoditycode in the CN.];;;;;;;;;;;;;;
		 * #SZAOSSZ(numerikus 14 karakter): Számlázott összeg Forintban [Invoiced amount (HUF)];;;;;;;;;;;;;;
		 * #STAERT(numerikus 14 karakter): Statisztikai érték Forintban [Statistical value (HUF)];;;;;;;;;;;;;;
		 * #PADO(nax. 40 karakter): Partner adószám [Partner tax ID number];;;;;;;;;;;;;;
		 * #----------------------------------------------------------------;;;;;;;;;;;;;;
		 * 
		 * 
		 *	[0] = Harmonizációs kód
		 *	[1] = Termék megnevezése
		 *	[2] = db/kg
		 *	[3] = Rendeltetési tagállam
		 *	[4] = Származási ország
		 *	[5] = Összes nettó tömeg(kg)
		 *	[6] = Összes mennyiség db
		 *	[7] = Számlázott összeg (ft)
		 *	[8] = Partner adószáma
		 *
		 * 
		 * 
		 * OSAP 2012 - Import / Beérkezés : 8 feldolgozandó oszlop
		 * 
		 * # 2012 INTRASTAT Beérkezés CSV SABLON [INTRASTAT Arrivals, CSV file];;;;;;;;;;;;;;
		 * #----------------------------------------------------------------;;;;;;;;;;;;;;
		 * #FEJEZET(karakter): Fejezet azonosító, 0 - Elõlap [Chapter ID, 0 - Preface];;;;;;;;;;;;;;
		 * #SORREND(numerikus 3 karakter): Elõlap 1 [Numeric, 3 characters, Preface 1];;;;;;;;;;;;;;
		 * #MC01 (4 karakter): OSAP száma - kötelezõen 2012 [OSAP number, must be 2012];;;;;;;;;;;;;;
		 * #M003_G (8 karakter): Gazdasági szervezet törzsszáma [Tax ID number of organization];;;;;;;;;;;;;;
		 * #M003 (8 karakter): Szakosodott egység törzsszáma (ha nincs, akkor a gazdasági szervezet törzsszáma) [ID of specialized unit, or tax ID nr];;;;;;;;;;;;;;
		 * #MEV (2 karakter): Tárgyév két hosszan (23) [tev in two characters];;;;;;;;;;;;;;
		 * #MHO (2 karakter): Tárgyhó két hosszan (01, 02, .., 12) [tho in two characters WITH LEADING ZERO];;;;;;;;;;;;;;
		 * #JHNEV: A kérdõívet jóváhagyó vezetõ neve [Name of executive];;;;;;;;;;;;;;
		 * #JBEOSZTAS: A kérdõívet jóváhagyó vezetõ beosztása [Status of executive];;;;;;;;;;;;;;
		 * #JTEL: A kérdõívet jóváhagyó vezetõ telefonszáma [Phone of executive];;;;;;;;;;;;;;
		 * #JEMAIL: A kérdõívet jóváhagyó vezetõ e-mail címe [E-mail of executive];;;;;;;;;;;;;;
		 * #KNEV: A kérdõívet kitöltõ neve [Name of contact person];;;;;;;;;;;;;;
		 * #KBEOSZTAS: A kérdõívet kitöltõ beosztása [Status of contact person];;;;;;;;;;;;;;
		 * #KTEL: A kérdõívet kitöltõ telefonszáma [Phone of contact person];;;;;;;;;;;;;;
		 * #KEMAIL: A kérdõívet kitöltõ e-mail címe [E-mail of contact person];;;;;;;;;;;;;;
		 * #MEGJEGYZES(max. 500 karakter): [Comment, max. 500 characters];;;;;;;;;;;;;;
		 * #VGEA002(numerikus max. 5 karakter): Kérdõív kitöltésére fordított idõ percekben;;;;;;;;;;;;;;
		 * # [Numeric, max. 5 characters, time spent filling in the questionnaire in minutes];;;;;;;;;;;;;;
		 * #----------------------------------------------------------------;;;;;;;;;;;;;;
		 * #FEJEZET(karakter): Fejezet azonosító, 1 – Beérkezés [Chapter ID, 1 - Arrivals];;;;;;;;;;;;;;
		 * #SORREND(numerikus 3 karakter): Beérkezés fejezet ismétlõdés sorrendje, 1-gyel kezdõdik [Repeat order nr of Arrivals chapter ];;;;;;;;;;;;;;
		 * #T_SORSZ(5 karakter): Tétel sorszáma VEZETÕ NULLÁKKAL[Serial number WITH LEADING ZEROES];;;;;;;;;;;;;;
		 * #TEKOD(8 karakter): Termék kódja [Commodity code];;;;;;;;;;;;;;
		 * #UKOD(2 karakter): Ügyletkód [Nature of transaction];;;;;;;;;;;;;;
		 * #FTA(2 karakter): Feladó tagállam [Member state of consignment];;;;;;;;;;;;;;
		 * #SZAORSZ(2 karakter): Származási ország [Country of origin];;;;;;;;;;;;;;
		 * #KGM(numerikus(14,3)karakter): Nettó tömeg(kg), 1 kg alatt 3 tizedesjegyig kell megadni tizedesponttal, felette egész kg-ra kell kerekíteni;;;;;;;;;;;;;;
		 * # [Quantity in net mass(kg) is to be declared with three decimals (e.g.0.003); above 1 kg it is to be rounded to kgs];;;;;;;;;;;;;
		 * #KIEGME(numerikus(14,3)karakter): Mennyiség kiegészítõ mértékegységben. A KN-ben megjelölt termékekre kötelezõ ;;;;;;;;;;;;;;
		 * # [Quantity in supplementary units. Only where a supplementary unit is specified to the commoditycode in the CN.];;;;;;;;;;;;;;
		 * #SZAOSSZ(numerikus 14 karakter): Számlázott összeg Forintban [Invoiced amount (HUF)];;;;;;;;;;;;;;
		 * #STAERT(numerikus 14 karakter): Statisztikai érték Forintban [Statistical value (HUF)];;;;;;;;;;;;;;
		 * #----------------------------------------------------------------;;;;;;;;;;;;;;
		 * 
		 * 
		 *	[0] = Harmonizációs kód
		 *	[1] = Termék megnevezése
		 *	[2] = db/kg
		 *	[3] = Feladó tagállam
		 *	[4] = Származási ország
		 *	[5] = Összes nettó tömeg(kg)
		 *	[6] = Összes mennyiség db
		 *	[7] = Számlázott összeg (ft)
		 * 
		 */
		if ($osap == 2010 and $numColumns = 9) {
			$formatum = true;
			$fej = '{T_SORSZ;TEKOD;UKOD;RTA;SZAORSZ;KGM;KIEGME;SZAOSSZ;STAERT;PADO};;;;;';
		} else if ($osap == 2012 and $numColumns = 8) {
			$formatum = true;
			$fej = '{T_SORSZ;TEKOD;UKOD;FTA;SZAORSZ;KGM;KIEGME;SZAOSSZ;STAERT};;;;;;';
		} else {
			$hiba .= 'Nem megfelelő oszlopszám (' . $numColumns . ') a bemeneten. ';
		}

		if ($formatum) {


			$csv .= ';;;;;;;;;;;;;;' . chr(10);
			$csv .= '{fejezet;sorrend};;;;;;;;;;;;;' . chr(10);
			$csv .= '0;1;;;;;;;;;;;;;' . chr(10);
			$csv .= ';;;;;;;;;;;;;;' . chr(10);
			$csv .= '{MC01;M003_G;M003;MEV;MHO;JHNEV;JBEOSZTAS;JTELEFON;JEMAIL;KNEV;KBEOSZTAS;KTELEFON;KEMAIL;MEGJEGYZES;VGEA002}' . chr(10);
			$csv .= $osap . ';' . $torzs . ';' . $sztorzs . ';' . $tev . ';' . $tho . ';' . $jnev . ';' . $jbeosztas . ';' . $jtelefon . ';' . $jemail . ';' . $knev . ';' . $kbeosztas . ';' . $ktelefon . ';' . $kemail . ';' . $megjegyzes . ';' . $percek . chr(10);



			// Sorok
			$sor = 1;
			$sorrend = 1;
			foreach ($data as $row) {
				if ($sor == 1) {
					$csv .= ';;;;;;;;;;;;;;' . chr(10);
					$csv .= '{fejezet;sorrend};;;;;;;;;;;;;' . chr(10);
					$csv .= '1;' . $sorrend . ';;;;;;;;;;;;;' . chr(10);
					$csv .= ';;;;;;;;;;;;;;'  . chr(10);
					$csv .= $fej . chr(10);
				}

				$TEKOD		= isset($row[0]) ? $row[0] : '';
				$xTA		= isset($row[3]) ? $row[3] : '';
				$SZAORSZ	= isset($row[4]) ? $row[4] : '';
				$KGM		= isset($row[5]) ? preg_replace('/[^0-9,.]/', '', $row[5]) : '';
				$KIEGM		= isset($row[6]) ? preg_replace('/[^0-9,.]/', '', $row[6]) : '';
				$SZAOSSZ	= isset($row[7]) ? preg_replace('/[^0-9,.]/', '', preg_replace('/[\n\r]/', '', $row[7])) : false;
				$STAERT		= '';
				$PADO		= isset($row[8]) ? preg_replace('/[\n\r]/', '', $row[8]) : '';

				if ($SZAOSSZ) {
					$csv .=  $sor . ';' . $TEKOD . ';' . $ukod . ';' . $xTA . ';' . $SZAORSZ . ';' . $KGM . ';' . $KIEGM . ';' . $SZAOSSZ . ';' . $STAERT . ';' . $PADO . ';;;;;' . chr(10);
					$sor++;
				}
				if ($sor == 26) {
					$sor = 1;
					$sorrend++;
				}
			}
		}
	}
}

if ($csv) {


	switch ($osap) {
		default:
			$osap_name = '';
			break;
		case 2010:
			$osap_name = 'kivitel';
			break;
		case 2012:
			$osap_name = 'behozatal';
			break;
	}

	$filename	= 'intrastat-' . $tev . '-' . $tho . '-' . $osap_name . '-' . date('YmdHis');

	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename='.$filename.'.csv');
	echo $csv;
	//echo "\xEF\xBB\xBF"; // UTF-8 BOM

} else {
	header('Content-Type: text/html; charset=utf-8');


?>
	<!doctype html>
	<html class="no-js" lang="hu">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow, noarchive">
		<title>Intrastat OSAP 2010/2012 CSV átalaktíó</title>
		<meta name="description" content="Táblázatból bemásolt adatokat alakít át CSV fájlá az intrastat (OSAP 2010 és 2012) kérdőív kitöltéséhez">

		<style>
			:root {
				--radius: 10px;
				--width: 600px;
				--padding: 25px;

				--color-light-gray: #dfdfdf;
				--color-gray: #828282;
				--color-green: #34a347;
				--color-red: #c22b2b;
				--color-blue: #1f5887;

				background-color: white;
				font-family: monospace;
				color: black;
				font-size: 12px;
				line-height: 1.5rem;
			}

			form {

				max-width: var(--width);
				/*width: 100%;*/
				margin: 50px auto 10px auto;
				padding: var(--padding);
				background-color: white;

				border-radius: var(--radius);

				box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.2);
			}

			fieldset {
				border: 1px solid var(--color-light-gray);
				text-align: center;
				border-radius: var(--radius);
			}

			.row {
				overflow: auto;
				margin-top: 10px;
				margin-bottom: 20px;
				clear: both;
			}

			.floating fieldset {
				float: left;
				width: calc(50% - var(--padding) - 10px);
			}

			.floating fieldset:first-of-type {
				margin-right: 20px;
				;
			}

			legend {
				color: var(--color-gray);
			}

			input,
			textarea {
				outline: none;
				box-sizing: border-box;
				width: 100%;
				margin-bottom: 10px;

				text-align: center;
			}

			input[readonly] {
				background-color: var(--color-light-gray);
				color: var(--color-gray);
				border: none;
			}


			input[type="radio"] {
				opacity: 0;
				position: fixed;
				width: 0;
			}

			label {
				margin: 20px 0 0 var(--radius);
			}

			label[for^="osap"] {
				display: inline-block;
				background-color: white;
				padding: 10px;
				margin: 20px;
				border: 1px solid var(--color-gray);
				border-radius: var(--radius);
			}

			label[for^="osap"]:first-of-type {
				margin-left: 0px;
			}

			input[type="radio"]:hover+label {
				background-color: white;
				border-color: black;
			}

			input[type="radio"]:checked+label {
				background-color: var(--color-blue);
				border-color: var(--color-blue);
				color: white;
			}

			input[type="text"],
			input[type="number"] {
				border: 1px solid var(--color-gray);
				border-radius: var(--radius);
				color: white;
				font-family: monospace;
				color: black;
				padding: 10px;
				display: block;
			}

			textarea {
				border: 1px solid var(--color-gray);
				border-radius: var(--radius);
				color: white;
				font-family: monospace;
				text-align: left;
				color: black;
				height: 100px;
				padding: 10px;
				font-size: 5px;
			}

			input:focus,
			textarea:focus {
				border-color: var(--color-blue);
			}

			.inline-block div {
				display: inline-block;
				vertical-align: middle;
				margin-right: 10px;
			}


			button {
				margin: 20px 10px 10px 0px;
				padding: 10px;
				border-radius: var(--radius);
				background-color: white;

				/*display: inline-block;*/
				vertical-align: middle;
				float: right;
			}

			button[type="submit"] {
				border: 1px solid var(--color-green);
				color: var(--color-green);
			}

			button[type="submit"]:hover,
			button[type="submit"]:focus {
				background-color: var(--color-green);
				color: white;
			}

			button[type="reset"] {
				border: 1px solid var(--color-red);
				color: var(--color-red);
			}

			button[type="reset"]:hover,
			button[type="reset"]:focus {
				background-color: var(--color-red);
				color: white;
			}

			footer {
				display: flex;
				align-items: center;
				justify-content: center;
				text-align: center;
				margin-top: 20px;
				margin-bottom: 3rem;
				color: black;
				font-size: 1em;
			}

			footer svg {
				margin-left: 10px;
				margin-right: 5px;
				opacity: 0.5;
			}

			footer a {
				display: flex;
				align-items: center;
				color: black;
				text-decoration: none;
				opacity: 0.5;
			}

			footer a:hover {
				opacity: 1;
			}

		</style>
	</head>


	<body>



		<form method="post" enctype="application/x-www-form-urlencoded" action="index.php">

			<?php if ($hiba) {
				echo '<p class="hiba">' . $hiba . '</p>';
			} ?>

			<div class="row">
				<fieldset id="osap-kerdoiv">
					<legend><abbr title="Országos Statisztikai Adatfelvételi Program">OSAP</abbr> kérdőív</legend>

					<input type="radio" id="osap2010" name="osap" value="2010" required>
					<label for="osap2010">Kiszállítás / export (<abbr title="Országos Statisztikai Adatfelvételi Program">OSAP</abbr> 2010)</label>

					<input type="radio" id="osap2012" name="osap" value="2012" required>
					<label for="osap2012">Beérkezés / import (<abbr title="Országos Statisztikai Adatfelvételi Program">OSAP</abbr> 2012)</label>

				</fieldset>
			</div>

			<div class="row floating">

				<fieldset id="torzsszam" class="left">
					<legend>Törzsszám</legend>

					<lable>Gazdasági szervezet törzsszáma</lable>
					<input type="text" name="torzs" value="<?php echo $torzs; ?>" required>

					<lable>Szakosodott egység törzsszáma</lable>
					<input type="text" name="sztorzs" value="<?php echo $sztorzs; ?>" required>
				</fieldset>

				<fieldset id="idoszak" class="left">
					<legend>Időszak</legend>

					<lable>Év</lable>
					<input type="text" name="tev" value="<?php echo $tev; ?>" required>

					<lable>Hónap</lable>
					<input type="text" name="tho" value="<?php echo $tho; ?>" required>
				</fieldset>
			</div>

			<div class="row floating">

				<fieldset id="jovahagyo" class="left">
					<legend>Jóváhagyó</legend>

					<lable>Név</lable>
					<input type="text" name="jnev" value="<?php echo $jnev; ?>" required readonly>

					<lable>Beosztás</lable>
					<input type="text" name="jbeosztas" value="<?php echo $jbeosztas; ?>" required readonly>

					<lable>E-mail cím</lable>
					<input type="text" name="jemail" value="<?php echo $jemail; ?>" required readonly>

					<lable>Telefonszám</lable>
					<input type="text" name="jtelefon" value="<?php echo $jtelefon; ?>" required readonly>
				</fieldset>

				<fieldset id="kitolto" class="left">
					<legend>Kitöltő</legend>

					<lable>Név</lable>
					<input type="text" name="knev" value="<?php echo $knev; ?>" required>

					<lable>Beosztás</lable>
					<input type="text" name="kbeosztas" value="<?php echo $kbeosztas; ?>" required>

					<lable>E-mail cím</lable>
					<input type="text" name="kemail" value="<?php echo $kemail; ?>" required>

					<lable>Telefonszám</lable>
					<input type="text" name="ktelefon" value="<?php echo $ktelefon; ?>" required>
				</fieldset>
			</div>

			<div class="row floating">
				<label>Táblázat</label>
				<textarea name="tablazat" required><?php echo $tablazat; ?></textarea>
			</div>

			<div class="row floating">

				<label>Megjegyzés</label>
				<input type="text" name="megjegyzes" value="<?php echo $megjegyzes; ?>">
			</div>
			<div class="row inline-block">

				<div>
					<label>A kitöltés ideje</label>
					<input type="number" name="percek" value="<?php echo $percek; ?>">
				</div>

				<button type="submit" name="atalakit" value="atalakit">CSV fájl készítése</button>
				<button type="reset">Űrlap adatainak törlése</button>
			</div>
		</form>
		<footer>
			<svg aria-hidden="true" height="16" viewBox="0 0 16 16" version="1.1" width="16" data-view-component="true" class="octicon octicon-mark-github">
    			<path d="M8 0c4.42 0 8 3.58 8 8a8.013 8.013 0 0 1-5.45 7.59c-.4.08-.55-.17-.55-.38 0-.27.01-1.13.01-2.2 0-.75-.25-1.23-.54-1.48 1.78-.2 3.65-.88 3.65-3.95 0-.88-.31-1.59-.82-2.15.08-.2.36-1.02-.08-2.12 0 0-.67-.22-2.2.82-.64-.18-1.32-.27-2-.27-.68 0-1.36.09-2 .27-1.53-1.03-2.2-.82-2.2-.82-.44 1.1-.16 1.92-.08 2.12-.51.56-.82 1.28-.82 2.15 0 3.06 1.86 3.75 3.64 3.95-.23.2-.44.55-.51 1.07-.46.21-1.61.55-2.33-.66-.15-.24-.6-.83-1.23-.82-.67.01-.27.38.01.53.34.19.73.9.82 1.13.16.45.68 1.31 2.69.94 0 .67.01 1.3.01 1.49 0 .21-.15.45-.55.38A7.995 7.995 0 0 1 0 8c0-4.42 3.58-8 8-8Z"></path>
			</svg>
			<a href="https://github.com/borbasmatyas" target="_blank">@borbasmatyas</a> 
 			<svg aria-hidden="true" height="16" viewBox="0 0 16 16" version="1.1" width="16" data-view-component="true" class="octicon octicon-repo color-fg-muted mr-2">
    			<path d="M2 2.5A2.5 2.5 0 0 1 4.5 0h8.75a.75.75 0 0 1 .75.75v12.5a.75.75 0 0 1-.75.75h-2.5a.75.75 0 0 1 0-1.5h1.75v-2h-8a1 1 0 0 0-.714 1.7.75.75 0 1 1-1.072 1.05A2.495 2.495 0 0 1 2 11.5Zm10.5-1h-8a1 1 0 0 0-1 1v6.708A2.486 2.486 0 0 1 4.5 9h8ZM5 12.25a.25.25 0 0 1 .25-.25h3.5a.25.25 0 0 1 .25.25v3.25a.25.25 0 0 1-.4.2l-1.45-1.087a.249.249 0 0 0-.3 0L5.4 15.7a.25.25 0 0 1-.4-.2Z"></path>
			</svg>
			<a href="https://github.com/fotoplus/intrastat-converter" target="_blank">fotoplus/intrastat-converter</a>
		</footer>
	</body>

	</html>
<?php

}

?>
