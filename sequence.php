<?php
/**
 * Sequence Class
 * Generate the next sequence using the provided string. Also determine the start point
 * if an input string is given.
 *
 */

class Sequence {

	//::String to use for sequencing
	private $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()_+-={}[]|\\;:<>,./?';

	//::Static length of character string. above
	private $cStrL = null;

	//::Store the position of each character in an array for direct access.
	private $positions = array();

	private $str = '';

	public function __construct($startStr = null) {
		if ($startStr !== null)
			$this->str = $startStr;

		$this->generatePositions();

		$this->cStrL = strlen($this->chars);

	}

	private function generatePositions() {

		for ($i = 0; $i < strlen($this->chars); $i++) {
			$this->positions[$this->chars[$i]] = $i;
		}

	}

	/**
	 * Get Character at position.
	 */
	private function at($index) {
		return $this->chars[$index];
	}

	private function posAt($char) {
		return $this->positions[$char];
	}

	private function chgAtIndex($index, $char) {
		$this->str[$index] = $char;
	}

	public function run() {

		//::if empty string is passed then start with the first character.
		if (strlen($this->str) == 0) {
			$this->str = $this->positions[0];
		}

		$pos = strlen($this->str) - 1;

		//::If the current position (last character in the string) is less than the length of the sequencing string than increment it.
		if ($pos < count($this->positions)) {
			$this->str[$pos] = $this->at($this->positions[$pos] + 1);
		} else {//::we are at the last character in the character string and need to adjust the second last character.

			//::Assuming the last character is at a position greater than 1 so a string like aa
			if ($pos > 0) {

				$prevPos = $pos - 1;
				//::Change the
				if ($this->posAt($this->str[$prevPos]) < count($this->positions)) {
					$this->chgAtIndex($prevPos, $this->at($this->posAt($prevPos) + 1));
				} else {
					$this->chgAtIndex($prevPos, $this->at(0));
					$prevPos -= 1;
					while ($this->posAt($prevPos) == count($this->positions) && $prevPos > 0) {
						$this->chgAtIndex($prevPos, $this->at(0));
						$prevPos -= 1;
					}
					
					//::at the end of it. If prevpos lands on 0 (first position in the str) than tack on an a.
					if ($prevPos == 0) {
						$this->str .= $this->at(0);	
					}
					
				}

			} else {
				
				
				
			}

		}

	}

}
