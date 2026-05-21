<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === USERS ===
        User::create([
            'name' => 'Mariama Sow',
            'email' => 'admin@samaboutique.sn',
            'telephone' => '77 523 00 72',
            'password' => Hash::make('password'),
            'role' => 'gerant', 'actif' => true,
        ]);
        $vendeur = User::create([
            'name' => 'Abdou Diop',
            'email' => 'vendeur@samaboutique.sn',
            'telephone' => '77 642 02 44',
            'password' => Hash::make('password'),
            'role' => 'vendeur', 'actif' => true,
        ]);

        // === CATÉGORIES (avec emoji + palette pastel) ===
        $cats = [
            ['Épicerie',     '🍚', '#FFE5C7', '#D4922A', 'Riz, huile, pâtes, conserves'],
            ['Boissons',     '🥤', '#FCE4EC', '#C2185B', 'Sodas, eaux, jus'],
            ['Hygiène',      '🧼', '#E1ECF4', '#457B9D', 'Savons, dentifrices, lessive'],
            ['Frais',        '🥛', '#E8F5E9', '#2A9D5C', 'Lait, yaourts, beurre'],
            ['Boulangerie',  '🥖', '#FFF3CD', '#946A0F', 'Pain, viennoiseries'],
            ['Bébé',         '🍼', '#F3E5F5', '#7B1FA2', 'Couches, lait infantile'],
            ['Snacks',       '🍪', '#FFE0B2', '#E65100', 'Biscuits, chips, bonbons'],
            ['Téléphonie',   '📱', '#E0F2F1', '#00695C', 'Crédit, accessoires'],
        ];
        $catModels = [];
        foreach ($cats as [$nom, $emoji, $fond, $accent, $desc]) {
            $catModels[$nom] = Category::create([
                'nom' => $nom, 'emoji' => $emoji,
                'couleur_fond' => $fond, 'couleur_accent' => $accent, 'description' => $desc,
            ]);
        }

        // === PRODUITS (emoji par produit) ===
        $produits = [
            // Épicerie
            ['Épicerie', 'RIZ-PARF-5', 'Riz parfumé 5kg', '🍚', 3500, 4500, 42],
            ['Épicerie', 'HUI-NIIN-1', 'Huile Niinal 1L', '🫒', 1200, 1800, 28],
            ['Épicerie', 'SEL-FIN-500','Sel fin 500g', '🧂', 200, 300, 84],
            ['Épicerie', 'TOM-CONC-400','Concentré tomate 400g', '🍅', 600, 900, 3],
            ['Épicerie', 'SUCRE-1', 'Sucre cristal 1kg', '🍬', 750, 1100, 30],

            // Boissons
            ['Boissons', 'COCA-15', 'Coca 1.5L', '🥤', 850, 1200, 31],
            ['Boissons', 'EAU-KIR-15', 'Kirène 1.5L', '💧', 350, 500, 30],
            ['Boissons', 'JUS-MANGO', 'Jus Pressea Mangue', '🧃', 500, 800, 15],
            ['Boissons', 'CAFE-SOL', 'Café Solubat 100g', '☕', 1500, 2200, 12],

            // Hygiène
            ['Hygiène', 'SAV-MADA', 'Savon Madar', '🧼', 200, 350, 47],
            ['Hygiène', 'DENT-COL', 'Dentifrice Colgate', '🪥', 800, 1200, 20],
            ['Hygiène', 'LESS-OMO-1', 'Lessive Omo 1kg', '🧺', 1500, 2200, 6],
            ['Hygiène', 'PQ-LOTUS', 'Papier toilette x4', '🧻', 700, 1000, 18],

            // Frais
            ['Frais', 'LAIT-POU-400', 'Lait poudre 400g', '🥛', 1500, 2200, 6],
            ['Frais', 'YAOURT-CHO', 'Yaourt chocolat', '🍶', 200, 350, 25],
            ['Frais', 'BEURRE-200', 'Beurre 200g', '🧈', 1200, 1700, 14],

            // Boulangerie
            ['Boulangerie', 'PAIN-BAG', 'Pain baguette', '🥖', 100, 200, 22],
            ['Boulangerie', 'CROIS-CHO', 'Croissant chocolat', '🥐', 250, 400, 16],

            // Bébé
            ['Bébé', 'COUCH-PAMP', 'Couches Pampers x30', '🧷', 5500, 7500, 8],
            ['Bébé', 'LAIT-INF', 'Lait infantile Nan', '🍼', 4500, 6000, 5],

            // Snacks
            ['Snacks', 'BIS-IKRAM', 'Biscuits Ikram', '🍪', 300, 450, 56],
            ['Snacks', 'CHIPS-LAYS', 'Chips Lays 50g', '🍟', 300, 500, 25],
            ['Snacks', 'BON-MEN', 'Bonbons menthe', '🍬', 50, 100, 200],

            // Téléphonie
            ['Téléphonie', 'CRD-OR-1000', 'Recharge Orange 1000F', '📱', 950, 1000, 50],
            ['Téléphonie', 'CRD-FR-500',  'Recharge Free 500F', '📱', 480, 500, 0],
            ['Téléphonie', 'CHG-USB-C',   'Chargeur USB-C', '🔌', 1500, 2500, 4],
        ];
        $productModels = [];
        foreach ($produits as [$cat, $ref, $nom, $emoji, $achat, $vente, $stock]) {
            $productModels[$ref] = Product::create([
                'category_id' => $catModels[$cat]->id,
                'reference' => $ref, 'nom' => $nom, 'emoji' => $emoji,
                'prix_achat' => $achat, 'prix_vente' => $vente,
                'stock' => $stock, 'seuil_alerte' => 5, 'actif' => true,
            ]);
        }

        // === CLIENTS (8 clients réalistes) ===
        $clientsData = [
            ['Fatou Ndiaye',   '77 654 12 08', 'cliente régulière'],
            ['Abdoul Diallo',  '70 845 33 11', 'voisin'],
            ['Mariama Sow',    '77 123 45 67', 'cliente VIP'],
            ['Ousmane Sarr',   '76 320 71 02', 'épicier voisin'],
            ['Aïcha Bâ',       '78 102 56 33', 'enseignante'],
            ['Ibrahima Kane',  '77 540 22 19', 'tailleur du quartier'],
            ['Khady Sy',       '76 998 12 04', null],
            ['Modou Faye',     '70 445 88 21', 'maçon'],
        ];
        $customers = [];
        foreach ($clientsData as [$n, $t, $etiq]) {
            $customers[] = Customer::create([
                'nom' => $n, 'telephone' => $t, 'adresse' => 'Pikine', 'etiquette' => $etiq,
            ]);
        }

        // === FOURNISSEURS ===
        foreach ([
            ['Sénégal Distribution', 'Cheikh Fall', '33 825 12 34', 'commande@sendis.sn'],
            ['Patisen SA',           'Service Achats', '33 859 70 00', null],
            ['Sonatel Distribution', 'Awa Diagne', '33 839 00 00', null],
        ] as [$n, $c, $t, $e]) {
            Supplier::create(['nom' => $n, 'contact' => $c, 'telephone' => $t, 'email' => $e]);
        }

        // === VENTES DEMO (pour avoir un historique réaliste) ===
        // Quelques ventes payées + crédits + partielles + dates variées
        $this->seedSale($vendeur, $customers[0], ['RIZ-PARF-5'=>2, 'BIS-IKRAM'=>2, 'COCA-15'=>1], 500, 'especes', 11000, now()->subHours(2));
        $this->seedSale($vendeur, $customers[1], ['HUI-NIIN-1'=>3, 'TOM-CONC-400'=>2], 0, 'especes', null, now()->subDays(1));
        $this->seedSale($vendeur, $customers[2], ['DENT-COL'=>1, 'SAV-MADA'=>5], 0, 'wave', null, now()->subDays(2));
        // Crédit: Fatou Ndiaye (déjà acheté ci-dessus, ajoutons un crédit aussi)
        $this->seedSaleCredit($vendeur, $customers[0], ['LAIT-POU-400'=>2, 'COUCH-PAMP'=>1], 11500, now()->subDays(6), now()->addDays(24));
        // Crédit en retard
        $this->seedSaleCredit($vendeur, $customers[1], ['HUI-NIIN-1'=>10, 'RIZ-PARF-5'=>5], 28900, now()->subDays(40), now()->subDays(10));
        // Partielle
        $this->seedSaleCredit($vendeur, $customers[3], ['CRD-OR-1000'=>20, 'CHG-USB-C'=>1], 22500, now()->subDays(3), now()->addDays(27), 11250);
        // Crédit en cours
        $this->seedSaleCredit($vendeur, $customers[4], ['BIS-IKRAM'=>10, 'JUS-MANGO'=>4], 6400, now()->subDays(1), now()->addDays(29), 3200);
        // En retard (-5j)
        $this->seedSaleCredit($vendeur, $customers[5], ['HUI-NIIN-1'=>15, 'LESS-OMO-1'=>4], 34800, now()->subDays(35), now()->subDays(5), 14250);
        // À jour
        $this->seedSale($vendeur, $customers[6], ['PAIN-BAG'=>3, 'BEURRE-200'=>1], 0, 'especes', null, now()->subDays(4));
        // Crédit récent
        $this->seedSaleCredit($vendeur, $customers[7], ['SUCRE-1'=>4, 'CAFE-SOL'=>2], 8800, now()->subHours(5), now()->addDays(29));
    }

    private function seedSale($user, $customer, array $items, float $remise, string $mode, ?float $paye, $date)
    {
        $itemsRows = [];
        $sousTotal = 0;
        foreach ($items as $ref => $qte) {
            $p = \App\Models\Product::where('reference', $ref)->first();
            if (!$p) continue;
            $st = (float)$p->prix_vente * $qte;
            $sousTotal += $st;
            $itemsRows[] = [
                'product' => $p, 'qte' => $qte, 'st' => $st,
            ];
            $p->decrement('stock', $qte);
        }
        $total = $sousTotal - $remise;
        $paye = $paye ?? $total;
        $statut = $paye >= $total ? 'payee' : ($paye > 0 ? 'partielle' : 'credit');

        $sale = Sale::create([
            'numero' => 'V-' . $date->format('md') . '-' . str_pad(Sale::count() + 1, 3, '0', STR_PAD_LEFT),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'client_nom' => $customer->nom,
            'client_tel' => $customer->telephone,
            'montant_total' => $total,
            'remise' => $remise,
            'montant_paye' => min($paye, $total),
            'mode_paiement' => $mode,
            'statut' => $statut,
            'echeance' => $statut !== 'payee' ? $date->copy()->addDays(30)->toDateString() : null,
            'created_at' => $date, 'updated_at' => $date,
        ]);
        foreach ($itemsRows as $r) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $r['product']->id,
                'produit_nom' => $r['product']->nom,
                'quantite' => $r['qte'],
                'prix_unitaire' => $r['product']->prix_vente,
                'sous_total' => $r['st'],
                'created_at' => $date, 'updated_at' => $date,
            ]);
        }
    }

    private function seedSaleCredit($user, $customer, array $items, float $totalCible, $date, $echeance, ?float $paye = 0)
    {
        $itemsRows = [];
        $sousTotal = 0;
        foreach ($items as $ref => $qte) {
            $p = \App\Models\Product::where('reference', $ref)->first();
            if (!$p) continue;
            $st = (float)$p->prix_vente * $qte;
            $sousTotal += $st;
            $itemsRows[] = ['product' => $p, 'qte' => $qte, 'st' => $st];
            $p->decrement('stock', $qte);
        }
        $remise = max(0, $sousTotal - $totalCible);
        $total = $sousTotal - $remise;
        $paye = $paye ?? 0;
        $statut = $paye >= $total ? 'payee' : ($paye > 0 ? 'partielle' : 'credit');

        $sale = Sale::create([
            'numero' => 'V-' . $date->format('md') . '-' . str_pad(Sale::count() + 1, 3, '0', STR_PAD_LEFT),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'client_nom' => $customer->nom,
            'client_tel' => $customer->telephone,
            'montant_total' => $total,
            'remise' => $remise,
            'montant_paye' => $paye,
            'mode_paiement' => 'especes',
            'statut' => $statut,
            'echeance' => $echeance->toDateString(),
            'created_at' => $date, 'updated_at' => $date,
        ]);
        foreach ($itemsRows as $r) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $r['product']->id,
                'produit_nom' => $r['product']->nom,
                'quantite' => $r['qte'],
                'prix_unitaire' => $r['product']->prix_vente,
                'sous_total' => $r['st'],
                'created_at' => $date, 'updated_at' => $date,
            ]);
        }
    }
}
