<?php

class CustomException extends Exception {
	public function __toString()
    {
		//return get_class($this) . ": '{$this->message}' in {$this->file}({$this->line})\n" . "<div style=\"padding:8px; font-size:small;\">--<br />" . nl2br($this->getTraceAsString()). "<br />--</div>";
		return $this->message;
    }
}

class ParameterPassingException extends CustomException {}
class IOException extends CustomException {}

?>