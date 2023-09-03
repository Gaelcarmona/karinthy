    <div class="md:h-172 md:w-172 flex rounded p-3 items-center">
        <div class="md:w-1/2 md:pl-4 md:pt-0 md:pt-4 md:order-1">
            <img src="{{ asset('images/six_degrees_of_separation.svg.png') }}" alt="six_degrees_of_separation">
        </div>
        <div class="md:w-1/2 md:pr-4 md:pb-0 md:pb-4 md:order-2 mx-3 items-center">
            <p>Bienvenue sur notre site dédié à l'exploration fascinante des liens qui unissent le vaste univers de
                la connaissance. Inspiré par la théorie des <a style="color: #03a696" class="navbar-brand text-white"
                    href="https://fr.wikipedia.org/wiki/Six_degrés_de_séparation" target="_blank">six degrés de
                    séparation</a> de <a style="color: #03a696" class="navbar-brand text-white"
                    href="https://fr.wikipedia.org/wiki/Frigyes_Karinthy" target="_blank">Frigyes
                    Karinthy</a>,
                notre
                plateforme vous invite à découvrir la magie qui se cache derrière chaque clic. Parcourez les
                méandres des pages Wikipedia et laissez-vous émerveiller par les connexions qui relient des personnages,
                des lieux, des concepts et bien plus encore.</p>
            <br>
            <p>Notre objectif est simple : vous offrir un moyen captivant et ludique de mettre en lumière les
                chemins inattendus qui tissent la toile de la connaissance. Plongez dans le défi des wikispeedruns,
                explorez les méta-recherches et prouvez par vous-même la théorie des six degrés de séparation en
                découvrant comment chaque coin de la vaste encyclopédie en ligne est interconnecté.</p>
            <br>
            <p>Rejoignez-nous dans cette aventure où chaque clic vous rapproche davantage de la découverte, où
                chaque lien vous guide vers de nouvelles perspectives. Bienvenue sur notre plateforme d'exploration
                et de jeu, où la curiosité est votre guide et où la découverte est votre récompense.</p>
            <br>
            <form wire:submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                <div class="text-center">
                    <div class="flex flex-col space-y-4">
                        <div class="flex items-center mx-3">
                            <div class="flex-grow h-full w-40">
                                <input type="text"
                                    class="bg-gray-100 focus:bg-white border-solid border-2 border-black rounded-tl rounded-bl w-full text-xl"
                                    placeholder="Page de départ" wire:model.debounce.250ms="start">
                                @if (!empty($startSearchResults))
                                    <div class="relative">
                                        <div class="absolute top-100 mt-1 w-full border bg-white shadow-xl rounded">
                                            <ul class="p-3">
                                                @foreach ($startSearchResults as $result)
                                                    @php
                                                        $resultWithoutLashes = str_replace("'", '.', $result);
                                                    @endphp
                                                    <li>
                                                        <p class="p-2 flex block w-full rounded hover:bg-gray-100"
                                                            wire:click="selectStartResult('{{ $resultWithoutLashes }}')">
                                                            {{ $result }}
                                                        </p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button"
                                class="border-solid border-2 border-black h-full bg-white rounded-tr rounded-br"
                                wire:click="shuffleStart">
                                <img src="{{ asset('images/shuffle-svgrepo-com.svg') }}" alt="shuffle">
                            </button>
                        </div>
                        <div class="flex items-center mx-3">
                            <div class="flex-grow h-full w-40">
                                <input type="text"
                                    class="bg-gray-100 focus:bg-white border-solid border-2 border-black rounded-tl rounded-bl w-full text-xl"
                                    placeholder="Page d'arrivée" wire:model.debounce.250ms="end">
                                @if (!empty($endSearchResults))
                                    <div class="relative">
                                        <div class="absolute top-100 mt-1 w-full border bg-white shadow-xl rounded">
                                            <ul class="p-3">
                                                @foreach ($endSearchResults as $result)
                                                    @php
                                                        $resultWithoutLashes = str_replace("'", '.', $result);
                                                    @endphp
                                                    <li>
                                                        <p class="p-2 flex block w-full rounded hover:bg-gray-100"
                                                            wire:click="selectEndResult('{{ $resultWithoutLashes }}')">
                                                            {{ $result }}
                                                        </p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button"
                                class="border-solid border-2 border-black h-full bg-white rounded-tr rounded-br"
                                wire:click="shuffleEnd">
                                <img src="{{ asset('images/shuffle-svgrepo-com.svg') }}" alt="shuffle">
                            </button>
                        </div>
                    </div>
                    @if (!$formSubmitted)
                        <button type="submit" class="text-white font-bold  px-4 rounded my-3 search-button"
                            style="background-color: #03a696">
                            Rechercher
                        </button>
                    @else
                        <a href="{{ back()->getTargetUrl() }}" class="text-white font-bold  px-4 rounded my-3"
                            style="background-color: #03a696">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
