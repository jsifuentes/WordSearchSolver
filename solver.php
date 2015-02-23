<?php
ini_set("display_errors", "On");
error_reporting(E_ALL);


// WORDS TO FIND
$allWords = "TEST
CUP
BOTTLE
MOUSE
SPEAKER
STRANGLE
SEARCHINGZZZ
WORKING
PIZZA";

// WORD SEARCH GRID
$wordSearchPlain = "RZSJKSLBQJSRDVRFJJSR
AUERHEMSJDEDWVCGGVRH
LAAZCEVTKFDDWDXOKBBD
KERHOACQHJEMYSQXZQZD
LSCNQVQNZOBLTKPCNAHU
OIHXFPGROXPRTNNORZGF
QCIAZZIPCWADBTKFLAIY
BGNPTPXURNVNYNOBWCPJ
RNGHJXPMGZAIDBTBINTQ
LIZVGFLLEXGRVUTRFXJB
OKZQKMEDKMMNOGVLBAMN
VRZSSGXQNPMHJEOMFRSF
TOJQGNBSAOHGKWOIGEWV
BWPOHNEUCQCLEUUHEKAB
NRIBSABTHHHRSRQNOALP
DZWQERTVSTSEEQBIREDW
ILYBMZIUHEZKUNWKRPVG
IAXBVRGMTOTDWLLHJSNU
JVOQVYLJUVAQBJRZLNRR
ZKGCHROPBBJKXXATVMCQ";


class WordSearch
{
	protected $searching;
	protected $words;
	protected $matches;

	protected $maxRowIndexAllowed;
	protected $maxColumnIndexAllowed;

	protected $niceResult = false;

	public function __construct(array $searching, array $words)
	{
		$this->searching = $searching;

		if(count($this->searching) == 0) {
			throw new Exception("Word search must have at least one row.");
		}

		if(count($this->searching[0]) == 0) {
			throw new Exception("Rows in word searches must have at least one element.");
		}

		$this->maxRowIndexAllowed = count($this->searching) - 1;
		$this->maxColumnIndexAllowed = count($this->searching[0]) - 1;

		$this->words = $words;
	}

	public function checkCell($rowIndex, $columnIndex, $matching)
	{
		if($rowIndex < 0 && $columnIndex < 0 && $rowIndex > $this->maxRowIndexAllowed && $columnIndex > $this->maxColumnIndexAllowed) {
			return false;
		}

		if(!isset($this->searching[$rowIndex])) {
			return false;
		}

		if(!isset($this->searching[$rowIndex][$columnIndex])) {
			return false;
		}

		return $this->searching[$rowIndex][$columnIndex] == $matching;
	}

	public function checkNorth($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex - 1, $columnIndex, $matching)) ? array($rowIndex - 1, $columnIndex) : false;
	}

	public function checkSouth($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex + 1, $columnIndex, $matching)) ? array($rowIndex + 1, $columnIndex) : false;
	}

	public function checkEast($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex, $columnIndex + 1, $matching)) ? array($rowIndex, $columnIndex + 1) : false;
	}

	public function checkWest($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex, $columnIndex - 1, $matching)) ? array($rowIndex, $columnIndex - 1) : false;
	}

	public function checkNorthEast($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex - 1, $columnIndex + 1, $matching)) ? array($rowIndex - 1, $columnIndex + 1) : false;
	}

	public function checkNorthWest($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex - 1, $columnIndex - 1, $matching)) ? array($rowIndex - 1, $columnIndex - 1) : false;
	}

	public function checkSouthEast($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex + 1, $columnIndex + 1, $matching)) ? array($rowIndex + 1, $columnIndex + 1) : false;
	}

	public function checkSouthWest($rowIndex, $columnIndex, $matching)
	{
		return ($this->checkCell($rowIndex + 1, $columnIndex - 1, $matching)) ? array($rowIndex + 1, $columnIndex - 1) : false;
	}

	public function findWord($word)
	{
		// try to find the first letter of the word in entire puzzle
		$firstLetterCells = array();
		foreach($this->searching as $rowIndex => $row) {
			$arraySearchResults = array_keys($row, $word[0]);
			foreach($arraySearchResults as $result) {
				$firstLetterCells[] = array($rowIndex, $result);
			}
		}

		if(empty($firstLetterCells)) {
			return array();
		}

		$possiblePaths = array();
		// Okay, try to find the second match to get an idea of a path.
		foreach($firstLetterCells as $startCells) {
			$rowIndex = $startCells[0];
			$columnIndex = $startCells[1];

			// check north cell
			if($this->checkNorth($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "north");
			}

			// check south cell
			if($this->checkSouth($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "south");
			}

			// check east cell
			if($this->checkEast($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "east");
			}

			// check west cell
			if($this->checkWest($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "west");
			}

			// check north east cell
			if($this->checkNorthEast($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "northeast");
			}

			// check north west cell
			if($this->checkNorthWest($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "northwest");
			}

			// check south east cell
			if($this->checkSouthEast($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "southeast");
			}

			// check south west cell
			if($this->checkSouthWest($rowIndex, $columnIndex, $word[1])) {
				$possiblePaths[] = array($rowIndex, $columnIndex, "southwest");
			}
		}

		if(empty($possiblePaths)) {
			return array();
		}

		// now verify!
		$weDidIt = array();
		foreach($possiblePaths as $element) {
			$currentRowIndex = $element[0];
			$currentColumnIndex = $element[1];
			$path = $element[2];

			$weDidIt[] = $currentRowIndex . "," . $currentColumnIndex;

			$checkFunc = null;

			switch($path) {
				case "north":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkNorth($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "south":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkSouth($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "east":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkEast($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "west":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkWest($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "northeast":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkNorthEast($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "northwest":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkNorthWest($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "southeast":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkSouthEast($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				case "southwest":
					$checkFunc = function($currentRowIndex, $currentColumnIndex, $match) {
						return $this->checkSouthWest($currentRowIndex, $currentColumnIndex, $match);
					}; break;
				default:
					throw new Exception("wat");
			}

			$failed = false;
			for($i = 1; $i < strlen($word); $i++) {
				if(($result = $checkFunc($currentRowIndex, $currentColumnIndex, $word[$i]))) {
					$currentRowIndex = $result[0];
					$currentColumnIndex = $result[1];
					$weDidIt[] = $currentRowIndex . "," . $currentColumnIndex;
				} else {
					$failed = true;
					break;
				}
			}

			if($failed) {
				$weDidIt = array();
			} else {
				break;
			}
		}

		return $weDidIt;
	}

	public function solve($wordIndex = true)
	{
		$results = array();
		foreach($this->words as $word) {
			$result = $this->findWord($word);
			if(!empty($result)) {
				if($wordIndex) {
					$results[$word] = $result;
				} else {
					$results = array_merge($results, $result);
				}
			}
		}

		return $results;
	}
}

$search = array();

$breakIntoLines = explode("\n", $wordSearchPlain);
foreach($breakIntoLines as $line) {
	$search[] = str_split($line, 1);
}

$breakAllWords = explode(PHP_EOL, $allWords);

$wordSearch = new WordSearch($search, $breakAllWords);

$solved = $wordSearch->solve(false);

echo "<table border\"0\">";
foreach($search as $rowIndex => $row) {
	echo "<tr>";
	foreach($row as $letterIndex => $letter) {
		if(in_array($rowIndex . "," . $letterIndex, $solved)) {
			echo "<td style=\"color:red;font-weight:bold;\">" . $letter . "</td>";
		} else {
			echo "<td>" . $letter . "</td>";
		}
	}
	echo "</tr>";
}
echo "</table>";