#!/usr/bin/php

<?php

$csv = "IssueType,List,Summary,Description,Asignee, Asignee, Asignee,Label,Label,Points,Comment,Comment,Comment,Comment,Comment,Comment,Comment,Comment,Comment,Comment,Comment\n";

$ch = curl_init("https://api.trello.com/1/boards/uzrRlj4H/lists?key=&token=");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

$onsite = json_decode(curl_exec($ch), true);
curl_close($ch);

foreach ($onsite as $list) {
	$idList = $list['id'];
	$listName = $list['name'];
	
	$ch = curl_init("https://api.trello.com/1/lists/" . $idList . "/cards?actions=commentCard&checklists=all&key=&token=");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	$cards = json_decode(curl_exec($ch), true);
	curl_close($ch);
	
	foreach ($cards as $card) {
		$points = "";
		$summary = $card['name'];
		if (is_numeric(substr($summary, strrpos($summary, "(") + 1, -1))) {
			$points = substr($summary, strrpos($summary, "(") + 1, -1);
			$summary = trim(substr($summary, 0, strrpos($summary, "(")));
		}
		else if (is_numeric(substr($summary	, strrpos($summary, "<") + 1, -1))) {
			$points = substr($summary, strrpos($summary, "<") + 1, -1);
			$summary = trim(substr($summary, 0, strrpos($summary, "<")));
		}
		$summary = "\"" . str_replace("\"", "\"\"", $summary) . "\"";

		$labels = $card['labels']; // [0]['name']
		$labelText = "";
		foreach ($labels as $label) {
			$labelText .= $label['name'] . ",";
		}
		$labelText = substr($labelText, 0, -1) . "\"";
		
		$description = $card['desc'];
		
		$idChecklists = $card['checklists']; //[0]['name'], ['checkItems'][0]['state'],['name']
		if (sizeof($idChecklists) != 0) {
			foreach ($idChecklists as $checklist) {
				$description .= "\n\n" . $checklist['name'] . "\n***********\n";
				foreach ($checklist['checkItems'] as $item) {
					if ($item['state'] == "complete")
						$description .= json_decode('"\u2713"') . " ";
					else
						$description .= "- ";
					$description .= $item['name'] . "\n";
				}
			}
		}
		$description = "\"" . str_replace("\"", "\"\"", $description) . "\"";
		
		$idMembers = $card['idMembers']; //parse this
		$memberText = "";
		foreach ($idMembers as $member) {
			$ch = curl_init("https://api.trello.com/1/members/" . $member . "?key=&token=");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

			$mResult = json_decode(curl_exec($ch), true);
			$memberText .= $mResult['fullName'] . ",";
			curl_close($ch);
		}
		$memberText .= str_repeat(',', 3 - sizeof($idMembers));
		$memberText = substr($memberText, 0, -1);
		
		$comments = $card['actions']; //[0]['data']['text], [0]['date'], [0]['memberCreator']['fullName']
		$commentText = "";
		foreach ($comments as $comment) {
			$commentText .= substr($comment['date'], 0, -8) . ';' . $comment['memberCreator']['fullName'] . ';' . $comment['data']['text'] . ',';
		}
		$commentText .= str_repeat(',', 11 - sizeof($comments));
		$commentText = substr($commentText, 0, -1);
		$commentText = "\"" . str_replace("\"", "\"\"", $commentText) . "\"";
		
		$csv .= "Story," . $listName . "," . $summary . "," . $description . "," . $memberText . "," . $labelText . "," . $points . "," . $commentText;
	}
}

echo $csv;

?>