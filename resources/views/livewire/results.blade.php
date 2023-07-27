<div>
    <h2>Résultats de la recherche :</h2>
    <ul>
        @if ($this->resultMessages)
            @foreach ($this->resultMessages as $message)
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