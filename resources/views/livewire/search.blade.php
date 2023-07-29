<form wire:submit.prevent="submit" class="space-y-8 divide-y divide-gray-200"
    x-data='{
        startSelected(e) {
            let value = e.target.value
            let id = document.body.querySelector("datalist [value=\""+value+"\"]").dataset.value

            // todo: Do something interesting with this
            console.log(id);
        }
        endSelected(e) {
            let value = e.target.value
            let id = document.body.querySelector("datalist [value=\""+value+"\"]").dataset.value

            // todo: Do something interesting with this
            console.log(id);
        }
    }'
    >
    <div class="text-center">
        <input type="text" list="startOptions" wire:model="start" class="rounded" x-ref="startInput"
            x-on:change.debounce="startSelected($event)">

        <datalist id="startOptions">
            @foreach ($searchResults as $result)
                <option wire:key="{{ $result['id'] }}" data-value="{{ $result['id'] }}" value="{{ $result['title'] }}">
                </option>
            @endforeach
        </datalist>

        <input type="text" list="endOptions" wire:model="end" class="rounded" x-ref="endInput"
            x-on:change.debounce="endSelected($event)">

        <datalist id="endOptions">
            @foreach ($searchResults as $result)
                <option wire:key="{{ $result['id'] }}" data-value="{{ $result['id'] }}" value="{{ $result['title'] }}">
                </option>
            @endforeach
        </datalist>
        <button type="submit" class="text-white font-bold py-2 px-4 rounded" style="background-color: #03a696">
            Rechercher
        </button>
    </div>
</form>


