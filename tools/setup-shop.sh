#!/usr/bin/env bash
# Richtet ein bestehendes WordPress für den Polinis-Shop ein.
#
# Voraussetzungen: WordPress läuft, WP-CLI ist installiert, dieses Repo liegt auf dem Server.
# Aufruf im WordPress-Verzeichnis:
#   bash /pfad/zum/repo/tools/setup-shop.sh
#
# Was passiert:
#   1. WooCommerce installieren und auf Deutschland/Euro stellen
#   2. Theme „Polinis ON/OFF“ verlinken und aktivieren
#   3. Versandkostenfrei für Deutschland anlegen
#   4. Seiten für Rechtstexte anlegen (leer – Texte aus dem alten Shop einfügen!)
#   5. Produkte aus dem bisherigen Shop importieren
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP="${WP:-wp}"

echo "→ WooCommerce"
$WP plugin is-installed woocommerce || $WP plugin install woocommerce
$WP plugin activate woocommerce
$WP language core install de_DE --activate || true
$WP language plugin install woocommerce de_DE || true

$WP option update woocommerce_default_country DE
$WP option update woocommerce_currency EUR
$WP option update woocommerce_currency_pos right_space
$WP option update woocommerce_price_thousand_sep .
$WP option update woocommerce_price_decimal_sep ,
$WP option update woocommerce_prices_include_tax yes
$WP option update woocommerce_tax_display_shop incl
$WP option update woocommerce_tax_display_cart incl
$WP option update woocommerce_coming_soon no
$WP rewrite structure '/%postname%/'

echo "→ Theme"
THEMES="$($WP eval 'echo get_theme_root();')"
if [ ! -e "$THEMES/polinis" ]; then
	ln -s "$REPO/wp-content/themes/polinis" "$THEMES/polinis"
fi
$WP theme activate polinis

echo "→ Versand: kostenlos in Deutschland"
ZONE=$($WP eval '
$zones = WC_Shipping_Zones::get_zones();
foreach ( $zones as $z ) { if ( $z["zone_name"] === "Deutschland" ) { echo $z["id"]; exit; } }
$zone = new WC_Shipping_Zone();
$zone->set_zone_name( "Deutschland" );
$zone->add_location( "DE", "country" );
$zone->save();
$zone->add_shipping_method( "free_shipping" );
echo $zone->get_id();')
echo "   Zone $ZONE"

echo "→ Seiten"
for PAGE in "Impressum:impressum" "Datenschutz:datenschutz" "AGB:agb" "Widerrufsrecht:widerrufsrecht" "Versand:versand" "Kontakt:kontakt"; do
	TITLE="${PAGE%%:*}"
	SLUG="${PAGE##*:}"
	if [ -z "$($WP post list --post_type=page --name="$SLUG" --field=ID)" ]; then
		$WP post create --post_type=page --post_status=publish --post_title="$TITLE" --post_name="$SLUG" \
			--post_content="<p><strong>Platzhalter:</strong> Hier den Text „$TITLE“ aus dem bisherigen Shop einfügen.</p>"
	fi
done
$WP option update show_on_front posts

echo "→ Produkte"
$WP eval-file "$REPO/tools/import-shopify.php"

echo "Fertig. Startseite öffnen und den Schalter umlegen."
