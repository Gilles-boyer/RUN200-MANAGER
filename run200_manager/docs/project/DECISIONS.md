# Décisions et risques

Consigner date, décision, raison, impact et risques avec responsable.

## 15/10/2026 — coordinateur

- Source de vérité : `docs/project/board.json`, vue dérivée `KANBAN.md`.
- Émetteur présumé du reçu : ASSOCIATION SPORTIVE AUTOMOBILE DU CIRCUIT FELIX GUICHARD, SIREN 510 609 134, SIRET 510 609 134 00018, siège 400 chemin Robespierre, Les Orangers Sainte-Anne, 97437 Saint-Benoît, selon l'API Annuaire des entreprises. E-mail `contact@cfg.re` et téléphone `0692 725 584` fournis par l'utilisateur. À conserver en configuration/version de reçu.
- Les reçus concernent les encaissements d'inscription ; aucune qualification de reçu fiscal ou de facture tant que le statut fiscal n'est pas établi.
- Audit et corrections critiques précèdent le verdict de publication. L'identifiant SMTP présent dans l'historique Git ne doit jamais être copié dans les rapports.
- L'utilisateur a confirmé le 15/10/2026 un reçu ordinaire de paiement d'inscription, sans valeur fiscale, et l'envoi rétroactif automatique des reçus des paiements passés. Le lot historique nécessite un inventaire préalable des montants, identités et adresses ; les anomalies ne doivent pas déclencher d'envoi.
- Les nouveaux paiements donnent lieu à un reçu figé et un e-mail avec PDF. Les paiements passés ont un message distinct, sans promesse de prochaine course ; les envois sont idempotents autant que permet SMTP et leurs échecs sont traçables.
