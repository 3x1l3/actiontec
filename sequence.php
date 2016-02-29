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

	private $str = null;

	public function __construct($startStr = null) {
		$this->str = $startStr;

		$this->generatePositions();

		$this->cStrL = strlen($this->chars);

	}

	private function generatePositions() {

		for ($i = 0; $i < strlen($this->chars); $i++) {
			$this->positions[$this->at($i)] = $i;
		}

	}

	/**
	 * Get Character at position.
	 */
	private function at($index) {

		if ($index >= strlen($this->chars))
			throw new Exception("method::at index ({$index}) out of bounds");

		return $this->chars[$index];
	}

	private function posAt($char) {
		return $this->positions[$char];
	}

	private function chgAtIndex($index, $char) {
		$this->str[$index] = $char;
	}

	public function getStr() {
		return $this->str;
	}

	private function strAt($index) {
		return $this->str[$index];
	}
	
	private function nextChar($index) {
		return $this->at($this->posAt($this->str[$index]) + 1);
	}

	public function run() {

		//::if empty string is passed then start with the first character.
		if (strlen($this->str) == 0 || $this->str === null) {
			$this->str = $this->at(0);
			return;
		}

		$pos = strlen($this->str) - 1;

		//::If the current position (last character in the string) is less than the length of the sequencing string than increment it.
		if ($this->posAt($this->strAt($pos)) < count($this->positions) - 1) {
			$this->chgAtIndex($pos,$this->nextChar($pos));
		} else {//::we are at the last character in the character string and need to adjust the second last character.

			if ($pos > 0) {
				$prevPos = $pos - 1;
				$this->chgAtIndex($pos, $this->at(0));

				//::If the prevpos has a value less than the last character position than just increment it.
				if ($this->posAt($this->str[$prevPos]) < count($this->positions) - 1) {
					$this->chgAtIndex($prevPos, $this->nextChar($prevPos));
				} else {
					$this->chgAtIndex($prevPos, $this->at(0));
					$prevPos -= 1;
					while ($prevPos >= 0 && $this->posAt($this->strAt($prevPos)) == count($this->positions)-1) {
						$this->chgAtIndex($prevPos, $this->at(0));
						$prevPos -= 1;
						//::at the end of it. If prevpos lands on 0 (first position in the str) than tack on an a.
					}
					if ($prevPos <= 0) {
						$this->str .= $this->at(0);
					} else {
						$this->chgAtIndex($prevPos, $this->nextChar($prevPos));	
					}

				}

			} else {
				$this->chgAtIndex(0, $this->at(0));
				$this->str .= $this->at(0);
			}

		}

	}

}
