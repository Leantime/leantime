<?php

class leads {
	
	private $status = array('lead' => 'Lead', 'opportunity' => 'Opportunity', 'client' => 'Client');
	
	public function __construct() {
		
		$this->db = new db();
	}
	
	public function getStatus() {
		
		return $this->status;
	}
	
	public function getReferralSource($id) {
		
		$sql = "SELECT * FROM zp_referralSource WHERE id = :id LIMIT 1";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		return $values;
	}
	
	public function getReferralSources() {
		
		$sql = "SELECT * FROM zp_referralSource";
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
	}
	
	public function getHotLeads() {
		
		$sql = "SELECT * FROM zp_lead ORDER BY date ASC";
				
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;		
	}
	
	public function getAllLeads() {
				
		$sql = "SELECT * FROM zp_lead";
				
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
	}
	
	public function getLeads($limit = 9999) {
		
		$sql = "SELECT 
					zp_lead.name, zp_lead.status, zp_lead.id, zp_lead.refSource, zp_lead.refValue, zp_lead.potentialMoney, zp_lead.actualMoney, zp_lead.clientId, zp_lead.creatorId, zp_lead.date,
					zp_clients.name, zp_clients.internet
				FROM zp_lead
				INNER JOIN zp_clients WHERE zp_lead.clientId = zp_clients.id
				LIMIT :limit";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':limit',$limit,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
		
	}
	
	public function getLead($id) {
		
		$sql = "SELECT * FROM zp_lead WHERE id = :id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		return $values;	
			
	}
	
	public function getLeadContact($id) {
		
		$sql = "SELECT 
					contact.street, contact.city, contact.state, contact.country, 
					contact.zip, contact.internet, contact.phone, contact.email  
				FROM zp_lead as lead
					INNER JOIN zp_clients as contact ON lead.clientId = contact.id
				WHERE lead.id = :id";
				
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$value = $stmn->fetch();
		$stmn->closeCursor();
		
		return $value;	
	}
	
	public function addLead($values) {
		
		$sql = "INSERT INTO zp_lead (
			name, status, refSource, refValue, potentialMoney, creatorId, date
		) VALUES (
			:name, 'lead', :source, :value, :potentialMoney, :creatorId, NOW()
		)";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
		$stmn->bindValue(':source', $values['refSource'], PDO::PARAM_STR);
		$stmn->bindValue(':value', $values['refValue'], PDO::PARAM_STR);
		$stmn->bindValue(':potentialMoney', $values['potentialMoney'], PDO::PARAM_STR);
		$stmn->bindValue(':creatorId', $values['creatorId'], PDO::PARAM_INT);
		
		$stmn->execute();
		$stmn->closeCursor();		
		
		return $this->db->{'database'}->lastInsertId();
	}
	
	public function addLeadContact($values,$id) {
		
		$this->db->{'database'}->beginTransaction();

		$sql = "INSERT INTO zp_clients (
			name, street, zip, city, state, country, phone, email, internet, published
		) VALUES (
			:name, :street, :zip, :city, :state, :country, :phone, :email, :internet, 0
		)";

		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
		$stmn->bindValue(':street', $values['street'], PDO::PARAM_STR);
		$stmn->bindValue(':zip', $values['zip'], PDO::PARAM_STR);
		$stmn->bindValue(':city', $values['city'], PDO::PARAM_STR);
		$stmn->bindValue(':state', $values['state'], PDO::PARAM_STR);
		$stmn->bindValue(':country', $values['country'], PDO::PARAM_STR);
		$stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
		$stmn->bindValue(':email', $values['email'], PDO::PARAM_STR);
		$stmn->bindValue(':internet', $values['internet'], PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();			
		
		$clientId = $this->db->{'database'}->lastInsertId();
		
		$sql = "UPDATE zp_lead SET clientId = :clientId WHERE id = :id";

		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':clientId', $clientId, PDO::PARAM_INT);
		$stmn->bindValue(':id', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$stmn->closeCursor();			
		
		$this->db->{'database'}->commit();
	}
	
	public function addReferralSource($values) {
		
		$sql = "INSERT INTO zp_referralSource (
			alias, title
		) VALUES (
			:alias, :title
		)";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':alias',$values['alias'],PDO::PARAM_STR);
		$stmn->bindValue(':title',$values['title'],PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();
		
	}
	
	public function editLead($values,$id) {
		
		$sql = "UPDATE zp_lead 
				SET
					name=:name,
					status=:status,
					refSource=:source,
					refValue=:value,
					potentialMoney=:potentialMoney,
					actualMoney=:actualMoney
				WHERE id=:id";
				
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $id, PDO::PARAM_INT);
		$stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
		$stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
		$stmn->bindValue(':source', $values['refSource'], PDO::PARAM_STR);
		$stmn->bindValue(':value', $values['refValue'], PDO::PARAM_STR);
		$stmn->bindValue(':potentialMoney', $values['potentialMoney'], PDO::PARAM_STR);
		$stmn->bindValue(':actualMoney', $values['actualMoney'], PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();			
		
		$published = 0;
		if ($values['status']==='client')
			$published = 1;
		
		$this->setClient($id,$published);
	}
	
	public function setClient($id, $published = 0) {
		
		$lead = $this->getLead($id);
		
		$sql = "UPDATE zp_clients SET published=:published WHERE id=:clientId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':clientId', $lead['clientId'], PDO::PARAM_INT);
		$stmn->bindValue(':published', $published, PDO::PARAM_INT);
				
		$stmn->execute();
		$stmn->closeCursor();			
	}
	
	public function deleteLead($id) {
		
		$sql = "DELETE FROM zp_lead WHERE id=:id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$stmn->closeCursor();
	}
	
	public function isLead($name) {
		
		$sql = "SELECT * FROM zp_lead WHERE name = :name";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':name', $name, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();			
		
		$flag = false;
		if (count($values)) 
			$flag = true;
		
		return $flag;
	}
}

?>
