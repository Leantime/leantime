<?php

/**
 * element.inc template - Generic template for a single element
 *
 * Required variables:
 * - $canvasName  Name of current canvas
 * - $elementName Name of the current element
 * - $filter      Array on which to filter
 */

?>
<h4 class="widgettitle title-primary">
    <?php if (isset($canvasTypes[$elementName]['icon'])) {
        echo '<i class="fas ' . $canvasTypes[$elementName]['icon'] . '"></i> ';
    }
    ?><?=$canvasTypes[$elementName]['title'] ?>
</h4>
<div class="contentInner even status_<?php echo $elementName; ?>"
     <?=(isset($canvasTypes[$elementName]['color']) ? 'style="background: ' . $canvasTypes[$elementName]['color'] . ';"' : '') ?>>

    <?php foreach ($canvasItems as $row) {
        $filterStatus = $filter['status'] ?? 'all';
        $filterRelates = $filter['relates'] ?? 'all';

        if (
            $row['box'] === $elementName && ($filterStatus == 'all' ||
                                            $filterStatus == $row['status']) && ($filterRelates == 'all' ||
                                                                                 $filterRelates == $row['relates'])
        ) {
            $comments = new \leantime\domain\repositories\comments();
            $nbcomments = $comments->countComments(moduleId: $row['id']);
            ?>

        <div class="ticketBox" id="item_<?php echo $row["id"];?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="inlineDropDownContainer" style="float:right;">

                        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                            <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                        <?php } ?>



                        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                            &nbsp;&nbsp;&nbsp;
                            <ul class="dropdown-menu">
                                <li class="nav-header"><?=$this->__("subtitles.edit"); ?></li>
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $row["id"];?>"
                                       class="<?=$canvasName ?>CanvasModal"
                                       data="item_<?php echo $row["id"];?>"> <?=$this->__("links.edit_canvas_item"); ?></a></li>
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $row["id"]; ?>"
                                       class="delete <?=$canvasName ?>CanvasModal"
                                       data="item_<?php echo $row["id"];?>"> <?=$this->__("links.delete_canvas_item"); ?></a></li>
                            </ul>
                        <?php } ?>
                    </div>

                    <h4><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?=$row["id"];?>"
                           class="<?=$canvasName ?>CanvasModal"
                           data="item_<?=$row['id'] ?>"><?php $this->e($row["description"]);?></a></h4>

                    <?php if ($row["conclusion"] != "") {
                        echo '<small>' . $this->convertRelativePaths($row["conclusion"]) . '</small>';
                    } ?>

                    <div class="clearfix" style="padding-bottom: 8px;"></div>

                    <?php if (!empty($statusLabels)) { ?>
                        <div class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                            <a class="dropdown-toggle f-left status label-<?=$statusLabels[$row['status']]['dropdown'] ?>"
                               href="javascript:void(0);" role="button"
                               id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text"><?=$statusLabels[$row['status']]['title'] ?></span> <i class="fa fa-caret-down"
                                                                                                          aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>
                                <?php foreach ($statusLabels as $key => $data) { ?>
                                    <?php if ($data['active'] || true) { ?>
                                        <li class='dropdown-item'>
                                            <a href="javascript:void(0);" class="label-<?=$data['dropdown'] ?>"
                                               data-label='<?=$data["title"] ?>' data-value="<?=$row['id'] . "/" . $key ?>"
                                               id="ticketStatusChange<?=$row['id'] . $key ?>"><?=$data['title'] ?></a>
                                        </li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>

                    <?php if (!empty($relatesLabels)) {  ?>
                        <div class="dropdown ticketDropdown relatesDropdown colorized show firstDropdown">
                            <a class="dropdown-toggle f-left relates label-<?=$relatesLabels[$row['relates']]['dropdown'] ?>"
                               href="javascript:void(0);" role="button"
                               id="relatesDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                <span class="text"><?=$relatesLabels[$row['relates']]['title'] ?></span> <i class="fa fa-caret-down"
                                                                                                                   aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="relatesDropdownMenuLink<?=$row['id']?>">
                                <li class="nav-header border"><?=$this->__("dropdown.choose_relates")?></li>
                                <?php foreach ($relatesLabels as $key => $data) { ?>
                                    <?php if ($data['active'] || true) { ?>
                                        <li class='dropdown-item'>
                                            <a href="javascript:void(0);" class="label-<?=$data['dropdown'] ?>"
                                               data-label='<?=$data["title"] ?>'
                                               data-value="<?=$row['id'] . "/" . $key ?>"
                                               id="ticketRelatesChange<?=$row['id'] . $key ?>"><?=$data['title'] ?></a>
                                        </li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>

                    <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                        <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="text">
                                <?php if ($row["authorFirstname"] != "") {
                                    echo "<span id='userImage" . $row['id'] . "'>" .
                                         "<img src='" . BASE_URL . "/api/users?profileImage=" . $row['author'] . "' width='25' " .
                                         "style='vertical-align: middle;'/></span><span id='user" . $row['id'] . "'></span>";
                                } else {
                                    echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL .
                                         "/api/users?profileImage=false' width='25' " .
                                         "style='vertical-align: middle;'/></span><span id='user" . $row['id'] . "'></span>";
                                } ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                            <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>
                            <?php foreach ($this->get('users') as $user) {
                                echo "<li class='dropdown-item'>" .
                                     "<a href='javascript:void(0);' data-label='" .
                                     sprintf(
                                         $this->__("text.full_name"),
                                         $this->escape($user["firstname"]),
                                         $this->escape($user['lastname'])
                                     ) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" .
                                     $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" .
                                     BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' " .
                                     "style='vertical-align: middle; margin-right:5px;'/>" .
                                     sprintf(
                                         $this->__("text.full_name"),
                                         $this->escape($user["firstname"]),
                                         $this->escape($user['lastname'])
                                     ) . "</a>";
                                echo"</li>";
                            }?>
                        </ul>
                    </div>
                    <div class="pull-right" style="margin-right:10px;">
                        <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasComment/<?=$row['id'] ?>"
                           class="<?=$canvasName ?>CanvasModal" data="item_<?=$row['id'] ?>"
                            <?php echo $nbcomments == 0 ? 'style="color: grey;"' : ''
                            ?>><span class="fas fa-comments"></span></a> <small><?=$nbcomments ?></small>
                    </div>
                </div>
            </div>

            <?php if ($row['milestoneHeadline'] != '') {?>
                <hr style="margin-top: 5px; margin-bottom: 5px;"/><small>
                    <div class="row">
                        <div class="col-md-5" >
                            <?php strlen($row['milestoneHeadline']) > 60 ?
                            $this->e(substr(($row['milestoneHeadline']), 0, 60) . " ...") :  $this->e($row['milestoneHeadline']); ?>
                        </div>
                        <div class="col-md-7" style="text-align:right">
                            <?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" role="progressbar"
                                     aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100"
                                     style="width: <?php echo $row['percentDone']; ?>%">
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
  <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
      <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem?type=<?php echo $elementName; ?>"
         class="<?=$canvasName ?>CanvasModal" id="<?php echo $elementName; ?>"
         style="padding-bottom: 10px;"><?=$this->__('links.add_new_canvas_item') ?></a>
  <?php } ?>
</div>
