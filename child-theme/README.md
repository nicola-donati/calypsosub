# Calypso Sub Arezzo — Child Theme

Tema child WordPress costruito a partire dal design `Calypso Sub Arezzo.html`.

## Cosa c'è dentro

| File | Cosa contiene | Modificabile da editor WP? |
|---|---|---|
| `theme.json` | Palette, font, dimensioni testo, spacing, ombre, gradienti, stili globali di h1–h6, link, button, quote, ecc. | ✅ sì — è il "design system" esposto a Gutenberg |
| `style.css` | Header del child theme + utility CSS che l'editor non gestisce (placeholder striped, btn ghost/dark, eyebrow, mark, ph, focus visibili, form, ecc.) | ❌ no — codice |
| `functions.php` | Carica lo style del parent + child, registra le varianti `is-style-ghost`, `is-style-dark`, ecc. per i blocchi Gutenberg | ❌ no — codice |

## Installazione

1. Apri `style.css` e sostituisci `Template: PARENT_THEME_SLUG` con lo slug del tema genitore (es. `twentytwentyfive`, `twentytwentyfour`, o il tema che usi).
2. Zippa la cartella `wp-child-theme/` (rinominala come vuoi, es. `calypso-sub-child`).
3. Caricala da **Aspetto → Temi → Aggiungi nuovo → Carica tema**.
4. Attivala.

## Cosa NON è in style.css (sta tutto in theme.json)

Queste cose le modifichi dall'editor di WordPress senza toccare codice:

- **Colori** (Aspetto → Editor → Stili → Colori): tutta la palette `abyss / deep / wave / aqua / foam / sand / sand-warm / coral / coral-deep / bone / ink`.
- **Tipografia** (Stili → Tipografia): Big Shoulders Display (display), DM Sans (body), JetBrains Mono (mono); 10 size preset (`xs → hero`).
- **Spaziatura** (Stili → Layout): preset da `10 (8px)` a `80 (128px)`.
- **Stile di h1–h6, link, button, quote, pullquote, code, table**: già configurati in `theme.json > styles.elements` e `styles.blocks`.
- **Ombre & gradienti**: preset `sm / md / lg / coral` e gradienti `ocean / abyss-deep / sand-warm / coral-flame`.
- **Duotone**: preset `ocean / sand-ink / coral-deep` per le immagini.

Tutto questo è modificabile da **Aspetto → Editor → Stili** senza FTP.

## Cosa C'È in style.css (perché l'editor non lo copre)

- Placeholder striped per le immagini mancanti (`.ph`, `.ph-sand`).
- Variante bottone "ghost" e "dark" (con hover lift) — l'editor di Gutenberg permette il colore di background ma non hover/transform/shadow custom.
- Utility `.eyebrow`, `.display`, `.mark`, `.stat-num`, `.stat-label`.
- Form (`input`, `textarea`, `select`, `label`) — WP non li stila di default.
- Tabelle, scrollbar webkit, focus-visible.
- Selezione testo color corallo.
- `.dark-section` per gruppi su sfondo scuro (cambia automaticamente i colori dei titoli e dei link interni).

## Come usare le utility nell'editor

Quasi tutte queste classi si applicano da **Avanzate → Classi CSS aggiuntive** sul blocco:

| Classe | Effetto |
|---|---|
| `eyebrow` | Testo monospace minuscolo uppercase color wave |
| `display` | Titolo oversize uppercase tight |
| `stat-num` + `stat-label` | Numero stat oversize + label sotto |
| `mark` (o tag `<mark>`) | Highlight turchese |
| `dark-section` | Group/Cover su fondo scuro — adatta tutti i testi |
| `card` | Card bianca con hover lift |

Per il bottone, dopo aver aggiunto il tema apparirà nel pannello **Stili** del blocco Button: **Default / Ghost / Dark**.

## Aggiornare la palette in futuro

Se vuoi cambiare i colori, fallo da **Aspetto → Editor → Stili → Colori** — quella modifica viene salvata in `wp_global_styles` e ha la priorità su `theme.json`. Per ripristinare la palette di default basta resettare gli stili globali.
