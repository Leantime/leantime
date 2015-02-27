<?php $menuObj = $this->get('menu'); ?>

<ul class="level-0">
 <?php foreach($menuObj->getUserMenuRoots() as $row): ?>
	
	<li>
		<a href="<?php echo $row['link'] ?>" class="transition-1 <?php
			if ($_SERVER['REQUEST_URI'] == '/' . html_entity_decode($row['link']) . '') 
				echo ' current '; ?>">
			<span class="title"><?php echo $row['name'] ?></span>
			<span class="desc"></span>
			<span class="line transition-05"></span>
			<span class="triangle"></span>
		</a>
	
	<?php if(count($menuObj->getUserMenuNodes($row['id']))>=1): ?>
		
		<ul>
		<?php foreach($menuObj->getUserMenuNodes($row['id']) as $row): ?>
			
			<li>
				<a href="<?php echo $row['link'] ?>" class="transition-1<?php
					if($_SERVER['REQUEST_URI'] == '/'.html_entity_decode($row['link']).'')
						echo ' current '; ?>">
					<span class="title"><?php echo $row['name'] ?></span>
					<span class="desc"></span>
					<span class="line transition-05"></span>
					<span class="triangle"></span>
				</a>
			</li>
		
		<?php endforeach; ?>
		</ul>
		
	<?php endif; ?>
	</li>
	
 <?php endforeach; ?>
</ul>

