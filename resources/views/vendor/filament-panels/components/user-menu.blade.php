@php
$user = filament()->auth()->user();
$items = filament()->getUserMenuItems();

$profileItem = $items['profile'] ?? $items['account'] ?? null;
$profileItemUrl = $profileItem?->getUrl();
$profilePage = filament()->getProfilePage();
$hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

$logoutItem = $items['logout'] ?? null;

$items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown
    placement="bottom-end"
    teleport
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-menu'])
    ">
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="shrink-0">
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

    @if ($hasProfileItem)
    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item
            :color="$profileItem?->getColor()"
            :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
            :href="$profileItemUrl ?? filament()->getProfileUrl()"
            :target="($profileItem?->shouldOpenUrlInNewTab() ?? false) ? '_blank' : null"
            tag="a">
            {{ $profileItem?->getLabel() ?? ($profilePage ? $profilePage::getLabel() : null) ?? filament()->getUserName($user) }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
    @else
    <x-filament::dropdown.header
        :color="$profileItem?->getColor()"
        :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'">
        {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
    </x-filament::dropdown.header>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
    <x-filament::dropdown.list>
        <x-filament-panels::theme-switcher />
    </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
        @php
        $itemPostAction = $item->getPostAction();
        @endphp

        <x-filament::dropdown.list.item
            :action="$itemPostAction"
            :color="$item->getColor()"
            :href="$item->getUrl()"
            :icon="$item->getIcon()"
            :method="filled($itemPostAction) ? 'post' : null"
            :tag="filled($itemPostAction) ? 'form' : 'a'"
            :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null">
            {{ $item->getLabel() }}
        </x-filament::dropdown.list.item>
        @endforeach

        <x-filament::dropdown.list.item
            :color="$logoutItem?->getColor()"
            :icon="$logoutItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.logout-button') ?? 'heroicon-m-arrow-left-on-rectangle'"
            tag="button"
            x-on:click.prevent="$dispatch('open-modal', { id: 'logout-confirmation' })">
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
</x-filament::dropdown>

<x-filament::modal
    id="logout-confirmation"
    icon="heroicon-o-exclamation-triangle"
    icon-color="warning"
    width="lg"
    alignment="center"
>
    <x-slot name="heading">
        {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
    </x-slot>

    <x-slot name="description">
        {{ __('filament-panels::layout.actions.modal.description.label') ?? 'Are you sure you want to sign out?' }}
    </x-slot>

    <x-slot name="footer">
        <form method="POST" action="{{ $logoutItem?->getUrl() ?? filament()->getLogoutUrl() }}">
            @csrf
            <div class="flex gap-2 w-full">
                <x-filament::button
                    color="gray"
                    class="w-full"
                    x-on:click="$dispatch('close-modal', { id: 'logout-confirmation' })">
                    {{ __('filament-panels::layout.actions.modal.cancel.label') ?? 'Cancel' }}
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    class="w-full">
                    {{ __('filament-panels::layout.actions.modal.submit.label') ?? 'Log out' }}
                </x-filament::button>
            </div>
        </form>

    </x-slot>
</x-filament::modal>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}