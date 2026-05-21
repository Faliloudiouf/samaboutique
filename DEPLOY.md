# 🚀 Déploiement sur Railway (gratuit)

Guide pas-à-pas pour mettre **SamaBoutique** en ligne sur Railway, accessible publiquement à une URL `https://samaboutique-production.up.railway.app`.

⏱ **Temps estimé** : 10 minutes
💰 **Coût** : gratuit (crédit Railway de $5/mois, largement suffisant pour une démo)

---

## 📋 Prérequis
- Le projet est déjà sur GitHub ✅ (https://github.com/Faliloudiouf/samaboutique)
- Un compte Google (pour signer Railway en 1 clic)

---

## 🎯 Étape 1 — Créer ton compte Railway

1. Va sur **[railway.com](https://railway.com)**
2. Clique **Login** en haut à droite
3. Choisis **Login with GitHub** (le plus simple)
4. Autorise Railway à accéder à tes repos publics

---

## 🎯 Étape 2 — Créer le projet depuis GitHub

1. Sur ton dashboard Railway, clique **New Project**
2. Choisis **Deploy from GitHub repo**
3. Si tu vois "Configure Railway on GitHub", clique dessus puis choisis **All repositories** ou **samaboutique** uniquement
4. Sélectionne le repo `Faliloudiouf/samaboutique`
5. Railway crée automatiquement un service nommé `samaboutique`

🔄 Railway commence à build (sans succès cette première fois — c'est normal, on n'a pas encore la DB)

---

## 🎯 Étape 3 — Ajouter la base de données MySQL

1. Dans ton projet, clique **+ Create** (en haut à droite)
2. Choisis **Database** → **Add MySQL**
3. Une nouvelle "tile" MySQL apparaît à côté de ton app

---

## 🎯 Étape 4 — Configurer les variables d'environnement

1. Clique sur la tile **samaboutique** (ton app)
2. Onglet **Variables**
3. Clique **+ New Variable** et ajoute une par une (copie/colle) :

| Variable | Valeur |
|---|---|
| `APP_NAME` | `SamaBoutique` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_LOCALE` | `fr` |
| `APP_KEY` | (voir étape 4bis) |
| `APP_URL` | (voir étape 5, mettre l'URL Railway) |
| `LOG_CHANNEL` | `stack` |
| `LOG_LEVEL` | `error` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQL_DATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQL_ROOT_PASSWORD}}` |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `FILESYSTEM_DISK` | `local` |

> ⚠ Les `${{MySQL.XXX}}` sont des **références** : Railway va automatiquement remplacer par les vraies valeurs de ta DB MySQL. Tape-les exactement comme ça.

### Étape 4bis — Générer la clé APP_KEY

Dans ton terminal local (depuis le dossier du projet) :
```bash
php artisan key:generate --show
```

Cette commande affiche une clé du genre `base64:abc123XYZ...`. Copie tout (avec le `base64:`) et colle dans la variable `APP_KEY` sur Railway.

---

## 🎯 Étape 5 — Récupérer ton URL publique

1. Toujours sur la tile **samaboutique**, onglet **Settings**
2. Section **Networking**, clique **Generate Domain**
3. Railway te donne une URL du genre `samaboutique-production-xxxx.up.railway.app`
4. Retourne dans **Variables**, mets cette URL (avec `https://`) dans `APP_URL`

---

## 🎯 Étape 6 — Redéployer

1. Onglet **Deployments**
2. Clique sur les `⋮` du dernier déploiement → **Redeploy**
3. Attends ~3-5 min que le build termine (tu peux voir les logs en direct)

✅ Si tu vois "Application is live" en vert, c'est gagné !

---

## 🎯 Étape 7 — Insérer les données de démo (une seule fois)

1. Toujours sur la tile **samaboutique**, clique sur les `⋮` → **Open Shell** (ou onglet **Settings** → **Service** → bouton terminal)
2. Dans le terminal Railway, tape :
   ```bash
   php artisan db:seed --force
   ```
3. Tu verras les 2 utilisateurs + 26 produits + 8 clients seedés

---

## 🎯 Étape 8 — Tester en ligne

Ouvre ton URL Railway dans le navigateur. Connecte-toi :
- 🔑 Gérant : `admin@samaboutique.sn` / `password`
- 👨‍💼 Vendeur : `vendeur@samaboutique.sn` / `password`

🎉 **Ton appli est en ligne et accessible depuis n'importe où dans le monde !**

---

## 🔄 Mise à jour automatique

À chaque `git push` sur la branche `main`, Railway **redéploie automatiquement** en 2-3 minutes. Tu n'as rien à faire.

```bash
# Sur ta machine
git add .
git commit -m "Nouvelle fonctionnalité"
git push
# → Railway redéploie tout seul
```

---

## 💰 Surveiller la consommation

1. Sur ton dashboard Railway, clique sur ton avatar → **Usage**
2. Tu vois le crédit restant sur le mois
3. Le projet SamaBoutique consomme **très peu** (~$2-3/mois max). Le crédit gratuit de $5 suffit largement.

---

## 🚨 Problèmes fréquents

### Le build échoue avec "could not find driver"
➜ Tu as oublié d'ajouter l'extension `pdo_mysql`. Vérifie ton `nixpacks.toml`.

### Erreur 500 au premier chargement
➜ Va dans **Logs** sur Railway, cherche le message d'erreur.
➜ Souvent : `APP_KEY` mal copié (manque le `base64:` au début).

### Login marche pas (Session expired)
➜ Tu as oublié `SESSION_DRIVER=cookie` dans les variables. Ajoute-le.

### Les images uploadées disparaissent après un redéploiement
➜ Normal sur Railway free tier (stockage non persistent). Pour garder les images, il faut S3/Cloudinary. Pour la démo, contente-toi des emojis.

### "Database doesn't exist" après le déploiement
➜ Vérifie que ta tile MySQL est bien démarrée (vert). Lance `php artisan migrate --force` via Open Shell.

---

## 🌐 Bonus : domaine personnalisé

Si tu achètes `samaboutique.sn` (~10 000 FCFA/an sur [NIC Sénégal](https://www.nic.sn)) :
1. Va dans **Settings** → **Networking** sur Railway
2. Clique **Custom Domain**
3. Entre `samaboutique.sn`
4. Configure les DNS chez NIC Sénégal selon les instructions de Railway

---

## 🛟 Support

En cas de blocage, contacte **Falilou Diouf** au 77 523 00 72 ou faliloudiouf04@gmail.com.

Bon déploiement ! 🚀
