<?php 

class ReportTable {
	
	public $tableName;
	
	public $tableSorting;
	
	public $arrayOfEntries;
	
	
	function __construct($name, $sorting) {
		$this->tableName = $name;
		$this->tableSorting = $sorting;
		$this->arrayOfEntries = array();
	}
	
	function insertEntry($entry) {
		array_push($this->arrayOfEntries, $entry);
	}
	
	function getTotalTimeOfEntries() {
		$totalTime = 0;
		foreach ($this->arrayOfEntries as $entry) {
			$totalTime += $entry->entryTimeAccumulated;
		}
		return $totalTime;
	}
	
	
	
	function getHtmlTable() {
		$html = "<h3> $this->tableName ------------ Total Time: " . gmdate("H:i:s", $this->getTotalTimeOfEntries()) . "</h3>";
		
		$html .= "<table>
						<tr id='tableHeader'>
							<th><center>$this->tableSorting </center></th>
							<th><center>Last Visit</center></th>
							<th><center>Time Accumulated</center></th>
						</tr>";
		
		foreach ($this->arrayOfEntries as $entry) {
			if ($entry->isSection >= 1)
				$html .= "<tr id='sectiontr'>";
			else
				$html .= "<tr id='$entry->isSection'>";
			
			$html .= "<td> $entry->entryName </td>";
			
			if ($entry->entryLastVisit > 0) {
				$html .= "<td>" . gmdate("M d Y", $entry->entryLastVisit) . "</td>";
				$html .= "<td>" . gmdate("H:i:s", $entry->entryTimeAccumulated) . "</td>";
			}
			else {
				$html .= "<td> Never </td>";
				$html .= "<td> 00:00:00 </td>";
			}
			
			$html .= "</tr>";
		}
		
		$html .= "</table><br><br>";
		
		return $html;
	}//getHtmlTable
	
	
	
}
	
class ReportEntry {
	
	/**
	 * Name of the entry
	 * @var string
	 */
	public $entryName;
	
	
	/**
	 * Date of the last visit in seconds since January 1 1970
	 * @var integer
	 */
	public $entryLastVisit;
	
	
	/**
	 * Time accumulated in that entry in seconds
	 * @var integer
	 */
	public $entryTimeAccumulated;
	
	
	public $isSection;
	
	
	function __construct($name, $lastVisit, $timeAccumulated) {
		$this->entryName= $name;
		$this->entryLastVisit= $lastVisit;
		$this->entryTimeAccumulated= $timeAccumulated;
		$this->isSection = 0;
	}
	
	
}



?>