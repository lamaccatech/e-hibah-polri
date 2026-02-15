<div>
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="subtle" square aria-label="{{ __('component.notification.aria-label') }}" class="relative">
            <flux:icon.bell variant="mini" />
            @if ($unreadCount > 0)
                <span class="absolute -top-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-medium text-white">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80">
            <div class="flex items-center justify-between px-3 py-2">
                <flux:heading size="sm">{{ __('component.notification.title') }}</flux:heading>
                @if ($unreadCount > 0)
                    <flux:button variant="ghost" size="sm" wire:click="markAllAsRead">
                        {{ __('component.notification.mark-all-read') }}
                    </flux:button>
                @endif
            </div>

            <flux:menu.separator />

            @forelse ($notifications as $notification)
                <flux:menu.item
                    wire:key="{{ $notification->id }}"
                    :href="$this->getUrl($notification)"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    wire:navigate
                    class="{{ $notification->read_at ? 'opacity-60' : '' }}"
                >
                    <div class="flex flex-col gap-0.5">
                        <span class="text-sm">
                            {{ $notification->data['unit_name'] ?? '' }}
                            {{ __('component.notification.submitted-planning') }}
                            <span class="font-medium">{{ $notification->data['grant_name'] ?? '' }}</span>
                        </span>
                        <flux:text size="xs">{{ $notification->created_at->diffForHumans() }}</flux:text>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-4 text-center">
                    <flux:text size="sm">{{ __('component.notification.empty') }}</flux:text>
                </div>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>
