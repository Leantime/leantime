{{-- Standard floating tab nav (design call 2026-08-03) — the old
     .maincontentinner.tabs band CSS is gone, so this partial rides the
     shared lt-tabs structure like the ticket board tabs. --}}
<div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
    <nav class="lt-tabs-group" aria-label="Apps">
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
    </nav>
</div>
