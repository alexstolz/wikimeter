<?php

require_once("TextStatistics.php");
require_once("exceptions.php");
require_once("utils.php");

class Wikimeter {
	var $stats = null;
	var $utils = null;
	var $xml = null;
	var $text = null;
	
	function __construct() {
		$this->stats = new TextStatistics();
		// parse uri, fetch data object from xml
		$this->utils = new Utils();
		$this->xml = $this->utils->get_xml_object();
		$this->text = $this->get_last_revision()->text;
	}
	//readability metrics
	function calc_read_metric($id) {
		switch($id) {
			case "flesch1": // flesch reading ease score
				return $this->stats->flesch_kincaid_reading_ease($this->text);
			case "flesch2": // flesch kincaid grade level
				return $this->stats->flesch_kincaid_grade_level($this->text);
			case "ari": // automated readability index
				return $this->stats->automated_readability_index($this->text);
			case "cli": // coleman-liau index
				return $this->stats->coleman_liau_index($this->text);
			case "fog": // gunning fog
				return $this->stats->gunning_fog_score($this->text);
			case "smog": // smog grading
				return $this->stats->smog_index($this->text);
		}
	}
	// textual metrics
	function calc_text_metric($id) {
		switch($id) {
			case "word_count":
				return $this->stats->word_count($this->text);
			case "letter_count":
				return $this->stats->letter_count($this->text);
			case "syllable_count":
				return $this->stats->syllable_count($this->text);
			case "sentence_count":
				return $this->stats->sentence_count($this->text);
			case "complex_word_count":
				return $this->stats->words_with_three_syllables($this->text);
			
			// stats
			case "average_words_per_sentence":
				return $this->stats->average_words_per_sentence($this->text);
			case "average_syllables_per_word":
				return $this->stats->average_syllables_per_word($this->text);
			case "percentage_complex_words":
				return $this->stats->percentage_words_with_three_syllables($this->text);
			case "average_letters_per_word":
				return $this->stats->letter_count($this->text)/$this->stats->word_count($this->text);
		}
	}
	// structural metrics
	function calc_struct_metric($id) {
		switch($id) {
			case "num_int_links":
				return $this->get_num_int_links();
			case "num_ext_links":
				return $this->get_num_ext_links();
			case "num_images":
				return $this->get_num_images();
			case "num_subsections":
				return $this->get_num_subsections();
			
			// stats
			case "link_density": // (int_links+ext_links)/word_count
				return ($this->get_num_int_links()+$this->get_num_ext_links()) / $this->stats->word_count($this->text) *100;
		}
	}
	// meta-metrics (author-related things)
	function calc_meta_metric($id) {
		switch($id) {
			case "num_authors":
				$authors = $this->get_num_different_authors();
				return $authors["sum"];
			case "num_revisions":
				return $this->get_num_revisions();
			case "age":
				return $this->utils->get_time_diff($this->get_first_revision()->timestamp);
			case "last_edit":
				return $this->utils->get_time_diff($this->get_last_revision()->timestamp);
		}
	}
	// condensed metrics (ideal for a quick overview)
	function calc_condensed_metric($id) {
		switch($id) {
			case "average_grade_readability_metrics": // get rid of lowest and highest values, which could be statistical outliers
				$m1 = $this->stats->flesch_kincaid_grade_level($this->text);
				$m2 = $this->stats->automated_readability_index($this->text);
				$m3 = $this->stats->coleman_liau_index($this->text);
				$m4 = $this->stats->gunning_fog_score($this->text);
				$m5 = $this->stats->smog_index($this->text);
				return ($m1+$m2+$m3+$m4+$m5-min($m1,$m2,$m3,$m4,$m5)-max($m1,$m2,$m3,$m4,$m5)) / 3;
		}
	}
	
	// structural computations
	function get_num_int_links() {
		return preg_match_all("/\[\[/", $this->text, $matches);
	}
	function get_num_ext_links() {
		return preg_match_all("/(https?:\/\/|ftps?:\/\/)/i", $this->text, $matches);
	}
	function get_num_images() {
		return preg_match_all("/(\.jpg|\.png|\.gif|\.svg)/i", $this->text, $matches);
	}
	function get_num_subsections() {
		return preg_match_all("/[^=]==[^=]/", $this->text, $matches)/2;
	}
	
	// revisions
	function get_first_revision() {
		/*
			revision
				id
				timestamp
				contributor (ip or username)
				text
		*/
		return $this->xml->page->revision[0];
	}
	function get_last_revision() {
		return $this->xml->page->revision[$this->get_num_revisions()-1];
	}
	function get_num_revisions() {
		return sizeof($this->xml->page->revision);
	}
	function get_num_different_authors() {
		/*
			SimpleXMLElement Object
			(
		    	[id] => 1
		    	[timestamp] => 2010-10-01T08:04:02Z
		    	[contributor] => SimpleXMLElement Object
		        	(
		            	[ip] => MediaWiki default
		        	)
		
			returns an array consisting of indices "ip", "username" and "sum"
		*/
		$contributors_ip = array();
		$contributors_username = array();
		foreach ($this->xml->page->revision as $revision) {
			if($revision->contributor->ip)
				$contributors_ip[] = $revision->contributor->ip;
			else if($revision->contributor->username)
				$contributors_username[] = $revision->contributor->username;
		}
		// remove duplicate entries
		$contributors_ip = array_unique($contributors_ip);
		$contributors_username = array_unique($contributors_username);
		
		return array(
			"ip"=>sizeof($contributors_ip),
			"username"=>sizeof($contributors_username),
			"sum"=>sizeof($contributors_ip)+sizeof($contributors_username));
	}
	
	function get_utils() {
		return $this->utils;
	}
}

?>