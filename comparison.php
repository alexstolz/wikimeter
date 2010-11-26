<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
ini_set('max_execution_time', 0);

// header("Content-Type: text/html; charset=utf-8");
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
	h1 {
		font-size: 16px;
	}
	th {
		background-color: white;
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
		width: 700px;
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
	<h1>Veränderung der Gesamtmetrik über den Lebenszyklus des Artikels hinweg</h1>
</div>
<div id="content">
<?php
require_once("wikimeter.php");
require_once("output.php");

// initialize class for metrics
$wikimeter = new Wikimeter();

$chart = "http://chart.apis.google.com/chart?cht=lc&chs=700x420&chm=B,EEEEEE,0,0,0&chxt=x,y,x&chtt=Entwicklung+des+Gesamtindex+%C3%BCber+die+Versionshistorie&chds=0,1000&chxl=0:|0|".$wikimeter->get_num_revisions()."|1:|0|50|100|2:||Versionen|&chd=t:";
echo "<table style=\"width:100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\"><tr><th>weighted metric</th><th>% complex words</th><th>wc</th><th>link density</th><th>num revs</th><th>article age</th><th>num authors</th></tr>";
for($i=1; $i<=$wikimeter->get_num_revisions(); $i++) {
	$wikimeter->updateRevNum($i);
	// complex words
	$complex_words_dev = number_format(max(100-
		pow(abs(20-$wikimeter->calc_text_metric("percentage_complex_words")), 1.5) // distance from optimum value (diff up to 20% is ok)
		, 0), 2);
		
	// article length	
	$word_count_dev = number_format(min(log($wikimeter->calc_text_metric("word_count"), 10)/3, 3)*100, 2); // >1000 = 100%, logarithmic approach (base 10)
	
	// readability indices
	$readability_metrics_dev = number_format(max(100-
		pow(abs(16-$wikimeter->calc_condensed_metric("average_grade_readability_metrics")), 2) // distance from optimum value (doubled)
		, 0), 2); // 16 is optimum, for each percentage of distance reduce by factor 2 up to zero as minimum value
		
	// structural metrics
	$images_per_subsection = $wikimeter->calc_struct_metric("num_images")/$wikimeter->calc_struct_metric("num_subsections");
	$images_per_subsection_dev = max(100-pow(abs(100-$images_per_subsection*100), 1.5), 0);
	$link_density_dev = max(100-
		pow(abs(10-$wikimeter->calc_struct_metric("link_density")), 2) // distance from optimum value (diff up to <10% is ok)
		, 0);
	$structural_metrics_dev = number_format($images_per_subsection_dev*0.4+$link_density_dev*0.6, 2); // link_density has weight 0.6, images_per_subsecion 0.4
	
	// lifecycle metrics
	$age_dev = min(max(log($wikimeter->calc_meta_metric("age"), 10)/2.56, 0), 2.56)*100; // 100% (no deviation), if age is equal or greater than about 1 year
	if($wikimeter->calc_meta_metric("age")>0) {
		$last_edit_per_age_dev = max(100-$wikimeter->calc_meta_metric("last_edit")/$wikimeter->calc_meta_metric("age")*100, 0); // 0 is best
		$lifecycle_dev = number_format(0.5*$age_dev+0.5*$last_edit_per_age_dev, 2); // last_edit to age has weight 0.5, age 0.5
	}
	else {
		$lifecycle_dev = number_format($age_dev, 2); // last_edit to age has weight 0.5, age 0.5
	}
	
	// reputation
	$num_rev_dev = min(log($wikimeter->calc_meta_metric("num_revisions"), 10)/3, 3)*100; // >1000 = 100%, logarithmic approach (base 10)
	$num_auth_dev = min(log($wikimeter->calc_meta_metric("num_authors"), 10)/2, 2)*100; // >100 = 100%, logarithmic approach (base 10)
	$reputation_dev = number_format(0.6*$num_rev_dev+0.4*$num_auth_dev, 2); // we argue that in wikis the number of revisions is more significant than the number of authors involved. hence, 0.4 for authors.
	
	// weighted indices
	$weighted =
		0.1*$complex_words_dev+
		0.1*$word_count_dev+
		0.3*$readability_metrics_dev+
		0.2*$structural_metrics_dev+
		0.1*$lifecycle_dev+
		0.2*$reputation_dev;
	
	echo "<tr><td>".number_format($weighted, 2)."</td><td>".number_format($wikimeter->calc_text_metric("percentage_complex_words"), 2)."</td><td>".$wikimeter->calc_text_metric("word_count")."</td><td>".number_format($wikimeter->calc_struct_metric("link_density"), 2)."</td><td>".$wikimeter->calc_meta_metric("num_revisions")."</td><td>".$wikimeter->calc_meta_metric("age")."</td><td>".$wikimeter->calc_meta_metric("num_authors")."</td></tr>";
	$chart .= number_format($weighted*10, 0).",";
}
echo "</table>";

$chart = substr($chart, 0, -1);

// http://localhost:8080/wikimeter/comparison.php?uri=http://www.ebusiness-unibw.org/wiki/Main_Page
echo "<p><img src=\"$chart\" /></p>";

?>
</div>
<div id="footer">
</div>
</body>
</html>