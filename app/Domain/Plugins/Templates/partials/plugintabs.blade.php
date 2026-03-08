
<x-globals::navigation.tabs>
    <x-globals::navigation.tab
        label="Explore Apps"
        href="{{ BASE_URL }}/plugins/marketplace"
        icon="store"
        :active="$currentUrl == 'marketplace'" />
    <x-globals::navigation.tab
        label="My Apps"
        href="{{ BASE_URL }}/plugins/myapps"
        icon="extension"
        :active="$currentUrl == 'installed'" />
</x-globals::navigation.tabs>
