# WBCE-Template „eiziben" – README


## Dateistruktur

```
eiziben/
├── index.php      Haupt-Template-Datei (PHP + HTML)
├── info.php       Template-Metadaten für WBCE
├── style.css      Alle Styles (Design Tokens, Navigation, Layout, Footer …)
├── img/
│   └── hero.jpg   Globales Hero-Bild (ersetzen durch eigenes JPEG/PNG)
└── README.md      Diese Datei
```

## Hero-Bild

- **Global:** `img/hero.jpg` – wird auf allen Seiten angezeigt.
- **Seitenspezifisch:** `img/hero-{PAGE_ID}.jpg` – überschreibt das globale Bild
  für die Seite mit der jeweiligen ID (z. B. `img/hero-5.jpg` für Seite 5).
- **Kein Bild:** Fehlt `hero.jpg`, zeigt die Startseite einen Farbverlauf als
  Platzhalter. Unterseiten zeigen gar keinen Hero-Bereich.

## Inhaltsblöcke

| Block | Verwendung |
|-------|-----------|
| Block 1 | Hauptinhalt (Pflicht) |
| Block 2 | Sidebar (optional – wird ausgeblendet, wenn leer) |
| Block 99 | keine Ausgabe im Frontend |


## Navigation (show_menu2)

- Menü 1 → Hauptnavigation (bis zu 3 Ebenen).
- Desktop: Ebene 2 öffnet per Hover als Dropdown, Ebene 3 als Fly-out.
- Mobil: Hamburger-Button → Slide-in-Panel, Ebenen durch Antippen aufklappen.
- WBCE vergibt CSS-Klassen `menu-current`, `menu-parent`, `menu-expand`
  (= hat Unterseiten) automatisch.

## Breadcrumb

Wird automatisch ausgeblendet auf der Root-Ebene (LEVEL == 0).

## Footer

Inhalt wird über **Optionen → Allgemeine Optionen → Fußzeile** im Backend gepflegt
(`page_footer()`). Ist die Suche aktiv, erscheint ein kleines Suchfeld im Footer.

## Anpassung

Alle Farben, Schriften und Abstände sind als CSS Custom Properties (Design Tokens)
in `style.css` ganz oben unter `:root { … }` zusammengefasst und können dort
zentral geändert werden.
