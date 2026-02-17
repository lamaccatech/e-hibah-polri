<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.tag-management.title') }}</flux:heading>

    <form wire:submit="create" class="mb-6 flex items-end gap-3">
        <div class="w-1/3">
            <flux:field>
                <flux:label>{{ __('page.tag-management.label-name') }}</flux:label>
                <flux:input wire:model="name" placeholder="{{ __('page.tag-management.placeholder-name') }}" />
                <flux:error name="name" />
            </flux:field>
        </div>

        <flux:button type="submit" variant="primary" icon="plus">
            {{ __('page.tag-management.create-button') }}
        </flux:button>
    </form>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.tag-management.column-name') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.tag-management.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($tags as $tag)
                <flux:table.row :key="$tag->id">
                    <flux:table.cell>{{ $tag->name }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="startEdit({{ $tag->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="2" class="text-center">
                        {{ __('page.tag-management.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="showEditModal" class="min-w-[22rem]">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.tag-management.edit-modal-title') }}</flux:heading>
            </div>

            <flux:field>
                <flux:label>{{ __('page.tag-management.label-name') }}</flux:label>
                <flux:input wire:model="editingName" />
                <flux:error name="editingName" />
            </flux:field>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    {{ __('common.save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
