# Polinis ON/OFF – Design System

Zwei Welten, ein System. Der Zustand hängt an `html[data-signal="off|on"]`; alle Farben sind Tokens in `assets/css/main.css` und werden pro Zustand neu belegt.

## Farben

| Token | OFF (S-01, dunkel) | ON (S-02, hell) | Rolle |
|---|---|---|---|
| `--bg` | `#0d0c11` | `#f3f2f6` | Seitengrund |
| `--bg-2` | `#17151d` | `#e8e6ee` | Abgesetzte Fläche |
| `--ink` | `#efedf4` | `#141218` | Text |
| `--ink-2` | `#aaa5b9` | `#575169` | Sekundärtext (≥ 7:1) |
| `--line` | `#2c2935` | `#d2cfdb` | Linien |
| `--link` | `#b29bff` | `#4a1fcc` | Links, Fokus |
| `--violet` | `#5124d6` | `#5124d6` | Markenfeld: Laufband, Manifest, Hauptbutton |
| `--on-violet` | `#ffffff` | `#ffffff` | Text auf Violett (8,7:1) |
| `--tile` | `#e9e8ed` + multiply | `#ffffff` | Hintergrund der Produktfotos |

Farbstrategie: **Committed.** Violett besetzt ganze Flächen (Laufband, Manifest, Hauptaktion), nie als Leuchten oder Verlauf. Der Footer ist in beiden Welten dunkel.

Das Violett ist aus den Drucken gemessen (S-02-Collage ≈ `#4818b8`) und für Bildschirme leicht aufgehellt.

## Schrift

| Rolle | Schrift | Einstellung |
|---|---|---|
| Display (Headlines) | Archivo Variable | `font-stretch: 125%`, 850, Großbuchstaben, Zeilenhöhe 0,88–0,92, max. 6rem |
| Text | Archivo Variable | `font-stretch: 100%`, 400–700, 16px Basis |
| Schalter-Beschriftung | Archivo Variable | `font-stretch: 62%`, 900, schräg (`skewX(-9deg)`) wie auf dem Druck |
| Daten | JetBrains Mono | 500, 11–13px, Großbuchstaben, nur für Artikelnummern, Größen, Koordinaten, Zähler |

## Bausteine

- **Schalter** (`.switch--hero`, `.switch--mini`): SVG-Nachzeichnung des S-01-Drucks, zwei Farben (`--sw-house`, `--sw-slot`), keine Schatten. OFF: obere Hälfte erhaben (schräge Kante, kursives ON), untere als schwarzes Feld. ON gespiegelt. In der hellen Welt invertiert. `aria-pressed="true"` = ON.
- **Entwurf** (`.next__item`): geplante Serie mit Mockup, Etikett „Entwurf“, Code in Violett im Titel, Satz, Text, Basis.
- **Rahmen** (`.frame`): Eckmarken 18px, 1px, 10px außerhalb – wie auf den Drucken.
- **Buttons** (`.btn--primary`, `.btn--ghost`): eckig, 52–56px hoch, Großbuchstaben, Hover tauscht auf Textfarbe.
- **Produktkarte** (`.card`): quadratisches Foto, Serien-Etikett oben links, beim Hover wischt der Rückendruck von unten ein (`clip-path`).
- **Chips** (`.chip`): Filter auf Shop-Seiten, aktiv = invertiert.
- **Varianten** (`.swatch`): Farbfelder und Größenknöpfe, erzeugt per JS aus den WooCommerce-Selects (die Selects bleiben die Quelle).

## Bewegung

Ein Moment: Umschalten wächst als Kreis vom Schalter aus (View Transitions, 0,75s, Ease-out). Der Schalter selbst rastet dabei kurz ein (0,22s). Daneben nur ruhige Zustandswechsel (Kippen des Schalters, Wischen der Karten, Pfeile). `prefers-reduced-motion` schaltet alles ab, das Laufband steht dann still.

## Raster

12 Spalten, max. 1560px, Rand `clamp(16px, 3.2vw, 48px)`. Abstände 4 · 8 · 12 · 16 · 24 · 32 · 48 · 72 · 112px.
