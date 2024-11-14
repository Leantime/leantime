
<div class="maincontentinner tabs">
    <ul>
        <li class="{{ $currentUrl == 'marketplace' ? "active" : ""  }}">
            <a href="<?=BASE_URL ?>/plugins/marketplace">
                <i class="fa-solid fa-store"></i> Explore Apps
            </a>
        </li>
        <li class="{{ $currentUrl == 'installed' ? "active" : ""  }}">
            <a href="<?=BASE_URL ?>/plugins/myapps">
                <i class="fa-solid fa-puzzle-piece"></i> My Apps
            </a>
        </li>
    </ul>
</div>
