<?php

class statistics extends leads {
	
	public function run() {
		
		$tpl = new template();
		
			// Statistics
			$leads = $this->getLeads();
			$count = 0;
			$ageCount = 0;
			$totalAges = 0;
			$estimatedMoneyCount = 0;
			$estimatedMoneyTotal = 0;
			$actualMoneyCount = 0;
			$actualMoneyTotal = 0;
			$convertedLead = 0;
			$unconvertedLead = 0;
			$now = round(strtotime("now") / 60 / 60 / 24 / 7 / 4.33);
			$references = array();
			$highest = 0;
			$refLabels = array();
			$first = false;
			foreach ($leads as $lead) {
				if ($lead['age']) {
					$ageCount++;
					$totalAges += $lead['age'];
				}
				
				if ($lead['potentialMoney']) {
					$estimatedMoneyCount++;
					$estimatedMoneyTotal += $lead['potentialMoney'];
				}
				
				if ($lead['actualMoney']) {
					$actualMoneyCount++;
					$actualMoneyTotal += $lead['actualMoney'];
				}
				
				if ($lead['status'] == 'client') {
					$convertedLead++;
				} 
					
				if ($first !== true) {
					$first = true;
					$firstDate = round(strtotime($lead['date']) / 60 / 60 / 24 / 7 / 4.33); // months
				}
				
				if (isset($references[$lead['refSource']])) {
					$refSource = $this->getReferralSource($lead['refSource']);
					$references[$lead['refSource']] += 1;
					$refLabels[$lead['refSource']]  = $refSource['title'];
				} else {
					$references[$lead['refSource']] = 1;
					$refSource = $this->getReferralSource($lead['refSource']);
					$refLabels[$lead['refSource']]  = $refSource['title'];
				}
				
				$count++;
			}
			
			foreach ($references as $key => $ref) 
				if ($ref > $highest)  {
					$highest = $ref;
					$highestKey = $key;
				}
	
			$highestRef = $this->getReferralSource($highestKey);
			$avgAgeOfLead = $totalAges / $ageCount;
			$actualMoneyPerLead = $actualMoneyTotal / $actualMoneyCount;
			$estimatedMoneyPerLead = $estimatedMoneyTotal / $estimatedMoneyCount;
			$conversionRatio = $count / $convertedLead;
			$newLeads = $this->getLeads(5);
			$topReferences = $highestRef['title'];
			$avgLeadPerMonth = round($count / ($now - $firstDate));
			
			// statistics: avgLeadPerMinute, avgLeadPerMinute, conversionRatio, avgColdLeadAge, avgMoneyPerLead, newLeads
			$tpl->assign('avgLeadPerMonth', $avgLeadPerMonth);
			$tpl->assign('topReferences', $topReferences);
			$tpl->assign('conversionRatio', $conversionRatio);
			$tpl->assign('avgColdLeadAge', $avgAgeOfLead);
			$tpl->assign('actualMoneyPerLead', $actualMoneyPerLead);
			$tpl->assign('estimatedMoneyPerLead', $estimatedMoneyPerLead);
			$tpl->assign('newLeads', $newLeads);
			$tpl->assign('refLabels',$refLabels);
			$tpl->assign('references',$references);				
		
		$tpl->display('leads.statistics');
		
	}
	
}
