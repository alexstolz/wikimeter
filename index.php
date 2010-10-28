<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Wikimeter - Qualitätsbestimmung von Enterprise-Wiki-Artikeln</title>
	<style type="text/css">
	<!--
	a {
		text-decoration: none;
		color: #336699;
	}
	a:hover {
		color: #6699CC;
	}
	body {
		font-size: 12px;
		font-family: Arial, Verdana, sans-serif;
		margin: 0;
		padding-bottom: 10px;
	}
	#header {
		margin: 0 auto;
		width: 600px;
		padding: 3px;
		text-align: left;
	}
	#content {
		margin: 0 auto;
		width: 600px;
		padding: 20px;
		background-color: #CCDDEE;
		border: #666 solid 1px;
		border-radius: 1.5em;
		-moz-border-radius: 1.5em;
		-webkit-border-radius: 1.5em;
	}
	#footer {
		margin: 0 auto;
		width: 600px;
		padding: 10px;
		color: #666;
		text-align: center;
	}
	#head {
		width: 600px;
		background-color: #fff;
		border-radius: 1em; -moz-border-radius: 1em; -webkit-border-radius: 1em;
		padding: 6px;
		border: #999 solid 1px;
	}
	-->
	</style>
</head>
<body>
	<div id="header">
		<a href="<?php echo $_SERVER["QUERY_STRING"] ?>">&raquo; Hauptseite</a>
	</div>
	<div id="content">
<?php
if(!$_POST) {
?>
		<form method="post" action="<?php echo $_SERVER["QUERY_STRING"] ?>">
			<table cellpadding="3" id="head" style="text-align: center;">
				<thead>
					<tr>
						<th>Geben Sie in dieses Textfeld die URI der zu überprüfenden Wiki-Seite ein</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input type="text" name="uri" size="60" /></td>
					</tr>
					<tr>
						<td>z.B. <em>http://www.u-wiki.de/wiki/index.php/Name_des_Artikels</em></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td><input type="submit" value="Anfrage verschicken" /></td>
					</tr>
				</tfoot>
			</table>
		</form>
		<div>&nbsp;</div>
<?php
}

require_once("wikimeter.php");
require_once("output.php");

// initialize class for metrics
$wikimeter = new Wikimeter();
$utils = $wikimeter->get_utils();

if($utils->get_xml_uri()) {
?>
		<div id="head" style="text-align:center;"><strong>XML URI:</strong> <a href="<?php echo $utils->get_xml_uri() ?>"><?php echo $utils->get_xml_uri() ?></a></div>
		<div>&nbsp;</div>
<?php
}

// output
$output = new Output("Lesbarkeitsmetriken");

//$output->add_bar("Flesch Reading Ease", number_format($wikimeter->calc_read_metric("flesch1"), 2), 100);
$output->add_bar("Flesch-Kincaid Grade Level", number_format($wikimeter->calc_read_metric("flesch2"), 2), 25);
$output->add_bar("Automated Readability Index", number_format($wikimeter->calc_read_metric("ari"), 2), 25);
$output->add_bar("Coleman-Liau Index", number_format($wikimeter->calc_read_metric("cli"), 2), 25);
$output->add_bar("Gunning Fog", number_format($wikimeter->calc_read_metric("fog"), 2), 25);
$output->add_bar("SMOG Grading", number_format($wikimeter->calc_read_metric("smog"), 2), 25);
$output->add_bar("MWERT (Ausreißer-bereinigt)", number_format($wikimeter->calc_condensed_metric("average_grade_readability_metrics"), 2), 25);
echo $output->draw_chart();

echo "<div>&nbsp;</div>";

$output->add_row("Flesch Reading Ease", number_format($wikimeter->calc_read_metric("flesch1"), 2));
$output->add_separator();
$output->add_row("Zeichenanzahl", $wikimeter->calc_text_metric("letter_count"));
$output->add_row("Wortanzahl", $wikimeter->calc_text_metric("word_count"));
$output->add_row("Anzahl komplexer Wörter", $wikimeter->calc_text_metric("complex_word_count"));
$output->add_row("Satzanzahl", $wikimeter->calc_text_metric("sentence_count"));
$output->add_row("Silbenanzahl", $wikimeter->calc_text_metric("syllable_count"));
$output->add_separator();
$output->add_row("Prozentanteil komplexer Wörter", number_format($wikimeter->calc_text_metric("percentage_complex_words"), 2)."%");
$output->add_row("Durchschnittliche Zeichenanzahl pro Wort", number_format($wikimeter->calc_text_metric("average_letters_per_word"), 2));
$output->add_row("Durchschnittliche Wortanzahl pro Satz", number_format($wikimeter->calc_text_metric("average_words_per_sentence"), 2));
$output->add_row("Durchschnittliche Silbenanzahl pro Wort", number_format($wikimeter->calc_text_metric("average_syllables_per_word"), 2));
$output->add_separator();
$output->add_row("Anzahl interner Links", $wikimeter->calc_struct_metric("num_int_links"));
$output->add_row("Anzahl externer Links", $wikimeter->calc_struct_metric("num_ext_links"));
$output->add_row("Anzahl der Unterabschnitte", $wikimeter->calc_struct_metric("num_subsections"));
$output->add_row("Bilderanzahl", $wikimeter->calc_struct_metric("num_images"));
$output->add_row("Linkdichte", number_format($wikimeter->calc_struct_metric("link_density"), 2)."%");
if($wikimeter->calc_struct_metric("num_subsections")>0)
$output->add_row("Verhältnis der Bildanzahl zur Unterabschnittsanzahl", number_format($wikimeter->calc_struct_metric("num_images")/$wikimeter->calc_struct_metric("num_subsections"), 2));
$output->add_separator();
$output->add_row("Artikelalter", $wikimeter->calc_meta_metric("age"));
$output->add_row("Letzte Editierung", $wikimeter->calc_meta_metric("last_edit"));
$output->add_row("Anzahl der Versionen", $wikimeter->calc_meta_metric("num_revisions"));
$output->add_row("Anzahl mitwirkender Autoren", $wikimeter->calc_meta_metric("num_authors"));

echo $output->print_table();
?>
	</div>
	<div id="footer">
		Wikimeter Prototyp - entwickelt von Alex Stolz an der Universität Innsbruck, &copy; 2010
	</div>
</body>
</html>