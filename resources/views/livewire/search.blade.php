<form wire:submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
    <div class="text-center">

        <input type="text" list="startOptions" wire:model.debounce.2000ms="start" class="rounded" x-ref="startInput">
        <datalist id="startOptions">
            @foreach ($searchResults as $result)
                <option wire:key="{{ $result }}" data-value="{{ $result }}" value="{{ $result }}">
                </option>
            @endforeach
        </datalist>

        <input type="text" list="endOptions" wire:model.debounce.2000ms="end" class="rounded" x-ref="endInput">
        <datalist id="endOptions">
            @foreach ($searchResults as $result)
                <option wire:key="{{ $result }}" data-value="{{ $result }}" value="{{ $result }}">
                </option>
            @endforeach
        </datalist>

        <button type="submit" class="text-white font-bold py-2 px-4 rounded" style="background-color: #03a696">
            Rechercher
        </button>

    </div>
</form>


