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
    <div style="text-align: center">
        <input type="text" list="startOptions" wire:model="start" class="fancy-tailwind-things" x-ref="startInput"
            x-on:change.debounce="startSelected($event)">

        <datalist id="startOptions">
            @foreach ($searchResults as $result)
                <option wire:key="{{ $result['id'] }}" data-value="{{ $result['id'] }}" value="{{ $result['title'] }}">
                </option>
            @endforeach
        </datalist>

        <input type="text" list="endOptions" wire:model="end" class="fancy-tailwind-things" x-ref="endInput"
            x-on:change.debounce="endSelected($event)">

        <datalist id="endOptions">
            @foreach ($searchResults as $result)
                <option wire:key="{{ $result['id'] }}" data-value="{{ $result['id'] }}" value="{{ $result['title'] }}">
                </option>
            @endforeach
        </datalist>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Rechercher
        </button>
    </div>
</form>
<div  style="text-align: center">

        @foreach ($resultMessages as $message)
       @php
       print_r($message)
       @endphp 
       {{-- @dd($message) --}}
            <p>{{ $message }}</p>
        @endforeach

</div>

@push('scripts')
    <script>
        function reloadResultMessages() {
            // Effectuer une requête AJAX vers le serveur pour récupérer les messages mis à jour
            Livewire.emit('refreshResultMessages');
        }

        document.addEventListener('livewire:load', function () {
            // Recharger la div toutes les 5 secondes (5000 millisecondes)
            setInterval(reloadResultMessages, 5000);
        });
    </script>
@endpush


