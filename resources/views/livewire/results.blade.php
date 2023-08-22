<div class="text-center">
{{--    <h2>Résultats de la recherche :</h2>--}}
    <ul style="list-style-type: none;">
        @if ($resultMessages)
            @foreach ($resultMessages as $message)
                <li>{{ $message }}</li>
            @endforeach
        @else
            <li>Aucun résultat pour le moment.</li>
        @endif
    </ul>

    <div wire:loading>
        Chargement en cours...
    </div>
</div>
