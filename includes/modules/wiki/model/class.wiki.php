<?php

/**
 * Ticket class
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.1
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class wiki {

	/**
	 * @access public
	 * @var object
	 */
	public $result = NULL;

	/**
	 * @access public
	 * @var object
	 */
	public $wiki = NULL;

	/**
	 * @access private
	 * @var object
	 */
	private $db='';

	

	/**
	 * __construct - get db connection
	 *
	 * @access public
	 * @return unknown_type
	 */
	public function __construct() {

		$this->db = new db();

	}

	/**
	 * getAll - get all Tickets, depending on userrole
	 *
	 * @access public
	 * @return array
	 */
	public function getAll($searchTerm, $catId = '') {
		
		$query = "SELECT zp_wiki.*, zp_user.firstname, zp_user.lastname FROM zp_wiki LEFT JOIN zp_user ON zp_wiki.authorId = zp_user.id WHERE zp_wiki.headline <> '' ";
			
		if($catId != ''){	
			$query .= " AND zp_wiki.category LIKE '%".$catId."%'";
		}

		if($searchTerm != ''){
			$query .= " AND (zp_wiki.headline LIKE '%".$searchTerm."%' OR
				zp_wiki.text LIKE '%".$searchTerm."%' OR
				zp_wiki.tags LIKE '%".$searchTerm."%' )";
		}
		
		$query .= " ORDER BY zp_wiki.date DESC";

		return $this->db->dbQuery($query)->dbFetchResultsUnmasked();
	
	}
	
	public function getArticle($id){
		
		$query = "SELECT zp_wiki.*, zp_user.firstname, zp_user.lastname 
		FROM zp_wiki LEFT JOIN zp_user ON zp_wiki.authorId = zp_user.id WHERE zp_wiki.id = '".$id."'";
		
		return $this->db->dbQuery($query)->dbFetchRowUnmasked();	
		
	}
	
	public function addArticle($values){
		
		$query = "INSERT INTO zp_wiki (headline, text, tags, authorId, date, category) VALUES
		('".$values['headline']."', '".$values['text']."', '".$values['tags']."', '".$values['authorId']."', '".$values['date']."', '".$values['category']."')";
		
		$this->db->dbQuery($query);
		
	}
	
	public function delArticle($id){
		
		$query = "DELETE FROM zp_wiki WHERE id = '".$id."' ";
		
		$this->db->dbQuery($query);
		
	}
	
	public function updateArticle($values, $id){
		
		$query = "UPDATE zp_wiki 
			SET
			 headline = '".$values['headline']."',
			 text = '".$values['text']."',
			 tags = '".$values['tags']."',
			 category = '".$values['category']."'
			 WHERE id = '".$id."'";
		
		$this->db->dbQuery($query);
		
		
	}
	
	public function getCategories(){
		
		$query = "SELECT 
			zp_wiki_categories.id, 
			zp_wiki_categories.name
		FROM zp_wiki_categories ";
		
		return $this->db->dbQuery($query)->dbFetchResults();	
	}
	
	public function addCategory($catName){
		
		$query = "INSERT INTO zp_wiki_categories (name) VALUES ('".$catName."')";
		
		return $this->db->dbQuery($query);
		
	}
	
	public function getComments($id, $fatherId='', $comments=array(), $level = 0) {
		
		$query = "SELECT
		zp_wiki_comments.id,
		zp_wiki_comments.text, 
		zp_wiki_comments.userId, 
		zp_wiki_comments.date, 
		zp_wiki_comments.commentParent,
		zp_user.firstname,
		zp_user.lastname
		 FROM zp_wiki_comments JOIN zp_user 
		WHERE zp_wiki_comments.articleId = '".$id."' AND zp_wiki_comments.userId = zp_user.id ";

		if($fatherId == ''){ 
			
			$query .= "AND (commentParent IS NULL || commentParent = '')";
		
		}else{

			$query .= "AND commentParent = '".$fatherId."'";
		
		}
		
		$query .="
		Order BY zp_wiki_comments.date DESC";

		$results = $this->db->dbQuery($query)->dbFetchResults();
		
		foreach($results as $row){
			
			global $comments;
			//$comments[]['level']= $level;
			
			$row['level'] = $level;
			$comments[] = $row;
			
			
			
			$this->getComments($id, $row['id'], $comments, $level+1);
			
		
			
		}
		
		return $comments;
		
		
	}
	
	/**
	 * addComment - add a comment to a project
	 *
	 * @access public
	 * @param $values
	 *
	 */
	public function addComment($values) {

		$query = "INSERT INTO zp_wiki_comments (text, userId, date, articleId, commentParent)
		VALUES ('".$values['text']."', '".$values['userId']."', '".$values['date']."', '".$values['ticketId']."', '".$values['commentParent']."')";

		$this->db->dbQuery($query);

	}

	/**
	 * deleteComment - delete a comment
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteComment($id) {

		$query = "DELETE FROM zp_wiki_comments WHERE id = '".$id."' OR commentParent = '".$id."'";

		$this->db->dbQuery($query);

	}
	
	
	public function getFiles($id) {

		$query = "SELECT
			zp_wiki_files.encName, 
			zp_wiki_files.realName, 
			zp_wiki_files.date, 
			zp_wiki_files.userId, 
			zp_user.firstname, 
			zp_user.lastname 
			FROM zp_wiki_files JOIN zp_user ON zp_wiki_files.userId = zp_user.id WHERE zp_wiki_files.articleId = '".$id."' ORDER BY date";

		return $this->db->dbQuery($query)->dbFetchResults();

	}
	
/**
	 * addFile - add a file to the list
	 *
	 * @access public
	 * @param $values
	 *
	 */
	public function addFile($values) {

		$query = "INSERT INTO zp_wiki_files
			(encName, realName, date, articleId, userId) 
		VALUES 
			('".$values['encName']."', '".$values['realName']."', '".$values['date']."', '".$values['articleId']."', '".$values['userId']."')";

		$this->db->dbQuery($query);

	}

	/**
	 * deleteFile - delete a file
	 *
	 * @access public
	 * @param $file
	 *
	 */
	public function deleteFile($file) {
		$query = "DELETE FROM zp_wiki_files WHERE encName = '".$file."' LIMIT 1";

		$this->db->dbQuery($query);
	}

	/**
	 * deleteAllFiles - delete the whole list and the files on the server
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteAllFiles($id) {

		$upload = new fileupload();

		$query1 = "SELECT encName FROM zp_wiki_files WHERE articleId = '".$id."'";

		foreach($this->db->dbQuery($query1)->dbFetchResults() as $row) {

			$upload->deleteFile($row['encName']);

		}

	}
	
	
	public function getAllTags(){
		
		$query = "SELECT tags FROM zp_wiki";
		
		return $this->db->dbQuery($query)->dbFetchResults();
		
	}
		

}

?>