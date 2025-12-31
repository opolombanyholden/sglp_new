---
description: Accès et procédure de test pour l'application SGLP Admin
---

# Accès Admin SGLP

## Identifiants de connexion
- **URL**: http://localhost:8888/sglp_v116/public/admin
- **Email**: admin@pngdi.ga
- **Mot de passe**: password123

## Procédure de test

// turbo-all

1. Ouvrir http://localhost:8888/sglp_v116/public/login
2. Se connecter avec les identifiants ci-dessus
3. Naviguer vers le module à tester

## Modules disponibles

### Opérations
- **Nouvelle Organisation**: Création d'une nouvelle organisation
- **Modification**: Modifier une organisation existante (inclut changement statutaire)
- **Cessation**: Déclarer la fin d'activité
- **Ajout/Retrait Adhérent**: Gérer les membres
- **Déclaration Activité**: Rapport d'activité

### Dossiers par Statut
- Brouillons, En Attente, En Cours, Terminés, Rejetés, Annulés

## Nettoyage espace disque

Si l'espace disque est saturé, exécuter:
```bash
rm -rf ~/.gemini/antigravity/browser_recordings/*
```
