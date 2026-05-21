# 🛍️ SamaBoutique

> **Application web de gestion de boutique** — Projet MSI · L2 GLSI · ESP Dakar (2025-2026)

Application Laravel 12 complète pour gérer une boutique de quartier sénégalaise : ventes, stocks, crédits clients, fournisseurs et rapports. Pensée pour un boutiquier qui n'est pas informaticien — aucune fonctionnalité ne demande à l'utilisateur de "réfléchir".

---

## 📚 Documentation

- **[INSTALLATION.md](INSTALLATION.md)** — Comment installer et lancer le projet sur votre machine (étape par étape, pour débutants)
- **[RAPPORT.md](RAPPORT.md)** — Rapport complet de ce qui a été fait, architecture, fonctionnalités, choix techniques

---

## ⚡ Démarrage ultra-rapide

```bash
git clone https://github.com/Faliloudiouf/samaboutique.git
cd samaboutique
composer install
cp .env.example .env
php artisan key:generate
# créer la base "sama_boutique" dans phpMyAdmin
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Puis ouvrir [http://127.0.0.1:8000](http://127.0.0.1:8000)

**Comptes de démo :**
- 🔑 Gérant : `admin@samaboutique.sn` / `password`
- 👨‍💼 Vendeur : `vendeur@samaboutique.sn` / `password`

---

## ✨ Fonctionnalités

| Module | Description |
|---|---|
| 🛒 **Point de vente** | Caisse visuelle avec emojis par produit, ajout au panier, 4 modes paiement (Espèces, Wave, Orange Money, Crédit) + remise |
| 🧾 **Reçu auto** | PDF imprimable + envoi par WhatsApp / Email / SMS avec lien pré-rempli vers le client |
| 📦 **Catalogue** | Produits + catégories avec emojis, images, couleurs pastel. Stock auto décrémenté à chaque vente |
| ⚠ **Alertes stock** | Notifications visuelles produits en rupture ou sous le seuil d'alerte |
| 💰 **Crédits clients** | Suivi dettes par client, échéances 30j, statuts auto (à jour / à venir / en retard) |
| 💳 **Remboursements** | Page dédiée avec raccourcis montants, modes de paiement, calcul progression |
| 🚚 **Fournisseurs + commandes** | Création commandes avec lignes dynamiques, réception → stock incrémenté |
| 📊 **Rapports** | CA total, encaissé, panier moyen, top produits, par mode/statut/vendeur · export CSV |
| 👥 **Comptes** | Gérant peut créer/modifier/suspendre/supprimer des comptes (gérant ou vendeur) |
| 👤 **Profil** | Chaque user gère son nom, email, téléphone, photo, mot de passe |

---

## 🛠️ Stack technique

- **Backend** : PHP 8.2 + Laravel 12
- **Base de données** : MySQL/MariaDB (via XAMPP)
- **Frontend** : Bootstrap 5.3 + CSS custom (design terracotta + or sur fond crème)
- **Emojis** : Twemoji 14 (3D Apple-like cross-platform)
- **PDF** : barryvdh/laravel-dompdf
- **Polices** : Plus Jakarta Sans + DM Sans + DM Mono (Google Fonts)

---

## 👥 Équipe

Projet réalisé dans le cadre du module **MSI** en L2 GLSI à l'ESP de Dakar.
- **Scrum Master** : Falilou Diouf
- **Product Owner** : Le gérant de la boutique (fictif)
- **Équipe développement** : 4 membres

Méthodologie : SCRUM · 5 sprints · 8 user stories (toutes livrées)

---

## 📜 Licence

MIT — Projet académique libre d'utilisation.
