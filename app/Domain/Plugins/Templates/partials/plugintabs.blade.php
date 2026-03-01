
<div class="maincontentinner tabs">
    <ul>
        <li class="{{ $currentUrl == 'marketplace' ? "active" : ""  }}">
            <a href="<?=BASE_URL ?>/plugins/marketplace">
                <x-global::elements.icon name="store" /> Explore Apps
            </a>
        </li>
        <li class="{{ $currentUrl == 'installed' ? "active" : ""  }}">
            <a href="<?=BASE_URL ?>/plugins/myapps">
                <x-global::elements.icon name="extension" /> My Apps
            </a>
        </li>
    </ul>
</div>
