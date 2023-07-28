<div>
    <h2>Résultats de la recherche :</h2>
    <ul>
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

    <!-- Écouter l'événement pour mettre à jour la vue -->
    <script>
        Livewire.on('resultMessagesUpdated', messages => {
            @this.set('resultMessages', messages);
        });
    </script>
</div>
