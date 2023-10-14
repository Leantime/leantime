
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
        <li class="{{ $currentUrl == 'show' ? "active" : ""  }}" id="installCustomPlugin">
            <a href="<?=BASE_URL ?>/plugins/show">
                <i class="fa-solid fa-file-code"></i> Install Custom
            </a>
        </li>

    </ul>
</div>
