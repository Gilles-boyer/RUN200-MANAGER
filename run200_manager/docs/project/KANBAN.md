# Kanban du projet

Vue dérivée de board.json. Modifier la source avec le coordinateur.

## Backlog

- **SEC-03** · P1 · Revoquer ancien secret SMTP expose et traiter historique · `it_devops`
  Blocage : Proprietaire du compte a contacter pour revocation; utilisateur dit pouvoir le contacter
- **REL-01** · P1 · Deployer et verifier les recus sur o2switch · `it_devops`
  Blocage : Ancien secret Mailtrap actif, parcours complet nouveau paiement et acces PDF autre compte non verifies, audit critique ouvert; aucun verdict pret
- **PAY-02** · P1 · Envoyer les recus historiques par lots · `it_backend`
  Blocage : Envois historiques suspendus: procedure de confirmation des destinataires a definir sans activer naivement MustVerifyEmail; controle Stripe vers base en sens inverse; 75 paiements hors Stripe a rapprocher par utilisateur
- **LEG-02** · P1 · Mettre en oeuvre la conservation des données sans cascade destructive · `it_backend`
  Blocage : Concevoir anonymisation et preservation des preuves comptables et sportives

## À préciser

Aucune carte.

## Prêt

Aucune carte.

## En cours

- **REC-05** · P1 · Envoyer les reçus aux adresses des dossiers en lots contrôlés · `it_backend`

## En revue

- **REC-03** · P1 · Vérifier les adresses et envoyer les reçus Stripe historiques · `it_backend`

## À livrer

- **AUD-01** · P1 · Audit technique et parcours du site · `it_qa`
- **PAY-01** · P1 · Reçus de paiement nouveaux et historiques · `it_backend`
- **SEC-01** · P1 · Corriger les risques critiques avant publication · `it_backend`
- **CI-01** · P1 · Rétablir les contrôles GitHub Actions · `it_devops`
- **SEC-02** · P1 · Limiter l inscription publique · `it_backend`
- **OPS-01** · P1 · Preparer le deploiement o2switch et le retour arriere · `it_devops`
- **LEG-01** · P1 · Verifier et corriger informations legales et conservation · `it_product_owner`
- **PAY-03** · P1 · Garder les envois automatiques des nouveaux recus desactives au deploiement · `it_backend`
- **PAY-04** · P1 · Rendre le téléchargement de reçu strictement en lecture · `it_backend`
- **REC-04** · P1 · Borner le pilote de reçu historique à un seul paiement · `it_backend`
- **WEB-01** · P2 · Corriger les pages publiques et la page 404 · `it_frontend`

## Terminé

Aucune carte.
