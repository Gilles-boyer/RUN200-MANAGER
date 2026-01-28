{{--
    Politique de Confidentialité - RUN200 Manager
    Conformité : RGPD (Règlement UE 2016/679) et Loi Informatique et Libertés
--}}
<x-layouts.racing-public title="Politique de Confidentialité - RUN200 Manager">

    <div class="py-12 lg:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-racing-red-500/10 dark:bg-racing-red-500/20 rounded-full text-racing-red-600 dark:text-racing-red-400 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="text-sm font-semibold">Protection des données</span>
                </div>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-carbon-900 dark:text-white mb-4">
                    Politique de Confidentialité
                </h1>
                <p class="text-carbon-500 dark:text-carbon-400">
                    Dernière mise à jour : {{ now()->format('d/m/Y') }}
                </p>
            </div>

            {{-- Content --}}
            <div class="racing-card p-8 lg:p-12 space-y-10">

                {{-- Introduction --}}
                <section class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-blue-700 dark:text-blue-300">
                            <p class="font-semibold mb-2">Engagement RGPD</p>
                            <p class="text-sm">
                                L'ASA CFG s'engage à protéger la vie privée des utilisateurs de RUN200 Manager conformément au Règlement Général sur la Protection des Données (RGPD - UE 2016/679) et à la loi Informatique et Libertés modifiée.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Section 1: Responsable du traitement --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">1</span>
                        </div>
                        Responsable du traitement
                    </h2>
                    <div class="pl-11 space-y-3 text-carbon-600 dark:text-carbon-400">
                        <p>Le responsable du traitement des données personnelles est :</p>
                        <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4 space-y-2">
                            <p><strong class="text-carbon-900 dark:text-white">ASA CFG</strong> (Association Sportive Automobile – Circuit Félix Guichard)</p>
                            <p>Siège social : La Réunion (974), France</p>
                            <p>Contact : <a href="https://cfg.re/#contactCfg" target="_blank" rel="noopener" class="text-racing-red-600 dark:text-racing-red-400 hover:underline">https://cfg.re/#contactCfg</a></p>
                        </div>
                    </div>
                </section>

                {{-- Section 2: Données collectées --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">2</span>
                        </div>
                        Données personnelles collectées
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>Dans le cadre de l'utilisation de RUN200 Manager, nous collectons les données suivantes :</p>

                        <div class="space-y-4">
                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <h3 class="font-semibold text-carbon-900 dark:text-white mb-2">Données d'identification</h3>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    <li>Nom et prénom</li>
                                    <li>Date et lieu de naissance</li>
                                    <li>Adresse postale</li>
                                    <li>Numéro de téléphone</li>
                                    <li>Adresse email</li>
                                    <li>Photo d'identité (optionnelle)</li>
                                </ul>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <h3 class="font-semibold text-carbon-900 dark:text-white mb-2">Données sportives</h3>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    <li>Numéro de licence FFSA</li>
                                    <li>Informations sur les véhicules (marque, modèle, numéro de course)</li>
                                    <li>Historique des inscriptions aux courses</li>
                                    <li>Résultats sportifs et classements</li>
                                </ul>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <h3 class="font-semibold text-carbon-900 dark:text-white mb-2">Données de paiement</h3>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    <li>Historique des transactions</li>
                                    <li>Les données bancaires sont traitées exclusivement par notre prestataire de paiement sécurisé (Stripe) et ne sont jamais stockées sur nos serveurs</li>
                                </ul>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <h3 class="font-semibold text-carbon-900 dark:text-white mb-2">Données techniques</h3>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    <li>Adresse IP</li>
                                    <li>Données de connexion et de navigation</li>
                                    <li>Type de navigateur et appareil utilisé</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Section 3: Finalités du traitement --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">3</span>
                        </div>
                        Finalités du traitement
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>Vos données personnelles sont collectées et traitées pour les finalités suivantes :</p>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-carbon-100 dark:bg-carbon-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white rounded-tl-lg">Finalité</th>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white rounded-tr-lg">Base légale</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
                                    <tr>
                                        <td class="px-4 py-3">Gestion des inscriptions aux courses</td>
                                        <td class="px-4 py-3">Exécution du contrat</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Vérification administrative et technique</td>
                                        <td class="px-4 py-3">Exécution du contrat / Obligation légale FFSA</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Gestion des paiements</td>
                                        <td class="px-4 py-3">Exécution du contrat</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Publication des résultats sportifs</td>
                                        <td class="px-4 py-3">Intérêt légitime / Obligation réglementaire FFSA</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Gestion du championnat et classements</td>
                                        <td class="px-4 py-3">Exécution du contrat</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Communication d'informations sur les courses</td>
                                        <td class="px-4 py-3">Intérêt légitime</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Sécurité du site et prévention des fraudes</td>
                                        <td class="px-4 py-3">Intérêt légitime</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                {{-- Section 4: Destinataires --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">4</span>
                        </div>
                        Destinataires des données
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>Vos données personnelles peuvent être communiquées aux destinataires suivants :</p>
                        <ul class="list-disc list-inside space-y-2 ml-4">
                            <li><strong class="text-carbon-900 dark:text-white">Personnel autorisé de l'ASA CFG</strong> (organisateurs, commissaires, staff)</li>
                            <li><strong class="text-carbon-900 dark:text-white">FFSA et LSAR</strong> (obligations réglementaires sportives)</li>
                            <li><strong class="text-carbon-900 dark:text-white">Stripe</strong> (traitement sécurisé des paiements)</li>
                            <li><strong class="text-carbon-900 dark:text-white">Hébergeur OVH</strong> (hébergement technique)</li>
                            <li><strong class="text-carbon-900 dark:text-white">Autorités compétentes</strong> (sur réquisition légale)</li>
                        </ul>
                        <p>
                            Aucune donnée n'est vendue ou louée à des tiers à des fins commerciales.
                        </p>
                    </div>
                </section>

                {{-- Section 5: Durée de conservation --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">5</span>
                        </div>
                        Durée de conservation
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>Vos données sont conservées pendant les durées suivantes :</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-carbon-100 dark:bg-carbon-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white rounded-tl-lg">Type de données</th>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white rounded-tr-lg">Durée de conservation</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
                                    <tr>
                                        <td class="px-4 py-3">Compte utilisateur actif</td>
                                        <td class="px-4 py-3">Durée de l'activité + 3 ans d'inactivité</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Données d'inscription aux courses</td>
                                        <td class="px-4 py-3">5 ans (obligations comptables)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Résultats sportifs</td>
                                        <td class="px-4 py-3">Conservation illimitée (archives sportives)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Données de paiement</td>
                                        <td class="px-4 py-3">10 ans (obligations comptables)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3">Logs de connexion</td>
                                        <td class="px-4 py-3">1 an</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                {{-- Section 6: Vos droits --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">6</span>
                        </div>
                        Vos droits
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>Conformément au RGPD, vous disposez des droits suivants :</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <h3 class="font-semibold text-carbon-900 dark:text-white">Droit d'accès</h3>
                                </div>
                                <p class="text-sm">Obtenir la confirmation que vos données sont traitées et en recevoir une copie.</p>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <h3 class="font-semibold text-carbon-900 dark:text-white">Droit de rectification</h3>
                                </div>
                                <p class="text-sm">Faire corriger des données inexactes ou incomplètes.</p>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <h3 class="font-semibold text-carbon-900 dark:text-white">Droit à l'effacement</h3>
                                </div>
                                <p class="text-sm">Demander la suppression de vos données (sous réserve des obligations légales).</p>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <h3 class="font-semibold text-carbon-900 dark:text-white">Droit à la limitation</h3>
                                </div>
                                <p class="text-sm">Demander la suspension du traitement de vos données.</p>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    <h3 class="font-semibold text-carbon-900 dark:text-white">Droit à la portabilité</h3>
                                </div>
                                <p class="text-sm">Recevoir vos données dans un format structuré et couramment utilisé.</p>
                            </div>

                            <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
                                    </svg>
                                    <h3 class="font-semibold text-carbon-900 dark:text-white">Droit d'opposition</h3>
                                </div>
                                <p class="text-sm">Vous opposer au traitement de vos données pour des motifs légitimes.</p>
                            </div>
                        </div>

                        <div class="bg-racing-red-50 dark:bg-racing-red-900/20 border border-racing-red-200 dark:border-racing-red-800 rounded-xl p-4 mt-4">
                            <p class="text-racing-red-700 dark:text-racing-red-300">
                                <strong>Pour exercer vos droits :</strong> Contactez-nous via
                                <a href="https://cfg.re/#contactCfg" target="_blank" rel="noopener" class="underline hover:no-underline">https://cfg.re/#contactCfg</a>
                                en joignant une copie de votre pièce d'identité.
                            </p>
                        </div>

                        <p class="text-sm">
                            Vous disposez également du droit d'introduire une réclamation auprès de la <strong class="text-carbon-900 dark:text-white">CNIL</strong> (Commission Nationale de l'Informatique et des Libertés) : <a href="https://www.cnil.fr" target="_blank" rel="noopener" class="text-racing-red-600 dark:text-racing-red-400 hover:underline">www.cnil.fr</a>
                        </p>
                    </div>
                </section>

                {{-- Section 7: Sécurité --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">7</span>
                        </div>
                        Sécurité des données
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>Nous mettons en œuvre les mesures techniques et organisationnelles appropriées pour protéger vos données :</p>
                        <ul class="list-disc list-inside space-y-2 ml-4">
                            <li>Chiffrement SSL/TLS pour toutes les communications</li>
                            <li>Stockage sécurisé des mots de passe (hachage bcrypt)</li>
                            <li>Accès restreint aux données personnelles</li>
                            <li>Journalisation des accès et des modifications</li>
                            <li>Sauvegardes régulières et chiffrées</li>
                            <li>Authentification à deux facteurs disponible</li>
                        </ul>
                    </div>
                </section>

                {{-- Section 8: Cookies --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">8</span>
                        </div>
                        Cookies
                    </h2>
                    <div class="pl-11 space-y-4 text-carbon-600 dark:text-carbon-400">
                        <p>RUN200 Manager utilise uniquement des <strong class="text-carbon-900 dark:text-white">cookies strictement nécessaires</strong> au fonctionnement du site :</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-carbon-100 dark:bg-carbon-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white rounded-tl-lg">Cookie</th>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white">Finalité</th>
                                        <th class="px-4 py-3 text-left font-semibold text-carbon-900 dark:text-white rounded-tr-lg">Durée</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-xs">XSRF-TOKEN</td>
                                        <td class="px-4 py-3">Protection contre les attaques CSRF</td>
                                        <td class="px-4 py-3">Session</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-xs">run200-manager-session</td>
                                        <td class="px-4 py-3">Session utilisateur</td>
                                        <td class="px-4 py-3">2 heures</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-xs">darkMode</td>
                                        <td class="px-4 py-3">Préférence de thème (clair/sombre)</td>
                                        <td class="px-4 py-3">1 an</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-sm">
                            Aucun cookie de tracking, publicitaire ou de profilage n'est utilisé. Ces cookies étant strictement nécessaires, ils sont exemptés de consentement conformément à l'article 82 de la loi Informatique et Libertés.
                        </p>
                    </div>
                </section>

                {{-- Section 9: Modifications --}}
                <section>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-4">
                        <div class="w-8 h-8 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <span class="text-racing-red-500 font-bold">9</span>
                        </div>
                        Modifications de la politique
                    </h2>
                    <div class="pl-11 text-carbon-600 dark:text-carbon-400">
                        <p>
                            Nous pouvons mettre à jour cette politique de confidentialité. En cas de modification substantielle, vous serez informé par email ou via une notification sur le Site. Nous vous invitons à consulter régulièrement cette page.
                        </p>
                    </div>
                </section>

            </div>

            {{-- Navigation --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('legal') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-carbon-100 dark:bg-carbon-800 hover:bg-carbon-200 dark:hover:bg-carbon-700 text-carbon-700 dark:text-carbon-300 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Mentions légales
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
