# 📥 Guide d'installation — SamaBoutique

Ce guide vous permet d'installer et lancer SamaBoutique sur **votre machine** en moins de 10 minutes. Suivez les étapes dans l'ordre.

---

## 📋 Prérequis

Avant de commencer, installez **XAMPP** qui contient tout ce qu'il faut (PHP, MySQL, Apache) :

👉 [Télécharger XAMPP](https://www.apachefriends.org/download.html) — version PHP 8.2 minimum

Installez aussi :

| Outil | Pourquoi | Lien |
|---|---|---|
| **Git** | Cloner le projet depuis GitHub | [git-scm.com/downloads](https://git-scm.com/downloads) |
| **Composer** | Gestionnaire de packages PHP | [getcomposer.org/download](https://getcomposer.org/download/) |

⚠ **Vérification** : ouvrez un terminal (PowerShell sur Windows) et tapez :

```bash
php -v
composer --version
git --version
```

Si les 3 commandes affichent une version, tout est OK. Sinon, redémarrez votre PC après installation.

---

## 🚀 Étape 1 — Lancer XAMPP

1. Ouvrez le **XAMPP Control Panel** (raccourci sur le bureau ou menu Démarrer)
2. Cliquez sur **Start** à côté de **Apache**
3. Cliquez sur **Start** à côté de **MySQL**
4. Les deux services doivent passer au **vert**

![XAMPP](https://www.apachefriends.org/images/xampp-control-panel.png)

---

## 📂 Étape 2 — Cloner le projet

Ouvrez un terminal et placez-vous dans le dossier `htdocs` de XAMPP :

```bash
cd C:\xampp\htdocs
```

Puis clonez le repo :

```bash
git clone https://github.com/Faliloudiouf/samaboutique.git
cd samaboutique
```

---

## 📦 Étape 3 — Installer les dépendances PHP

```bash
composer install
```

⏳ Cette étape prend 2-5 minutes selon votre connexion. Composer télécharge Laravel et tous les packages dans le dossier `vendor/`.

> **Si vous avez une erreur** sur l'extension `fileinfo` ou `gd` : ouvrez `C:\xampp\php\php.ini`, cherchez `;extension=fileinfo` et `;extension=gd`, retirez le `;` au début. Puis relancez Apache dans XAMPP.

---

## ⚙️ Étape 4 — Configurer l'environnement

Copiez le fichier d'exemple en `.env` :

**Windows (PowerShell)** :
```powershell
Copy-Item .env.example .env
```

**Linux/Mac** :
```bash
cp .env.example .env
```

Puis générez la clé d'application :

```bash
php artisan key:generate
```

> Cette commande remplit automatiquement `APP_KEY=` dans le fichier `.env`. C'est la clé de chiffrement de votre installation.

---

## 🗄️ Étape 5 — Créer la base de données

1. Ouvrez votre navigateur sur **[http://localhost/phpmyadmin](http://localhost/phpmyadmin)**
2. Cliquez sur **Nouvelle base de données** (menu de gauche)
3. Nom : `sama_boutique`
4. Interclassement : `utf8mb4_unicode_ci`
5. Cliquez sur **Créer**

> ⚠ Le nom de la base **doit être exactement** `sama_boutique` (avec un underscore). C'est ce qui est défini dans le fichier `.env`.

---

## 🌱 Étape 6 — Créer les tables et données de démo

```bash
php artisan migrate --seed
```

Cette commande :
- Crée toutes les tables (`users`, `products`, `categories`, `sales`, `customers`, `suppliers`...)
- Insère les **données de démo** : 2 utilisateurs, 26 produits, 8 catégories, 8 clients, 10 ventes historiques

Vous verrez quelque chose comme :
```
INFO  Running migrations.
0001_01_01_000000_create_users_table .................. DONE
2026_05_18_100000_create_categories_table ............. DONE
...
INFO  Seeding database.
```

---

## 🔗 Étape 7 — Lier le stockage des images

```bash
php artisan storage:link
```

Cette commande crée un lien symbolique pour que les images uploadées (photos profil, produits, catégories) soient accessibles depuis le navigateur.

---

## ▶️ Étape 8 — Lancer le serveur

```bash
php artisan serve
```

Vous verrez :
```
INFO  Server running on [http://127.0.0.1:8000].
Press Ctrl+C to stop the server.
```

🎉 **Ouvrez votre navigateur sur [http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔐 Étape 9 — Se connecter

Deux comptes de démo sont prêts :

| Rôle | Email | Mot de passe | Accès |
|---|---|---|---|
| 🔑 **Gérant** | `admin@samaboutique.sn` | `password` | Tout (catalogue, fournisseurs, comptes, rapports) |
| 👨‍💼 **Vendeur** | `vendeur@samaboutique.sn` | `password` | Caisse + crédits clients uniquement |

---

## 🧪 Étape 10 — Tester l'application

Voici un parcours de test rapide pour vérifier que tout fonctionne :

### ✅ Test 1 : Faire une vente
1. Connectez-vous en **gérant** ou **vendeur**
2. Allez sur **Nouvelle vente** (menu gauche)
3. Cliquez sur 2-3 produits dans la grille
4. Choisissez le mode **Espèces**
5. Cliquez **Valider la vente** → vous arrivez sur le reçu
6. Cliquez **Envoyer par WhatsApp** pour tester le partage

### ✅ Test 2 : Créer un crédit
1. **Nouvelle vente** → ajoutez des produits
2. Choisissez un **client** dans la liste (en haut du panier)
3. Sélectionnez mode **Crédit**
4. Valider → la dette apparaît dans **Crédits clients**

### ✅ Test 3 : Encaisser un remboursement
1. Allez dans **Crédits clients**
2. Cliquez sur un client en retard
3. Cliquez sur **Remboursement**
4. Choisissez un raccourci (ex: "Tout") + mode → **Confirmer**

### ✅ Test 4 : Créer un produit avec image (gérant uniquement)
1. **Stocks** → **Nouveau produit**
2. Remplissez les champs + uploadez une image
3. Le produit apparaît avec votre image dans le POS

### ✅ Test 5 : Gérer un compte (gérant uniquement)
1. **Utilisateurs** → **Nouvel utilisateur**
2. Créez un vendeur avec photo de profil
3. Testez **Suspendre** puis **Réactiver**

---

## 🚨 Problèmes fréquents

### ❌ `SQLSTATE[HY000] [1049] Unknown database 'sama_boutique'`
➜ La base n'a pas été créée. Retournez à **l'étape 5**.

### ❌ `SQLSTATE[HY000] [2002] No connection could be made`
➜ MySQL n'est pas démarré. Ouvrez **XAMPP Control Panel** → **Start MySQL**.

### ❌ `composer install` échoue avec "extension ext-fileinfo missing"
➜ Ouvrez `C:\xampp\php\php.ini`, décommentez `extension=fileinfo` (retirez le `;`). Redémarrez Apache.

### ❌ Les images ne s'affichent pas
➜ Vous avez oublié l'étape 7. Lancez `php artisan storage:link`.

### ❌ Erreur 419 (Page Expired) au login
➜ Cache de session corrompu. Lancez :
```bash
php artisan config:clear
php artisan view:clear
```

### ❌ Port 8000 déjà utilisé
➜ Utilisez un autre port :
```bash
php artisan serve --port=8001
```

---

## 🔄 Mise à jour du projet

Pour récupérer les dernières modifications depuis GitHub :

```bash
git pull
composer install
php artisan migrate
php artisan view:clear
```

---

## 💡 Astuces

- **Voir les logs d'erreur** : `storage/logs/laravel.log`
- **Réinitialiser toutes les données** : `php artisan migrate:fresh --seed` (efface tout et remet les données de démo)
- **Vider les caches** :
  ```bash
  php artisan config:clear
  php artisan view:clear
  php artisan cache:clear
  ```

---

## 📞 Support

Si vous bloquez sur une étape, contactez **Falilou Diouf** :
- 📱 77 523 00 72
- 📧 faliloudiouf04@gmail.com

Bonne installation ! 🚀
