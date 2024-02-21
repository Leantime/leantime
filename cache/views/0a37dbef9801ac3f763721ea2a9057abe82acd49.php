<?php
    /**
     * @todo Move this to Composer, or find a better
     *       way to add filters for all passed variables
     */
    use Leantime\Domain\Auth\Models\Roles;
    $settingsLink = $tpl->dispatchTplFilter(
        'settingsLink',
        $settingsLink,
        ['type' => $menuType]
    );
?>

<?php if(isset($_SESSION['currentProjectName'])): ?>

    <?php $tpl->dispatchTplEvent('beforeMenu'); ?>

    <ul class="nav nav-tabs nav-stacked">

        <?php $tpl->dispatchTplEvent('afterMenuOpen'); ?>

        <?php if($allAvailableProjects || !empty($_SESSION['currentProject'])): ?>

            <li class="dropdown scrollableMenu">

                <ul style="display:block;">

                    <?php $__currentLoopData = $menuStructure; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <?php switch($menuItem['type']):
                            case ('header'): ?>
                                <li>
                                    <a href="javascript:void(0);">
                                        <strong><?php echo __($menuItem['title']); ?></strong>
                                    </a>
                                </li>
                                <?php break; ?>

                            <?php case ('separator'): ?>
                                <li class="separator"></li>
                                <?php break; ?>

                            <?php case ('item'): ?>
                                <li
                                    <?php if(
                                        $module == $menuItem['module']
                                        && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))
                                    ): ?>
                                        class="active"
                                    <?php endif; ?>
                                >
                                    <a href="<?php echo BASE_URL . $menuItem['href']; ?>">
                                        <?php echo __($menuItem['title']); ?>

                                    </a>
                                </li>
                                <?php break; ?>

                            <?php case ('submenu'): ?>
                                <li class="submenuToggle">
                                    <a href="javascript:void(0);"
                                       <?php if( $menuItem['visual'] !== 'always' ): ?>
                                           onclick="leantime.menuController.toggleSubmenu('<?php echo e($menuItem['id']); ?>')"
                                        <?php endif; ?>
                                    >
                                        <i class="submenuCaret fa fa-angle-<?php echo e($menuItem['visual'] == 'closed' ? 'right' : 'down'); ?>"
                                           id="submenu-icon-<?php echo e($menuItem['id']); ?>"></i>
                                        <strong><?php echo __($menuItem['title']); ?></strong>
                                    </a>
                                </li>
                                <ul id="submenu-<?php echo e($menuItem['id']); ?>" class="submenu <?php echo e($menuItem['visual'] == 'closed' ? 'closed' : 'open'); ?>">
                                    <?php $__currentLoopData = $menuItem['submenu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subkey => $submenuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php switch($submenuItem['type']):
                                            case ('header'): ?>
                                                <li class="title">
                                                    <a href="javascript:void(0);">
                                                        <strong><?php echo __($submenuItem['title']); ?></strong>
                                                    </a>
                                                </li>
                                                <?php break; ?>
                                            <?php case ('item'): ?>
                                                <li
                                                    <?php if(
                                                        $module == $submenuItem['module']
                                                        && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))
                                                    ): ?>
                                                        class='active'
                                                    <?php endif; ?>
                                                >
                                                    <a href="<?php echo e(BASE_URL . $submenuItem['href']); ?>"
                                                       data-tippy-content="<?php echo e(strip_tags(__($submenuItem['tooltip']))); ?>"
                                                       data-tippy-placement="right">
                                                        <?php echo __($submenuItem['title']); ?>

                                                    </a>
                                                </li>
                                        <?php endswitch; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <?php break; ?>
                        <?php endswitch; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if($login::userIsAtLeast(Roles::$manager) && $menuType != 'company' && $menuType != 'personal' && $menuType != 'projecthub'): ?>
                        <li class="fixedMenuPoint <?php echo e($module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : ''); ?>">
                            <a href="<?php echo e(BASE_URL); ?>/<?php echo e($settingsLink['module']); ?>/<?php echo e($settingsLink['action']); ?>/<?php echo e($_SESSION['currentProject']); ?>">
                                <?php echo $settingsLink['label']; ?>

                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if($menuType == 'personal'): ?>
                        <li class="fixedMenuPoint <?php echo e($module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : ''); ?>">
                            <a href="<?php if(isset($settingsLink['url'])): ?> <?php echo e($settingsLink['url']); ?> <?php else: ?> <?php echo e(BASE_URL); ?>/<?php echo e($settingsLink['module']); ?>/<?php echo e($settingsLink['action']); ?> <?php endif; ?>">
                                <?php echo __($settingsLink['label']); ?>

                            </a>
                        </li>
                    <?php endif; ?>


                </ul>

            </li>

        <?php endif; ?>

        <?php $tpl->dispatchTplEvent('beforeMenuClose'); ?>

    </ul>
    <?php $tpl->dispatchTplEvent('afterMenuClose'); ?>

<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('06c89986-c886-4ecb-a0c7-faa36f51bec1')): $__env->markAsRenderedOnce('06c89986-c886-4ecb-a0c7-faa36f51bec1'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            jQuery(document).ready(function () {
                leantime.menuController.initProjectSelector();
                leantime.menuController.initLeftMenuHamburgerButton();
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Menu/Templates/menu.blade.php ENDPATH**/ ?>