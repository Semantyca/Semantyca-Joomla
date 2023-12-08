<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;
class Util {

	public static function generateSubject(): string
	{
		$topics = array(
			"Important News", "Urgent Update", "Last Warning", "Breaking News", "Critical Information",
			"Mandatory Action Required", "Emergency Meeting", "Project Update", "Strategic Initiative",
			"Announcement", "Meeting Agenda", "Policy Change", "Procedure Update", "System Maintenance",
			"Sales Report", "Financial Update", "Operational Changes", "Company News", "Industry Trends"
		);

		$actions = array(
			"Review", "Action", "Consider", "Discuss", "React", "Respond", "Evaluate", "Analyze", "Assess", "Plan"
		);

		$randomTopic = $topics[array_rand($topics)];
		$randomAction = $actions[array_rand($actions)];

		return $randomTopic . ": " . $randomAction;
	}
}





