<form wire:submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
    <div class="text-center">

        <input type="text" placeholder="Page de départ" list="startOptions" wire:model.debounce.500ms="start" class="rounded" x-ref="startInput">
        <datalist id="startOptions">
            @foreach ($startSearchResults as $result)
                <option wire:key="{{ $result }}" data-value="{{ $result }}" value="{{ $result }}">
                </option>
            @endforeach
        </datalist>

        <input type="text" placeholder="Page d'arrivée" list="endOptions" wire:model.debounce.500ms="end" class="rounded" x-ref="endInput">
        <datalist id="endOptions">
            @foreach ($endSearchResults as $result)
                <option wire:key="{{ $result }}" data-value="{{ $result }}" value="{{ $result }}">
                </option>
            @endforeach
        </datalist>

        <button type="submit" class="text-white font-bold  px-4 rounded" style="background-color: #03a696">
            Rechercher
        </button>

    </div>
</form>


