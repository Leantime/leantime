<?php $menus = $this->get('menu') ;

$currentLink = explode(".", $_GET['act']);
$module = '';
$action = '';
if(is_array($currentLink)) {
	$module = $currentLink[0];
	$action = $currentLink[1];
}
?>



<ul class="nav nav-tabs nav-stacked">
	<li class="nav-header">Navigation</li>
	<?php foreach($this->get('menus') as $menu): ?>
	  <?php if ($this->displayLink($menu['module'].'.'.$menu['action'],'x') !== false): ?>
	  <li 
	  
	  
	  
	  <?php if (!$menus->getChildren($menu['id'])): ?>
	  	
			<?php if($module == $menu['module'] && $action == $menu['action']) echo" class='active' "; ?>
			><?php echo $this->displayLink(
						$menu['module'].'.'.$menu['action'], 
						'<span class="'.$menu['icon'].'"></span>'.$language->lang_echo($menu['name'], false).''
					) ?>
			</li>
		<?php else: ?>
		
			class='dropdown <?php if($module == $menu['module']) echo" active ";?>'>
		
			<a href><span class='<?php echo $menu['icon'] ?>'></span><?php echo $language->lang_echo($menu['name']) ?></a>
				<ul <?php if($module == $menu['module']) echo" style='display:block' ";?>>
					<?php foreach ($menus->getChildren($menu['id']) as $child): ?>
						<?php if($this->displayLink($child['module'].'.'.$child['action'],'x') !== false): ?>
						<li <?php if($module == $child['module'] && $action == $child['action']) echo" class='active' "; ?>>
							<?php echo $this->displayLink(
										$child['module'].'.'.$child['action'], 
										$language->lang_echo($child['name'], false)
									) ?>
						</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</li>
		<?php endif; ?>
	  <?php endif; ?>
	<?php endforeach; ?>
</ul>
