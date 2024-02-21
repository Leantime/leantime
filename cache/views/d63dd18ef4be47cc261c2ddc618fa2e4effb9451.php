<?php use Leantime\Domain\Auth\Models\Roles; ?>
<?php $tpl->dispatchTplEvent('beforeHeadMenu'); ?>

<ul class="headmenu pull-right">
    <?php $tpl->dispatchTplEvent('insideHeadMenu'); ?>

    <?php echo $__env->make('timesheets::partials.stopwatch', [
               'progressSteps' => $onTheClock
           ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php if($login::userIsAtLeast("admin")): ?>
        <li class="appsLink">
            <a href="<?php echo e(BASE_URL); ?>/plugins/marketplace" data-tippy-content="<?php echo e(__('menu.leantime_apps_tooltip')); ?>"><span class="fa fa-puzzle-piece"></span></a>
        </li>
    <?php endif; ?>
    <li class="notificationDropdown">
        <a
            class="dropdown-toggle profileHandler newsDropDownHandler"
            hx-get="<?php echo e(BASE_URL); ?>/notifications/news/get"
            hx-target="#newsDropdown"
            hx-indicator=".htmx-indicator"
            hx-trigger="click"
            data-toggle='dropdown'
            data-tippy-content='<?php echo e(__('popover.notifications')); ?>'
        >
            <span class="fa-solid fa-bolt-lightning"></span>
            <span hx-get="<?php echo e(BASE_URL); ?>/notifications/news-badge/get" hx-trigger="load" hx-target="this"></span>

        </a>

        <div class='dropdown-menu tw-p-m' id='newsDropdown'>
            <div class="htmx-indicator">
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.loadingText','data' => ['type' => 'text','count' => '3','includeHeadline' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::loadingText'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','count' => '3','includeHeadline' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
        </div>
    </li>

    <li class="notificationDropdown">

        <a
            href='javascript:void(0);'
            class="dropdown-toggle profileHandler notificationHandler"
            data-toggle='dropdown'
            data-tippy-content='<?php echo e(__('popover.notifications')); ?>'
        >
            <span class="fa-solid fa-bell"></span>
            <?php if($newNotificationCount>0): ?>
                <span class='notificationCounter'><?php echo e($newNotificationCount); ?></span>
            <?php endif; ?>
        </a>

        <div class='dropdown-menu' id='notificationsDropdown'>

            <div class='dropdownTabs'>
                <a
                    href='javascript:void(0);'
                    class='notifcationTabs active'
                    id="notificationsListLink"
                    onclick="toggleNotificationTabs('notifications')"
                >Notification (<?php echo e($totalNewNotifications); ?>)</a>
                <a
                    href='javascript:void(0);'
                    class='notifcationTabs'
                    id="mentionsListLink"
                    onclick="toggleNotificationTabs('mentions')"
                >Mentions (<?php echo e($totalNewMentions); ?>)</a>
            </div>

            <div class="scroll-wrapper">

                <ul id='notificationsList' class='notifcationViewLists'>
                    <?php if($totalNotificationCount === 0): ?>
                        <p style='padding: 10px'><?php echo e(__('text.no_notifications')); ?></p>
                    <?php endif; ?>

                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($notif['type'] == 'mention'): ?>
                            <?php continue; ?>
                        <?php endif; ?>

                        <li
                            <?php if($notif['read'] == 0): ?>
                                class='new'
                            <?php endif; ?>
                            data-url="<?php echo e($notif['url']); ?>"
                            data-id="<?php echo e($notif['id']); ?>"
                        >
                            <a href="<?php echo e($notif['url']); ?>">
                                <span class="notificationProfileImage">
                                    <img src="<?php echo e(BASE_URL); ?>/api/users?profileImage=<?php echo e($notif['authorId']); ?>"/>
                                </span>
                                <span class="notificationDate">
                                    <?php echo e(format($notif['datetime'])->date()); ?>

                                    <?php echo e(format($notif['datetime'])->time()); ?>

                                </span>
                                <span class="notificationTitle"><?php echo $tpl->convertRelativePaths($notif['message']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <ul id='mentionsList' style='display:none;' class='notificationViewLists'>
                    <?php if($totalMentionCount === 0): ?>
                        <p style="padding: 10px"><?php echo e(__('text.no_notifications')); ?></p>
                    <?php endif; ?>

                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($notif['type'] != 'mention'): ?>
                            <?php continue; ?>
                        <?php endif; ?>

                        <li
                            <?php if($notif['read'] == 0): ?>
                                class='new'
                            <?php endif; ?>
                            data-url="<?php echo e($notif['url']); ?>"
                            data-id="<?php echo e($notif['id']); ?>"
                        >
                            <a href="<?php echo e($notif['url']); ?>">
                                <span class="notificationProfileImage">
                                    <img src="<?php echo e(BASE_URL); ?>/api/users?profileImage=<?php echo e($notif['authorId']); ?>"/>
                                </span>
                                <span class="notificationDate">
                                    <?php echo e(format($notif['datetime'])->date()); ?>

                                    <?php echo e(format($notif['datetime'])->time()); ?>

                                </span>
                                <span class="notificationTitle"><?php echo $tpl->convertRelativePaths($notif['message']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

            </div>
        </div>

    </li>

    <li>
        <div class="userloggedinfo">

            <?php echo $__env->make("auth::partials.loginInfo", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        </div>

        <?php $tpl->dispatchTplEvent('afterUser'); ?>

    </li>

    <?php $tpl->dispatchTplEvent('beforeHeadMenuClose'); ?>

</ul>

<ul class="headmenu">

    <?php $tpl->dispatchTplEvent('afterHeadMenuOpen'); ?>
    <li>
        <?php echo $__env->make('menu::projectSelector', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </li>
    <li>
        <a
            href="<?php echo e(BASE_URL); ?>/dashboard/home"
            <?php if($menuType == 'personal'): ?>
                class="active"
            <?php endif; ?>
            data-tippy-content="<?php echo e(__('popover.my_work')); ?>"
        ><?php echo __('menu.my_work'); ?></a>
    </li>
    <?php if($login::userIsAtLeast("manager")): ?>
        <li>
            <a
                href="<?php echo e(BASE_URL); ?>/setting/editCompanySettings/"
                <?php if($menuType == 'company'): ?>
                    class="active"
                <?php endif; ?>
                data-tippy-content="<?php echo e(__('popover.company')); ?>"
            ><?php echo __('menu.company'); ?></a>
        </li>
    <?php endif; ?>

</ul>



<?php $tpl->dispatchTplEvent('afterHeadMenu'); ?>

<?php if (! $__env->hasRenderedOnce('067cf5f7-63cc-4971-b334-3a4d67a1831c')): $__env->markAsRenderedOnce('067cf5f7-63cc-4971-b334-3a4d67a1831c'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            function toggleNotificationTabs(active) {
                jQuery(".notifcationTabs").removeClass("active");
                jQuery('#' + active + 'ListLink').addClass("active");
                jQuery('.notifcationViewLists').hide();
                jQuery('#' + active + 'List').show();
            }

            jQuery(document).ready(function () {
                jQuery('.notificationHandler').on('click', function () {
                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl + '/api/notifications',
                            data: {
                                id: 'all',
                                action: 'read'
                            }
                        }
                    ).done(function () {
                        jQuery(".notifcationViewLists li.new").removeClass("new");
                        jQuery(".notificationCounter").fadeOut();
                    })
                });

                jQuery('.notificationDropdown .dropdown-menu').on('click', function (e) {
                    e.stopPropagation();
                });

                jQuery('notificationsDropdown li').click(function () {
                    const url = jQuery(this).data('url');
                    const id = jQuery(this).data('id');

                    window.location.href = url;
                })
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Menu/Templates/headMenu.blade.php ENDPATH**/ ?>