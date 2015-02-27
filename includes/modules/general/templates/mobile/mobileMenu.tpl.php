<?php $login= $this->get('login'); ?>
	
<div id="menu" data-role="navbar">

<?php if($login->logged_in() === true){?>
	
	<!--<a href="index.php?act=contacts.showPersonen">Suche nach Personen</a> 



	<a href='index.php?logout=1&sid=<?php echo session::getSID() ?>' style='color:#333;'>Abmelden</a> -->
	
<?php }else{?>

	<!-- <a href="index.php?act=general.mobileLogin">Login</a> -->
	
	<?php $menuObj = $this->get('menu');


foreach($menuObj->getUserMenuRoots() as $row) {

	

	echo'<a href="'.$row['link'].'"';

				
				
			echo' class="link" >'.$row['name'].'</a>';
            
	/*
	if(count($menuObj->getUserMenuNodes($row['id']))>=1){
		
		
		
		foreach($menuObj->getUserMenuNodes($row['id']) as $row){
			
			echo'<a href="'.$row['link'].'"';
				
				
				
			echo' style="margin-left:10px;" class="link" >'.$row['name'].'</a>';
		
		}
		
		echo'';
		
	}
	
     * 
     */
	echo'';
	
	
}

?>
	
<?php } ?>
</div>


