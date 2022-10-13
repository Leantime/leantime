<?php
/**
 * Generic template for a single element
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 * - $elementName      Name of the current element
 * - $filterStatus     Element status on which to filter
 */
?>
<div class="contentInner even status_<?php echo $elementName; ?>">
  <?php foreach($this->get('canvasItems') as $row) { ?>
    <?php if($row["box"] == "$elementName" && ($row['status'] == $filterStatus || $filterStatus == 'all')) {
        $comments = new \leantime\domain\repositories\comments();
        $nbc = $comments->countComments(moduleId: $row['id']);
      ?>
      <div class="ticketBox" id="item_<?php echo $row["id"];?>">
        <div class="row">
          <div class="col-md-12">
              <div class="inlineDropDownContainer" style="float:right;">
                <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                  <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>
                <?php } ?>
                <?php if($nbc > 0) { ?>
                    <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasComment/<?=$row['id'] ?>" class="<?=$canvasName ?>CanvasModal"
                         data="item_<?php echo $row["id"]; ?>"><span class="fas fa-comment"></span></a>
                <?php } ?>
                <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                &nbsp;&nbsp;&nbsp;
                <ul class="dropdown-menu">
                  <li class="nav-header"><?php echo $this->__("subtitles.edit"); ?></li>
                  <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $row["id"];?>" class="<?=$canvasName ?>CanvasModal"
                         data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.edit_canvas_item"); ?></a></li>
                  <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $row["id"]; ?>" class="delete <?=$canvasName ?>CanvasModal"
                         data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.delete_canvas_item"); ?></a></li>
                </ul>
            <?php } ?>
              </div>
            <h4><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $row["id"];?>" class="<?=$canvasName ?>CanvasModal"
                   data="item_<?php echo $row["id"];?>"><?php $this->e($row["description"]);?></a></h4>
            <?php if($row["conclusion"] != "") { echo '<small>'.$row["conclusion"].'</small>'; } ?>

            <div class="clearfix" style="padding-bottom: 8px;"></div>
            <div class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
              <a class="dropdown-toggle f-left status label-<?=$row["status"]; ?>" href="javascript:void(0);" role="button"
                 id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="text"><?php echo $statusLabels[$row['status']]; ?></span>
                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
              </a>
              <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>
                <?php foreach($statusLabels as $key=>$label) {
                  echo"<li class='dropdown-item'>
                  <a href='javascript:void(0);' class='label-".$key."' data-label='".$this->escape($label)."'
                     data-value='".$row['id']."_".$key."' id='ticketStatusChange".$row['id'].$key."'>".$this->escape($label)."</a>";
                  echo"</li>";
                }?>
              </ul>
            </div>

            <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
              <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="text">
                  <?php if($row["authorFirstname"] != "") {
                    echo "<span id='userImage".$row['id']."'>".
                     "<img src='".BASE_URL."/api/users?profileImage=".$row['authorProfileId']."' width='25' ".
                     "style='vertical-align: middle;'/></span><span id='user".$row['id']."'></span>";
                   } else {
                     echo "<span id='userImage".$row['id']."'><img src='".BASE_URL."/api/users?profileImage=false' width='25' ".
                       "style='vertical-align: middle;'/></span><span id='user".$row['id']."'></span>";
                   } ?>
                 </span>
               </a>
               <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                 <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>
                   <?php foreach($this->get('users') as $user){
                     echo"<li class='dropdown-item'>
                     <a href='javascript:void(0);' data-label='".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."' data-value='".$row['id']."_".$user['id']."_".$user['profileId']."' id='userStatusChange".$row['id'].$user['id']."' ><img src='".BASE_URL."/api/users?profileImage=".$user['profileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."</a>";
                     echo"</li>";
                   }?>
                 </ul>
               </div>
             </div>
           </div>

           <?php if($row['milestoneHeadline'] != '') {?>
           <hr style="margin-top: 5px; margin-bottom: 5px;"/><small>
           <div class="row">
             <div class="col-md-5" >
               <?php strlen($row['milestoneHeadline']) > 60 ? $this->e(substr(($row['milestoneHeadline']), 0, 60)." ...") :  $this->e($row['milestoneHeadline']); ?>
             </div>
             <div class="col-md-7" style="text-align:right">
               <?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?>
             </div>
           </div>
           <div class="row">
             <div class="col-md-12">
               <div class="progress">
                 <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                   <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?></span>
                 </div>
               </div>
             </div>
           </div></small>
           <?php } ?>
</div>
<?php } ?>
<?php } ?>
<br />
<?php if($login::userIsAtLeast($roles::$editor)) { ?>
    <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem?type=<?php echo $elementName; ?>" class="<?=$canvasName ?>CanvasModal" id="<?php echo $elementName; ?>" style="padding-bottom: 10px;"><?=$this->__('links.add_new_canvas_item') ?></a>
<?php } ?>
</div>
