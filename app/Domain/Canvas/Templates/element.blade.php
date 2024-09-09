<?php

use Leantime\Domain\Comments\Repositories\Comments;
$canvasTypes = $tpl->get('canvasTypes');
$canvasItems = $tpl->get('canvasItems');
?>
<h4 class="widgettitle title-primary">
    <?php if (isset($canvasTypes[$elementName]['icon'])) {
        echo '<i class="fas ' . $canvasTypes[$elementName]['icon'] . '"></i> ';
    }
    ?><?= $canvasTypes[$elementName]['title'] ?>
</h4>
<div class="contentInner even status_<?php echo $elementName; ?>"
    <?= isset($canvasTypes[$elementName]['color']) ? 'style="background: ' . $canvasTypes[$elementName]['color'] . ';"' : '' ?>>

    <?php foreach ($canvasItems as $row) {
        $filterStatus = $filter['status'] ?? 'all';
        $filterRelates = $filter['relates'] ?? 'all';

        if (
            $row['box'] === $elementName && ($filterStatus == 'all' ||
                                            $filterStatus == $row['status']) && ($filterRelates == 'all' ||
                                                                                 $filterRelates == $row['relates'])
        ) {
            $comments = app()->make(Comments::class);
            $nbcomments = $comments->countComments(moduleId: $row['id']);
            ?>

    <div class="ticketBox" id="item_<?php echo $row['id']; ?>">
        <div class="row">
            <div class="col-md-12">
                <div>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-global::content.context-menu label-text="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>"
                            contentRole="link" position="bottom" align="start">
                            <li class="nav-header">{{ __('subtitles.edit') }}</li>
                            <x-global::actions.dropdown.item variant="link"
                                href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}"
                                data="item_{{ $row['id'] }}">
                                {{ __('links.edit_canvas_item') }}
                            </x-global::actions.dropdown.item>
                            <x-global::actions.dropdown.item variant="link"
                                href="#/{{ $canvasName }}canvas/delCanvasItem/{{ $row['id'] }}" class="delete"
                                data="item_{{ $row['id'] }}">
                                {{ __('links.delete_canvas_item') }}
                            </x-global::actions.dropdown.item>
                        </x-global::content.context-menu>
                    @endif
                </div>


                <h4><a href="#/<?= $canvasName ?>canvas/editCanvasItem/<?= $row['id'] ?>"
                        data="item_<?= $row['id'] ?>"><?php $tpl->e($row['description']); ?></a></h4>

                <?php if ($row['conclusion'] != '') {
                    echo '<small>' . $tpl->convertRelativePaths($row['conclusion']) . '</small>';
                } ?>

                <div class="clearfix" style="padding-bottom: 8px;"></div>

                <?php if (!empty($statusLabels)) { ?>
                <x-global::actions.dropdown
                    label-text="<span class='text'>{{ $statusLabels[$row['status']]['title'] }}</span> <i class='fa fa-caret-down' aria-hidden='true'></i>"
                    contentRole="link" position="bottom" align="start" :class="'ticketDropdown statusDropdown colorized show firstDropdown'">

                    <x-slot:menu>
                        <!-- Header Item -->
                        <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>

                        <!-- Dynamic Status Menu Items -->
                        @foreach ($statusLabels as $key => $data)
                            @if ($data['active'] || true)
                                <x-global::actions.dropdown.item variant="link" href="javascript:void(0);"
                                    class="label-{{ $data['dropdown'] }}" :data-label="$data['title']" :data-value="$row['id'] . '/' . $key"
                                    :id="'ticketStatusChange' . $row['id'] . $key">
                                    {{ $data['title'] }}
                                </x-global::actions.dropdown.item>
                            @endif
                        @endforeach
                    </x-slot:menu>
                </x-global::actions.dropdown>

                <?php } ?>

                <?php if (!empty($relatesLabels)) {  ?>
                <x-global::actions.dropdown
                    label-text="<span class='text'>{{ $relatesLabels[$row['relates']]['title'] }}</span> <i class='fa fa-caret-down' aria-hidden='true'></i>"
                    contentRole="link" position="bottom" align="start" :class="'ticketDropdown relatesDropdown colorized show firstDropdown'">

                    <x-slot:menu>
                        <!-- Header Item -->
                        <li class="nav-header border">{{ __('dropdown.choose_relates') }}</li>

                        <!-- Dynamic Relates Menu Items -->
                        @foreach ($relatesLabels as $key => $data)
                            @if ($data['active'] || true)
                                <x-global::actions.dropdown.item variant="link" href="javascript:void(0);"
                                    class="label-{{ $data['dropdown'] }}" :data-label="$data['title']" :data-value="$row['id'] . '/' . $key"
                                    :id="'ticketRelatesChange' . $row['id'] . $key">
                                    {{ $data['title'] }}
                                </x-global::actions.dropdown.item>
                            @endif
                        @endforeach
                    </x-slot:menu>
                </x-global::actions.dropdown>

                <?php } ?>
                <x-global::actions.dropdown
                    label-text="
                    <span class='text'>
                        @if ($row['authorFirstname'] != '')
<span id='userImage{{ $row['id'] }}'>
                                <img src='{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}' width='25' style='vertical-align: middle;' />
                            </span>
                            <span id='user{{ $row['id'] }}'></span>
@else
<span id='userImage{{ $row['id'] }}'>
                                <img src='{{ BASE_URL }}/api/users?profileImage=false' width='25' style='vertical-align: middle;' />
                            </span>
                            <span id='user{{ $row['id'] }}'></span>
@endif
                    </span>"
                    contentRole="link" position="bottom" align="start" :class="'ticketDropdown userDropdown noBg show right lastDropdown dropRight'">

                    <x-slot:menu>
                        <!-- Header Item -->
                        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>

                        <!-- Dynamic User Menu Items -->
                        @foreach ($tpl->get('users') as $user)
                            <x-global::actions.dropdown.item variant="link" href="javascript:void(0);" :data-label="sprintf(
                                __('text.full_name'),
                                $tpl->escape($user['firstname']),
                                $tpl->escape($user['lastname']),
                            )"
                                :data-value="$row['id'] . '_' . $user['id'] . '_' . $user['profileId']" :id="'userStatusChange' . $row['id'] . $user['id']">
                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}&v={{ $user['modified'] }}"
                                    width="25" style="vertical-align: middle; margin-right:5px;" />
                                {{ sprintf(__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                            </x-global::actions.dropdown.item>
                        @endforeach
                    </x-slot:menu>
                </x-global::actions.dropdown>

                <div class="pull-right" style="margin-right:10px;">
                    <a href="#/<?= $canvasName ?>canvas/editCanvasComment/<?= $row['id'] ?>" class="commentCountLink"
                        data="item_<?= $row['id'] ?>"> <span class="fas fa-comments"></span></a>
                    <small><?= $nbcomments ?></small>
                </div>
            </div>
        </div>

        <?php if ($row['milestoneHeadline'] != '') {?>
        <br />
        <div hx-trigger="load" hx-indicator=".htmx-indicator"
            hx-get="<?= BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?= $row['milestoneId'] ?>">

            <div class="htmx-indicator">
                <?= $tpl->__('label.loading_milestone') ?>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    <?php } ?>
    <br />
    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
    <a href="#/<?= $canvasName ?>canvas/editCanvasItem?type=<?php echo $elementName; ?>" class=""
        id="<?php echo $elementName; ?>" style="padding-bottom: 10px;"><?= $tpl->__('links.add_new_canvas_item') ?></a>
    <?php } ?>
</div>
