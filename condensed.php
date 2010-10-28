<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);

if(isset($_GET["table"])):
header("Content-Type: text/html; charset=utf-8");
else:
header("Content-Type: image/png; charset=utf-8");
endif;

require_once("wikimeter.php");
require_once("output.php");

// initialize class for metrics
$wikimeter = new Wikimeter();
// output
$output = new Output("Metriken - Abweichungen relativ zum Maximalwert");

function add($label, $value, $max) {
	global $output;
	if(isset($_GET["table"])) {
		$output->add_row($label, $value);
	}
	else {
		$output->add_bar($label, $value, $max);
	}
}

// complex words
$complex_words_dev = number_format(max(100-
	pow(abs(20-$wikimeter->calc_text_metric("percentage_complex_words")), 1.5) // distance from optimum value (diff up to 20% is ok)
	, 0), 2);
add("Komplexe Wörter", $complex_words_dev, 100);

// article length	
$word_count_dev = number_format(min(log($wikimeter->calc_text_metric("word_count"), 10)/3, 3)*100, 2); // >1000 = 100%, logarithmic approach (base 10)
add("Artikellänge", $word_count_dev, 100);

// readability indices
$readability_metrics_dev = number_format(max(100-
	pow(abs(16-$wikimeter->calc_condensed_metric("average_grade_readability_metrics")), 2) // distance from optimum value (doubled)
	, 0), 2); // 16 is optimum, for each percentage of distance reduce by factor 2 up to zero as minimum value
add("Lesbarkeitsmetriken", $readability_metrics_dev, 100);

// structural metrics
$images_per_subsection = $wikimeter->calc_struct_metric("num_images")/$wikimeter->calc_struct_metric("num_subsections");
$images_per_subsection_dev = max(100-pow(abs(100-$images_per_subsection*100), 1.5), 0);
$link_density_dev = max(100-
	pow(abs(10-$wikimeter->calc_struct_metric("link_density")), 2) // distance from optimum value (diff up to <10% is ok)
	, 0);
$structural_metrics_dev = number_format($images_per_subsection_dev*0.4+$link_density_dev*0.6, 2); // link_density has weight 0.6, images_per_subsecion 0.4
add("Struktur", $structural_metrics_dev, 100);

// lifecycle metrics
$age_dev = min(max(log($wikimeter->calc_meta_metric("age"), 10)/2.56, 0), 2.56)*100; // 100% (no deviation), if age is equal or greater than about 1 year
$last_edit_per_age_dev = max(100-$wikimeter->calc_meta_metric("last_edit")/$wikimeter->calc_meta_metric("age")*100, 0); // 0 is best
$lifecycle_dev = number_format(0.5*$age_dev+0.5*$last_edit_per_age_dev, 2); // last_edit to age has weight 0.5, age 0.5
add("Lebenszyklus", $lifecycle_dev, 100);

// reputation
$num_rev_dev = min(log($wikimeter->calc_meta_metric("num_revisions"), 10)/3, 3)*100; // >1000 = 100%, logarithmic approach (base 10)
$num_auth_dev = min(log($wikimeter->calc_meta_metric("num_authors"), 10)/2, 2)*100; // >100 = 100%, logarithmic approach (base 10)
$reputation_dev = number_format(0.8*$num_rev_dev+0.2*$num_auth_dev, 2); // we argue that in wikis the number of revisions is much more significant than the number of authors involved. hence, 0.2 for authors.
add("Reputation", $reputation_dev, 100);

add("MWERT",
	0.1*$complex_words_dev+
	0.1*$word_count_dev+
	0.2*$readability_metrics_dev+
	0.2*$structural_metrics_dev+
	0.1*$lifecycle_dev+
	0.3*$reputation_dev, 100);

// draw chart / create table
if(isset($_GET["table"])):
echo $output->print_table();
else:
echo file_get_contents($output->draw_chart(false /* without outputting img tag */));
endif;
?>