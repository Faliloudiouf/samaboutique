# 📋 Rapport du projet SamaBoutique

> **Module** : MSI · **Niveau** : L2 GLSI · **École** : ESP Dakar · **Année** : 2025-2026

---

## 1. Présentation du projet

**SamaBoutique** est une application web de gestion complète pour les boutiques de quartier au Sénégal. Elle est pensée pour un boutiquier **qui n'est pas informaticien** : tout doit être visuel, automatique, et accessible sans formation.

### Contexte
Au Sénégal, beaucoup de petites boutiques tiennent encore leurs comptes sur papier ou Excel. Les principaux besoins identifiés :
- Suivi des ventes journalières
- Mise à jour automatique des stocks
- Gestion des **crédits clients** (très courant dans les quartiers)
- Commandes aux fournisseurs et réception
- Rapports périodiques

### Méthodologie
**SCRUM** sur 5 sprints, avec 3 rôles :
- **Product Owner** : le gérant de la boutique
- **Scrum Master** : Falilou Diouf
- **Équipe développement** : 4 membres

---

## 2. User Stories — toutes livrées ✅

| # | User Story | Statut |
|---|---|---|
| US1 | En tant que vendeur, je veux enregistrer une vente afin de mettre le stock à jour automatiquement | ✅ |
| US2 | En tant que gérant, je veux consulter le tableau de bord afin de piloter la boutique en temps réel | ✅ |
| US3 | En tant que gérant, je veux gérer le catalogue des produits afin de maintenir les informations à jour | ✅ |
| US4 | En tant que vendeur, je veux suivre les stocks avec alertes afin d'éviter les ruptures | ✅ |
| US5 | En tant que gérant, je veux enregistrer les crédits clients afin de suivre les dettes et remboursements | ✅ |
| US6 | En tant que gérant, je veux passer des commandes aux fournisseurs afin de réapprovisionner la boutique | ✅ |
| US7 | En tant que gérant, je veux générer des rapports de ventes afin d'analyser les performances | ✅ |
| US8 | En tant que gérant, je veux gérer les comptes utilisateurs afin de contrôler les accès | ✅ |

---

## 3. Architecture technique

### Stack
| Couche | Technologie |
|---|---|
| Langage backend | **PHP 8.2** |
| Framework | **Laravel 12** |
| Base de données | **MySQL 10.4** (via MariaDB / XAMPP) |
| Frontend | **Bootstrap 5.3** + CSS custom |
| Polices | Plus Jakarta Sans + DM Sans + DM Mono |
| Emojis | **Twemoji 14** (3D Apple-like) |
| Génération PDF | barryvdh/laravel-dompdf |
| Serveur local | Apache (XAMPP) |
| Versioning | Git + GitHub |

### Structure du projet
```
sama-boutique/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # 11 contrôleurs (Auth, Sale, Customer, Product...)
│   │   └── Middleware/         # EnsureUserRole.php (rôle gérant/vendeur)
│   └── Models/                 # 9 modèles Eloquent
├── database/
│   ├── migrations/             # 8 migrations (schéma complet)
│   └── seeders/                # DatabaseSeeder avec données démo
├── resources/
│   └── views/                  # 35+ vues Blade organisées par module
│       ├── layouts/app.blade.php   # Layout principal (sidebar + topbar)
│       ├── auth/                   # Login
│       ├── sales/                  # POS, historique, reçu
│       ├── customers/              # Crédits clients + remboursement
│       ├── products/               # Catalogue
│       ├── categories/             # Catégories
│       ├── suppliers/              # Fournisseurs
│       ├── purchase_orders/        # Commandes
│       ├── reports/                # Rapports
│       ├── users/                  # Gestion comptes
│       └── profile/                # Profil utilisateur
├── public/
│   ├── css/app.css             # CSS custom design system
│   └── storage/                # Images uploadées (lien symbolique)
└── routes/web.php              # Toutes les routes de l'app
```

---

## 4. Modèle de données

### Tables principales

```
users (id, name, email, password, role, photo, telephone, actif, suspended_at)
       ↓ user_id
sales (id, numero, customer_id, montant_total, remise, montant_paye,
       mode_paiement, statut, echeance)
       ↓ sale_id
sale_items (id, product_id, produit_nom, quantite, prix_unitaire, sous_total)

customers (id, nom, telephone, adresse, etiquette)
       ↓ customer_id
customer_payments (id, sale_id, user_id, montant, mode_paiement)

categories (id, nom, emoji, image, couleur_fond, couleur_accent)
       ↓ category_id
products (id, reference, nom, emoji, image, prix_achat, prix_vente,
          stock, seuil_alerte, actif)

suppliers (id, nom, contact, telephone, email, adresse)
       ↓ supplier_id
purchase_orders (id, numero, statut, montant_total, date_commande, date_reception)
       ↓ purchase_order_id
purchase_order_items (id, product_id, quantite, prix_unitaire, sous_total)
```

### Règles métier importantes

**Statuts de vente** (auto-calculés)
- `payee` si montant_paye ≥ montant_total
- `partielle` si 0 < montant_paye < montant_total
- `credit` si montant_paye = 0
- `annulee` si la vente est annulée par le gérant

**Statuts de crédit** (auto-calculés selon échéance 30j)
- `à jour` si solde = 0
- `à venir` si échéance ≤ 5 jours
- `en cours` si échéance > 5 jours
- `en retard` si échéance < aujourd'hui

**Numérotation auto**
- Ventes : `V-MMDD-NNN` (ex: V-0518-003)
- Commandes : `CMD-YYYYMMDD-NNNN`

**Décrément stock automatique** : à chaque ligne de vente créée, le stock du produit est décrémenté dans une transaction SQL (verrouillage `lockForUpdate`).

**Incrément stock automatique** : à la réception d'une commande fournisseur, le stock de chaque produit est augmenté.

---

## 5. Fonctionnalités détaillées

### 🛒 Point de vente (POS)
- Grille de produits avec emojis 3D Twemoji et fonds pastel par catégorie
- Chips de filtre par catégorie avec compteurs
- Recherche live par nom ou référence
- Sélection client existant ou client occasionnel
- Panier sticky à droite avec quantités modifiables (+/−)
- 4 modes paiement visuels : Espèces / Wave / Orange / Crédit
- Champ remise applicable au total
- Validation impossible si :
  - Panier vide
  - Mode = Crédit sans client sélectionné

### 🧾 Reçu de vente
- Page de confirmation avec :
  - Reçu papier centré (logo, en-tête, lignes, total, QR code)
  - Panneau succès vert affichant le montant
  - 4 actions d'envoi du reçu :
    - **Imprimer** (PDF via dompdf)
    - **WhatsApp** (lien `wa.me/{tel_client}?text=...` pré-rempli)
    - **Email** (lien `mailto:` avec sujet et corps)
    - **SMS** (lien `sms:`)

### 💰 Crédits clients
- Page index avec 4 stat cards : Total dû / Clients actifs / En retard / Remboursé ce mois
- Tabs de filtrage : Tous / Récents (7j) / En retard
- Recherche par nom ou téléphone
- Table avec avatars colorés (générés par hash du nom), statuts pills auto

### 💳 Remboursement (page dédiée)
- À gauche : la dette en cours avec barre de progression
- Historique des paiements
- À droite : formulaire avec
  - Input montant grand format
  - 4 raccourcis (1000, 2500, 5000, Tout)
  - 4 modes paiement avec emojis
  - Preview live "Après remboursement"
- Stratégie d'imputation FIFO si remboursement libre

### 📦 Catalogue
- CRUD produits avec :
  - Référence unique, nom, description
  - Emoji personnalisable (override la catégorie)
  - Image uploadable (JPG/PNG/WebP max 2 Mo)
  - Prix achat + vente
  - Stock + seuil d'alerte
  - Statut actif/inactif
- CRUD catégories avec :
  - Emoji + couleurs pastel personnalisables
  - Image uploadable
- Filtres : par catégorie, par stock (alerte/rupture), recherche

### 🚚 Fournisseurs & commandes
- CRUD fournisseurs
- Création de commandes avec lignes dynamiques (JS)
- États : `en_cours` → `recue` ou `annulee`
- À la réception : stock incrémenté + prix d'achat MAJ automatiquement

### 📊 Rapports
- Filtres période (date début/fin) + raccourcis (Auj/Sem/Mois/Année)
- KPI : CA total, encaissé, nb ventes, panier moyen
- Graphiques en barres simples (CSS) :
  - Ventes par jour
  - Top 15 produits
- Tableaux : ventes par mode paiement, par statut, par vendeur
- Export CSV (UTF-8 avec BOM pour Excel)

### 👥 Gestion comptes (US8)
- Gérant peut :
  - **Créer** un compte (gérant ou vendeur) avec photo
  - **Modifier** infos + photo + mot de passe
  - **Suspendre / Réactiver**
  - **Supprimer** (bloqué si ventes liées → suggère suspendre)
- Sécurité : impossible de se supprimer ou se suspendre soi-même
- Filtres : par rôle, par statut (actifs/suspendus), recherche

### 👤 Profil utilisateur
- Modifier nom / email / téléphone / photo
- Changer mot de passe avec vérification de l'ancien
- Bloc info compte (rôle, date inscription, dernière activité)

---

## 6. Design

### Palette
Inspirée des couleurs sénégalaises chaudes :
- **Primaire** : `#C84B31` (terracotta)
- **Accent** : `#F4B942` (or)
- **Fond app** : `#F1E7D7` (crème)
- **Sidebar** : `#1C1410` (wood dark)
- **Succès** : `#2A9D5C` · **Danger** : `#E63946` · **Info** : `#457B9D`

### Polices
- **Titres** : Plus Jakarta Sans (700/800)
- **Texte** : DM Sans (400/600)
- **Code/numéros** : DM Mono

### Couleurs pastel par catégorie
Chaque catégorie a sa propre palette de fond pour les cards produits :
- Épicerie : `#FFE5C7` · Boissons : `#FCE4EC` · Hygiène : `#E1ECF4`
- Frais : `#E8F5E9` · Boulangerie : `#FFF3CD` · Snacks : `#FFE0B2`
- Bébé : `#F3E5F5` · Téléphonie : `#E0F2F1`

### Avatars colorés
Couleur déterministe par nom (hash crc32 → 10 couleurs prédéfinies).

### Responsive
- **Desktop** (>1200px) : layout 2 colonnes (master-detail, POS+panier)
- **Tablette** (900-1200px) : layouts stackés verticalement
- **Mobile** (<900px) :
  - Sidebar devient drawer avec backdrop
  - Tables deviennent cards empilées
  - POS en grille 2 colonnes
  - Panier non-sticky

---

## 7. Sécurité

- **Authentification** Laravel native avec `Auth::attempt`
- **Hash bcrypt** des mots de passe (rounds=12)
- **CSRF protection** sur tous les formulaires
- **Middleware `role:gerant`** sur les routes sensibles
- **Comptes suspendus** : déconnexion forcée au login
- **Validation** côté serveur sur toutes les inputs (max length, formats, existence)
- **Upload images** : validation type + taille max 2 Mo, stockage hors-public
- **Transactions SQL** sur opérations critiques (ventes, stock, paiements)

---

## 8. Cérémonies SCRUM appliquées

| Cérémonie | Application |
|---|---|
| **Sprint Planning** | Découpage des 8 US en 5 sprints, estimation, répartition |
| **Daily Scrum** | Réunion quotidienne courte sur l'avancement |
| **Sprint Review** | Démo de chaque incrément au PO (le gérant fictif) |
| **Rétrospective** | Bilan fin de sprint : ce qui marche, ce qui ne marche pas |

---

## 9. Sprints réalisés

| Sprint | Périmètre | Livrable |
|---|---|---|
| **Sprint 1** | Auth, rôles, CRUD catalogue | Login, sidebar par rôle, produits + catégories |
| **Sprint 2** | Ventes + reçu + stock | POS visuel, reçu PDF, décrément auto |
| **Sprint 3** | Crédits clients | Clients, dettes, remboursements FIFO |
| **Sprint 4** | Fournisseurs + commandes | Fournisseurs, commandes, réception → stock |
| **Sprint 5** | Tableau de bord + rapports + gestion comptes | Dashboard, CSV, US8 admin |

---

## 10. Tests effectués

Tests manuels bout en bout sur les principaux parcours :

| Test | Résultat |
|---|---|
| Vente espèces + génération reçu | ✅ |
| Vente à crédit + statut → "credit" + échéance +30j | ✅ |
| Vente avec remise (sous-total - remise = total) | ✅ |
| Stock auto décrémenté après vente | ✅ |
| Remboursement partiel FIFO sur plusieurs ventes | ✅ |
| Création commande + réception → stock incrémenté | ✅ |
| Lien WhatsApp avec texte pré-rempli + numéro client | ✅ |
| Export CSV UTF-8 ouvert correctement dans Excel | ✅ |
| Vendeur ne voit pas le menu Catalogue ni Comptes | ✅ |
| Suspension compte → déconnexion forcée au login | ✅ |
| Upload image produit/catégorie/profil | ✅ |
| Responsive : sidebar drawer mobile, tables → cards | ✅ |

---

## 11. Difficultés rencontrées et solutions

| Problème | Solution |
|---|---|
| Composer install lent et bugué au démarrage | Documenter prérequis + extensions PHP dans INSTALLATION.md |
| Twemoji CDN 404 sur certains paquets | Switch vers `cdn.jsdelivr.net/npm/twemoji@14.0.2` |
| Step input HTML invalidait les montants quelconques | `step="any"` au lieu de `step="100"` |
| Master-detail qui débordait sur grands écrans | `grid-template-columns: minmax(0,1fr) 380px` + `min-width:0` |
| Bootstrap retiré par accident lors d'une refonte | Restauration en CDN + alias CSS pour les anciennes classes |
| Conflit ENUM `statut` quand on ajoute 'annulee' | `ALTER TABLE ... MODIFY` via DB::statement |

---

## 12. Statistiques du projet

- **Migrations** : 8
- **Modèles** : 9 (User, Category, Product, Sale, SaleItem, Customer, CustomerPayment, Supplier, PurchaseOrder, PurchaseOrderItem)
- **Contrôleurs** : 11
- **Vues Blade** : 35+
- **Routes** : 40+
- **Lignes de code custom** (hors vendor) : ~6500
- **Données de démo** : 2 users, 26 produits, 8 catégories, 8 clients, 3 fournisseurs, 10 ventes historiques

---

## 13. Perspectives d'amélioration

Idées pour des versions futures :
- 📲 Application mobile native (Android via Capacitor ou Flutter)
- 🔔 Notifications push pour stocks bas
- 💬 SMS automatique aux clients en retard via API Orange
- 📈 Graphiques avancés (Chart.js)
- 🗓 Échéancier visuel des paiements
- 🔄 Synchronisation multi-boutiques (mode chaîne)
- 🌍 Multi-langue (Wolof / Français / Anglais)
- 🧾 Lecture code-barres pour ajout rapide au panier
- 💸 Intégration paiement Wave / Orange Money via API officielle

---

## 14. Conclusion

SamaBoutique répond à **100% des user stories du backlog**. L'application est fonctionnelle, esthétique, et prête pour une utilisation réelle en boutique. Le design met l'accent sur la **simplicité d'usage** : le boutiquier voit des emojis et des couleurs, pas des écrans complexes.

Le code est organisé selon les bonnes pratiques Laravel (MVC, middlewares, validation, transactions) et le projet est entièrement documenté pour permettre à un nouveau développeur de l'installer et de contribuer en moins de 10 minutes.

---

**Falilou Diouf** · L2 GLSI · ESP Dakar · Mai 2026
