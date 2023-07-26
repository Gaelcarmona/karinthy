<div>
    <h2>Résultats de la recherche :</h2>
    <ul>
        @foreach ($resultMessages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>