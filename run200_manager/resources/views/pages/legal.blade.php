{{--
    Mentions Légales - RUN200 Manager
    Conformité : Loi pour la Confiance dans l'Économie Numérique (LCEN)
--}}
<x-layouts.racing-public title="Mentions Légales - RUN200 Manager">

    <div class="py-12 lg:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-racing-red-500/10 dark:bg-racing-red-500/20 rounded-full text-racing-red-600 dark:text-racing-red-400 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm font-semibold">Informations légales</span>
                </div>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-carbon-900 dark:text-white mb-4">
                    Mentions Légales
                </h1>
                <p class="text-carbon-500 dark:text-carbon-400">
                    Dernière mise à jour : {{ now()->format('d/m/Y') }}
                </p>
            </div>

            {{-- Content --}}
            <div class="racing-card p-8 lg:p-12 space-y-10">

                {{-- Section 1: Éditeur du site --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">1</span>
                        </div>
                        Éditeur du site
                    </h2>
                    <div class="pl-11 space-y-3 text-carbon-600 dark:text-carbon-400">
                        <p>
                            Le site <strong class="text-carbon-900 dark:text-white">RUN200 Manager</strong> (ci-après « le Site ») est édité par :
                        </p>
                        <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4 space-y-2">
                            <p><strong class="text-carbon-900 dark:text-white">ASA CFG</strong> (Association Sportive Automobile – Circuit Félix Guichard)</p>
                            <p>Association loi 1901</p>
                            <p>Affiliée à la <strong>FFSA</strong> (Fédération Française du Sport Automobile)</p>
                            <p>Et à la <strong>LSAR</strong> (Ligue du Sport Automobile de La Réunion)</p>
                            <p>Siège social : La Réunion (974), France</p>
                        </div>
                        <p>
                            <strong class="text-carbon-900 dark:text-white">Contact :</strong>
                            <a href="https://cfg.re/#contactCfg" target="_blank" rel="noopener" class="text-racing-red-600 dark:text-racing-red-400 hover:underline">
                                https://cfg.re/#contactCfg
                            </a>
                        </p>
                    </div>
                </section>

                {{-- Section 2: Directeur de la publication --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">2</span>
                        </div>
                        Directeur de la publication
                    </h2>
                    <div class="pl-11 text-carbon-600 dark:text-carbon-400">
                        <p>Le directeur de la publication est le Président de l'ASA CFG.</p>
                    </div>
                </section>

                {{-- Section 3: Hébergement --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">3</span>
                        </div>
                        Hébergement
                    </h2>
                    <div class="pl-11 space-y-3 text-carbon-600 dark:text-carbon-400">
                        <p>Le Site est hébergé par :</p>
                        <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4 space-y-2">
                            <p><strong class="text-carbon-900 dark:text-white">OVH SAS</strong></p>
                            <p>2, rue Kellermann – 59100 Roubaix – France</p>
                            <p>RCS Lille Métropole 424 761 419 00045</p>
                            <p>Téléphone : +33 9 72 10 10 07</p>
                            <p>Site web : <a href="https://www.ovh.com" target="_blank" rel="noopener" class="text-racing-red-600 dark:text-racing-red-400 hover:underline">www.ovh.com</a></p>
                        </div>
                    </div>
                </section>

                {{-- Section 4: Propriété intellectuelle --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">4</span>
                        </div>
                        Propriété intellectuelle
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>
                            L'ensemble du contenu du Site (textes, images, graphismes, logo, icônes, logiciels, base de données, etc.) est protégé par le droit de la propriété intellectuelle, notamment par les dispositions du Code de la propriété intellectuelle.
                        </p>
                        <p>
                            La marque <strong class="text-carbon-900 dark:text-white">RUN200</strong>, le logo et les signes distinctifs associés sont la propriété exclusive de l'ASA CFG. Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments du Site est interdite sans autorisation écrite préalable.
                        </p>
                        <p>
                            Toute exploitation non autorisée du Site ou de l'un des éléments qu'il contient sera considérée comme constitutive d'une contrefaçon et poursuivie conformément aux dispositions des articles L.335-2 et suivants du Code de la Propriété Intellectuelle.
                        </p>
                    </div>
                </section>

                {{-- Section 5: Limitation de responsabilité --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">5</span>
                        </div>
                        Limitation de responsabilité
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>
                            L'ASA CFG s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur le Site. Cependant, elle ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition.
                        </p>
                        <p>
                            L'ASA CFG décline toute responsabilité :
                        </p>
                        <ul class="list-disc list-inside space-y-2 ml-4">
                            <li>Pour toute interruption du Site</li>
                            <li>Pour toute survenance de bogues</li>
                            <li>Pour tout dommage résultant d'une intrusion frauduleuse d'un tiers ayant entraîné une modification des informations mises à disposition sur le Site</li>
                            <li>Et plus généralement, pour tout dommage direct ou indirect, quelle qu'en soit la cause, l'origine, la nature ou les conséquences</li>
                        </ul>
                    </div>
                </section>

                {{-- Section 6: Liens hypertextes --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">6</span>
                        </div>
                        Liens hypertextes
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>
                            Le Site peut contenir des liens hypertextes vers d'autres sites. L'ASA CFG n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu.
                        </p>
                        <p>
                            La création de liens hypertextes vers le Site est soumise à l'accord préalable de l'ASA CFG.
                        </p>
                    </div>
                </section>

                {{-- Section 7: Droit applicable --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">7</span>
                        </div>
                        Droit applicable et juridiction compétente
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>
                            Les présentes mentions légales sont régies par le droit français. En cas de litige, les tribunaux français seront seuls compétents.
                        </p>
                    </div>
                </section>

                {{-- Section 8: Crédits --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">8</span>
                        </div>
                        Crédits
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>
                            <strong class="text-carbon-900 dark:text-white">Conception et développement :</strong> ASA CFG
                        </p>
                        <p>
                            <strong class="text-carbon-900 dark:text-white">Technologies utilisées :</strong> Laravel, Livewire, TailwindCSS
                        </p>
                    </div>
                </section>

            </div>

            {{-- Navigation --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('privacy') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-carbon-100 dark:bg-carbon-800 hover:bg-carbon-200 dark:hover:bg-carbon-700 text-carbon-700 dark:text-carbon-300 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Politique de confidentialité
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-racing-red-500 hover:bg-racing-red-600 text-white rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Retour à l'accueil
                </a>
            </div>

        </div>
    </div>

</x-layouts.racing-public>
