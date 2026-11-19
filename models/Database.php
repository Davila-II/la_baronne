<?php
/**
 * Database connection & ORM helper for DjoKhouse SCI
 */
class Database {
    private static ?PDO $pdo = null;

    public static function getConn(): PDO {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../data/djokhouse.db';

            self::$pdo = new PDO('sqlite:' . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            self::initSchema();
            self::seedData();
        }
        return self::$pdo;
    }

    public static function initSchema(): void {
        $db = self::$pdo;

        $db->exec("
            CREATE TABLE IF NOT EXISTS site_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                site_name TEXT NOT NULL,
                slogan TEXT,
                phone TEXT,
                phone2 TEXT,
                email TEXT,
                address TEXT,
                about_summary TEXT,
                facebook TEXT,
                linkedin TEXT,
                whatsapp TEXT
            );

            CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                email TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS services (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT UNIQUE NOT NULL,
                title TEXT NOT NULL,
                summary TEXT,
                content TEXT,
                image TEXT,
                icon TEXT,
                order_num INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1
            );

            CREATE TABLE IF NOT EXISTS properties (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT,
                category TEXT NOT NULL,
                type TEXT DEFAULT 'Vente',
                location TEXT,
                surface TEXT,
                price TEXT,
                description TEXT,
                image TEXT,
                gallery TEXT,
                is_featured INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS testimonials (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                author TEXT NOT NULL,
                role TEXT,
                avatar TEXT,
                content TEXT NOT NULL,
                rating INTEGER DEFAULT 5,
                is_active INTEGER DEFAULT 1
            );

            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT,
                subject TEXT,
                message TEXT NOT NULL,
                is_read INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS reservations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                property_id INTEGER NULL,
                service_id INTEGER NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT NOT NULL,
                details TEXT,
                status TEXT DEFAULT 'En attente',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS newsletter (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    public static function seedData(): void {
        $db = self::$pdo;

        // Check if settings exist
        $count = $db->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
        if ($count == 0) {
            $stmt = $db->prepare("
                INSERT INTO site_settings (site_name, slogan, phone, phone2, email, address, about_summary, facebook, linkedin, whatsapp)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'DjoKhouse SCI',
                'Un Camerounais, un terrain',
                '(+237) 696 052 390',
                '(+237) 670 123 456',
                'contact@djokhouse.cm',
                'Douala, Cameroun',
                'DjoKhouse SCI est votre partenaire de confiance au Cameroun pour l’acquisition, la sécurisation foncière, les études topographiques et l’urbanisme. Notre mot d’ordre : Un Camerounais, un terrain.',
                'https://facebook.com',
                'https://linkedin.com',
                '(+237) 696 052 390'
            ]);
        }

        // Check if admin user exists (admin / admin123)
        $adminCount = $db->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        if ($adminCount == 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO admin_users (username, password_hash, email) VALUES (?, ?, ?)");
            $stmt->execute(['admin', $hash, 'admin@djokhouse.cm']);
        }

        // Seed Services
        $servCount = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
        if ($servCount == 0) {
            $services = [
                [
                    'conseils',
                    'Conseils Fonciers & Immobiliers',
                    'Accompagnement, traitement, suivi et conseils pratiques et techniques dans les procédures foncières et immobilières.',
                    'Nous vous guidons étape par étape dans toutes vos démarches pour sécuriser vos investissements fonciers au Cameroun.',
                    'images/services/1.jpg',
                    'fal fa-balance-scale',
                    1
                ],
                [
                    'urbanisme',
                    'Urbanisme & Aménagement',
                    'Création de lotissements, aménagement sur terrain rural/urbain, permis de construire et d’aménager.',
                    'Études d’impact urbain, lotissements clés en main, démarches d’obtention de certificats d’urbanisme.',
                    'images/services/2.jpg',
                    'fal fa-city',
                    2
                ],
                [
                    'topographie',
                    'Topographie & Génie Civil',
                    'Levé topographique, nivellement, division de parcelle, plans architecturaux et suivi de chantiers BTP.',
                    'Matériel de pointe pour des relevés de haute précision géolocalisés et conformes aux exigences du Cadastre.',
                    'images/services/3.jpg',
                    'fal fa-compass',
                    3
                ],
                [
                    'bornage',
                    'Bornage & Sécurisation Foncière',
                    'Délimitation officielle de parcelles, pose de bornes agréées et facilitation pour l’obtention du Titre Foncier.',
                    'Évitez les litiges de mitoyenneté et sécurisez définitivement vos limites de propriété.',
                    'images/services/4.jpg',
                    'fal fa-map-marked-alt',
                    4
                ],
                [
                    'prestation',
                    'Prestation de Services & BTP',
                    'Suivi, contrôle et pilotage des travaux de construction et d’aménagement partout au Cameroun.',
                    'Assistance technique globale pour garantir la qualité d’exécution et le respect des normes.',
                    'images/services/5.jpg',
                    'fal fa-tools',
                    5
                ],
            ];

            $stmt = $db->prepare("INSERT INTO services (slug, title, summary, content, image, icon, order_num) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($services as $s) {
                $stmt->execute($s);
            }
        }

        // Seed Properties / Projects
        $propCount = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
        if ($propCount == 0) {
            $properties = [
                [
                    'Terrains Résidentiels PK14 - Douala',
                    'terrains-residentiels-pk14-douala',
                    'Terrain',
                    'Vente',
                    'PK14, Douala',
                    '500 m²',
                    '15 000 000 FCFA',
                    'Magnifiques parcelles viabilisées avec accès eau et électricité. Titre foncier disponible et immédiatement cessible.',
                    'images/folio/1.jpg',
                    1
                ],
                [
                    'Lotissement Moderne Yassa - Douala',
                    'lotissement-moderne-yassa-douala',
                    'Urbanisme',
                    'En cours',
                    'Yassa, Douala',
                    '10 000 m²',
                    'Sur demande',
                    'Projet d’aménagement d’un lotissement résidentiel haut de gamme avec voiries pavées et espaces verts.',
                    'images/folio/2.jpg',
                    1
                ],
                [
                    'Bornage et Titre Foncier Kribi Bord de Mer',
                    'bornage-titre-foncier-kribi',
                    'Bornage',
                    'Projet réalisé',
                    'Kribi',
                    '2 500 m²',
                    'Réalisé avec succès',
                    'Opération de bornage contradictoire et accompagnement complet jusqu’à la délivrance du titre foncier.',
                    'images/folio/3.jpg',
                    1
                ],
                [
                    'Levé Topographique & VRD Yaoundé Olembe',
                    'leve-topographique-yaounde-olembe',
                    'Topographie',
                    'Projet réalisé',
                    'Olembe, Yaoundé',
                    '4 800 m²',
                    'Réalisé avec succès',
                    'Levé topographique complet avec courbes de niveau pour l’implantation d’un complexe immobilier moderne.',
                    'images/folio/6.jpg',
                    1
                ],
                [
                    'Parcelles Agricoles & Écologiques Dibombari',
                    'parcelles-agricoles-dibombari',
                    'Terrain',
                    'Vente',
                    'Dibombari, Littoral',
                    '20 000 m²',
                    '2 500 FCFA / m²',
                    'Idéal pour projets agropastoraux ou investissement d’avenir. Proche de la route nationale et sol fertile.',
                    'images/folio/7.jpg',
                    1
                ],
                [
                    'Résidence Art Family - Bonamoussadi',
                    'residence-art-family-bonamoussadi',
                    'Maison',
                    'Vente',
                    'Bonamoussadi, Douala',
                    '350 m²',
                    '65 000 000 FCFA',
                    'Villa contemporaine 4 chambres avec finitions soignées, parking et clôture sécurisée.',
                    'images/folio/9.jpg',
                    1
                ]
            ];

            $stmt = $db->prepare("INSERT INTO properties (title, slug, category, type, location, surface, price, description, image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($properties as $p) {
                $stmt->execute($p);
            }
        }

        // Seed Testimonials
        $testiCount = $db->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
        if ($testiCount == 0) {
            $testimonials = [
                [
                    'Antoine Kamga',
                    'Propriétaire à Douala',
                    'images/avatar/user.png',
                    'Je remercie chaleureusement DjoKhouse SCI de m’avoir accompagné pour l’acquisition et les procédures foncières de mon terrain. Suivi transparent et rigoureux à chaque étape !',
                    5
                ],
                [
                    'Marie-Claire Ngo',
                    'Membre de la Diaspora (France)',
                    'images/avatar/user.png',
                    'Depuis l’étranger, acquérir un terrain au pays sans risque est difficile. DjoKhouse SCI a sécurisé mon achat avec un titre foncier authentique. Un Camerounais, un terrain : la promesse est tenue !',
                    5
                ],
                [
                    'Patrick Mbida',
                    'Promoteur Immobilier',
                    'images/avatar/user.png',
                    'La précision des levés topographiques et l’efficacité pour le bornage m’ont fait gagner un temps précieux sur mon chantier. Une équipe d’experts hautement recommandée.',
                    5
                ]
            ];

            $stmt = $db->prepare("INSERT INTO testimonials (author, role, avatar, content, rating) VALUES (?, ?, ?, ?, ?)");
            foreach ($testimonials as $t) {
                $stmt->execute($t);
            }
        }
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getConn()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function insert(string $table, array $data): int {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        self::query($sql, array_values($data));
        return (int)self::getConn()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = [];
        $values = [];
        foreach ($data as $col => $val) {
            $set[] = "{$col} = ?";
            $values[] = $val;
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $stmt = self::query($sql, array_merge($values, $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $whereParams = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $whereParams);
        return $stmt->rowCount();
    }
}
