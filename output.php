<?php

class Bar {
	
	var $label, $value, $max;
	
	function __construct($label, $value, $max) {
		$this->label = $label;
		$this->value = $value;
		$this->max = $max;
	}
	
	function get_label() { return $this->label; }
	function get_value() { return $this->value; }
	function get_max() { return $this->max; }
}

class Row {
	
	var $label, $value;
	
	function __construct($label, $value) {
		$this->label = $label;
		$this->value = $value;
	}
	
	function get_label() { return $this->label; }
	function get_value() { return $this->value; }
	
}

class Output {
	
	var $title = null;
	var $bars = null;
	var $rows = null;
	
	function __construct($title) {
		$this->title = $title;
		$this->bars = array();
		$this->rows = array();
	}
	
	function draw_chart($with_img_tag = true) {
		// google chart api
		$base_uri = "http://chart.apis.google.com/chart?";
		$val_string = "";
		$max_string = "";
		$label_string = "";
		$max = 0;
		$min = 0;
		foreach($this->bars as $bar) {
			$val_string .= $bar->get_value().",";
			$max_string .= ($bar->get_max()-$bar->get_value()).",";
			$max = max($max, $bar->get_max());
			$min = min($min, $bar->get_value());
			$label_string = "|".urlencode($bar->get_label()) . $label_string;
		}
		$val_string = substr($val_string, 0, -1);
		$max_string = substr($max_string, 0, -1);
		$params = "cht=bhs&chs=600x".(sizeof($this->bars)*28+65)."&chtt=".urlencode($this->title)."&chco=4D89F9,C6D9FD&chm=N,000000,0,-1,11&chg=10,10,1,5&chd=t:$val_string|$max_string&chds=$min,$max&chxt=x,y,x&chxr=0,0,$max,".($max/5)."&chxl=1:".$label_string."|2:|".urlencode("einfach, Schüler")."|".urlencode("schwierig, Akademiker");
		
		if($with_img_tag)
			return "<img src=\"".$base_uri.$params."\" alt=\"$this->title\" />";
		else
			return $base_uri.$params;
	}
	function add_bar($label, $value, $max) {
		$this->bars[] = new Bar($label, $value, $max);
	}
	
	function print_table() {
		$table = 
"<table cellpadding=\"6\" rules=\"groups\" style=\"width:600px;\">
	<thead>
		<tr>
			<th>Metrik</th><th>Wert</th>
		</tr>
	</thead>
	<tbody>";
		foreach($this->rows as $row) {
			if($row == "---")
				$table .= "
	</tbody>
	<tbody>";
			else
				$table .= "
		<tr>
			<td>$row->label</td><td>$row->value</td>
		</tr>";
		}
		$table .= "
	</tbody>
</table>";
		return $table;
	}
	function add_row($label, $value) {
		$this->rows[] = new Row($label, $value);
	}
	function add_separator() {
		$this->rows[] = "---";
	}
}

?>