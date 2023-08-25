<div>
    @unless ($formSubmitted)

        <form wire:submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
            <div class="text-center">

                <div>
                    <label>
                        <input type="text" placeholder="Page de départ" list="startOptions"
                               wire:model.debounce.500ms="start"
                               class="rounded" x-ref="startInput">
                        <datalist id="startOptions">
                            @foreach ($startSearchResults as $result)
                                <option wire:key="{{ $result }}" data-value="{{ $result }}" value="{{ $result }}">
                                </option>
                            @endforeach
                        </datalist>
                    </label>
                    <label>
                        <input type="text" placeholder="Page d'arrivée" list="endOptions"
                               wire:model.debounce.500ms="end"
                               class="rounded" x-ref="endInput">
                        <datalist id="endOptions">
                            @foreach ($endSearchResults as $result)
                                <option wire:key="{{ $result }}" data-value="{{ $result }}" value="{{ $result }}">
                                </option>
                            @endforeach
                        </datalist>

                    </label>

                </div>
                <div>
                    <label>
                        <input type="checkbox" x-ref="allShortestPathsInput"
                               wire:model="allShortestPaths"/>
                        Afficher tous les chemins les plus rapides
                        (recherche plus longue).
                    </label>
                </div>
                <button type="submit" class="text-white font-bold  px-4 rounded" style="background-color: #03a696">
                    Rechercher
                </button>

            </div>
        </form>
    @endunless
</div>


