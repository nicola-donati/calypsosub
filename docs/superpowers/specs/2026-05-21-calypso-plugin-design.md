# Calypso Sub Arezzo — Plugin WordPress: Design Spec

**Data:** 2026-05-21  
**Progetto:** Plugin WordPress custom per ASD Calypso Sub Arezzo  
**Repo:** `/home/nik/Scrivania/Workspace/calypsosub`  
**Design reference:** `Calypso Sub Arezzo - design.html` (bundle HTML — usare per layout/stile, NON per i dati)

---

## 1. Obiettivo

Plugin WordPress monolitico per la gestione di uscite subacquee, eventi, corsi e docenti dell'ASD Calypso Sub Arezzo. Include sistema di prenotazione con conferma email, lista d'attesa opzionale e area personale utente.

---

## 2. Architettura

### Approccio
Plugin monolitico (opzione A). Tutta la logica — CPT, prenotazioni, email, admin, funzioni pubbliche — in un unico plugin.

### Struttura file

```
calypsosub/
├── calypsosub.php                      # entry point, headers, bootstrap
├── includes/
│   ├── post-types/
│   │   ├── class-cpt-uscite.php
│   │   ├── class-cpt-eventi.php
│   │   ├── class-cpt-corsi.php
│   │   └── class-cpt-docenti.php
│   ├── taxonomies/
│   │   └── class-tax-brevetti.php
│   ├── bookings/
│   │   ├── class-booking-manager.php   # logica prenotazioni + lista attesa
│   │   └── class-booking-email.php     # invio email
│   ├── admin/
│   │   ├── class-admin-menus.php
│   │   └── class-email-templates.php   # editor template email nell'admin
│   ├── account/
│   │   └── class-user-account.php      # area personale utente
│   └── helpers/
│       └── functions.php               # API pubblica (usata dai blocchi)
└── block-templates/                    # file PHP monolitici per i blocchi Gutenberg
    ├── block-uscite-lista.php
    ├── block-eventi-lista.php
    ├── block-corsi-lista.php
    ├── block-docenti-lista.php
    └── block-area-personale.php
```

### API pubblica (`functions.php`)
Funzioni chiamabili dai file-blocco:
- `calypso_get_uscite( $args )` — lista uscite con filtri
- `calypso_get_eventi( $args )` — lista eventi con filtri
- `calypso_get_corsi( $args )` — lista corsi con filtri
- `calypso_get_docenti( $args )` — lista docenti
- `calypso_get_user_bookings( $user_id )` — prenotazioni utente
- `calypso_can_book( $post_id, $user_id )` — check disponibilità
- `calypso_book( $post_id, $user_id, $data )` — crea prenotazione
- `calypso_cancel_booking( $booking_id, $user_id )` — cancella prenotazione
- `calypso_is_user_logged_in()` — wrapper estendibile per auth (WP nativo ora, sostituibile in futuro con plugin membership)

### Frontend — file blocchi
Un file PHP per blocco, autocontenuto: PHP + HTML + `<style>` inline + `<script>` inline dove necessario. L'utente copia i file in `block-templates/` nel proprio plugin blocchi Gutenberg. Modifiche presentazione senza toccare il plugin core.

---

## 3. Custom Post Types

### 3.1 Uscite (`calypso_uscita`)

| Campo | Tipo | Note |
|-------|------|------|
| Titolo | WP nativo | |
| Sottotitolo | text | |
| Descrizione | WP editor | |
| Descrizione breve | textarea | |
| Immagine | WP featured image | |
| Luogo | text | |
| Ritrovo | text | |
| Date | repeater (data + ora) | più date supportate |
| Max partecipanti | number | opzionale; se vuoto = libera partecipazione |
| Accompagnatori max | number | non contano nel max partecipanti |
| Lista d'attesa | checkbox | opzionale, abilitabile per uscita |
| Incluso nell'uscita | textarea | opzionale |
| Cosa portare | textarea | opzionale |
| Note cancellazione | textarea | opzionale |

### 3.2 Eventi (`calypso_evento`)
(cene, ritrovi, presentazioni)

| Campo | Tipo | Note |
|-------|------|------|
| Titolo | WP nativo | |
| Sottotitolo | text | |
| Descrizione | WP editor | |
| Descrizione breve | textarea | |
| Immagine | WP featured image | |
| Luogo | text | |
| Date | repeater (data + ora) | |
| Max partecipanti | number | opzionale |
| Lista d'attesa | checkbox | opzionale |

### 3.3 Corsi (`calypso_corso`)

| Campo | Tipo | Note |
|-------|------|------|
| Titolo | WP nativo | |
| Sottotitolo | text | |
| Descrizione | WP editor | |
| Descrizione breve | textarea | |
| Immagine | WP featured image | |
| Data inizio | date | |
| Data fine | date | |
| Date lezioni | repeater (data + ora) | |
| Luogo | text | |
| Direttore corso | post select (Docenti) | relazione singola |
| Docenti | post select multiplo (Docenti) | relazione multipla |
| Materiale incluso | textarea | opzionale |

I corsi **non hanno prenotazione**.

### 3.4 Docenti (`calypso_docente`)

| Campo | Tipo | Note |
|-------|------|------|
| Nome | text | |
| Cognome | text | |
| Account WP | user select | collegamento a utente WP registrato (opzionale) |
| Immagine profilo | image | |
| Galleria foto | gallery (repeater) | |
| Bio | WP editor | |
| Specializzazioni | textarea | |
| Anni di esperienza | number | |
| Ruolo nel club | text | |
| Email | email | |
| Telefono | text | |
| Social | repeater (nome + URL) | |
| Brevetti | taxonomy `calypso_brevetto` | |

---
⚠ Articoli non inseriti (1)
Codice 	Motivo 	Dettaglio
2571525053 	Descrizione non corrispondente 	Nessun fornitore PCM selezionato (DescrizioneNonCorrispondente)
Codice 	Stato 	Fornitore 	Prezzo 	Consegna 	Sim. 	Note
2571525053 	descrizione_non_corrispondente 				— 	Nessun fornitore PCM selezionato (DescrizioneNonCorrispondente)
2571525052 	added 	HOFFMANN ITALIA SPA 	43.7500 		17 % 	
2571525050 	added 	HOFFMANN ITALIA SPA 	23.2800 		17 %

## 4. Taxonomy

### Brevetti (`calypso_brevetto`)
- Taxonomy condivisa, lista fissa di brevetti subacquei
- Solo nome
- Usata per taggare i Docenti
- Non gerarchica

---

## 5. Prenotazioni

### CPT privato (`calypso_prenotazione`)
Non visibile nel frontend direttamente. Gestito internamente.

| Meta | Tipo | Note |
|------|------|------|
| `_booking_post_id` | int | ID uscita o evento |
| `_booking_post_type` | string | `calypso_uscita` o `calypso_evento` |
| `_booking_user_id` | int | ID utente WP |
| `_booking_companions` | int | numero accompagnatori |
| `_booking_allergies` | text | solo per uscite; copre utente + accompagnatori |
| `_booking_status` | string | `confermata`, `annullata`, `lista_attesa` |
| `_booking_date` | datetime | data creazione prenotazione |

### Flusso prenotazione

1. Utente loggato clicca "Prenota" nel blocco
2. Form: numero accompagnatori + allergie/intolleranze (solo uscite)
3. Check disponibilità: `max_partecipanti - prenotati_confermati` (accompagnatori esclusi dal conteggio)
4. Se posti disponibili → crea prenotazione con stato `confermata`
5. Se pieno + lista attesa attiva → offre posto in lista attesa → stato `lista_attesa`
6. Se pieno + lista attesa disattiva → prenotazione chiusa
7. Email conferma all'utente + notifica alla lista email admin configurabile

### Flusso cancellazione

1. Utente dall'area personale clicca "Cancella"
2. Stato prenotazione → `annullata`
3. Se esistono prenotazioni in `lista_attesa` per quell'evento → il primo in lista viene promosso automaticamente a `confermata` e riceve email di notifica (nessuna azione richiesta)

### Conteggio posti
- Max partecipanti = numero di **utenti** prenotati (esclusi accompagnatori)
- Accompagnatori sono extra, non occupano posti del limite
- Posti rimanenti = `max_partecipanti - COUNT(prenotazioni con stato 'confermata')`

---

## 6. Area personale utente

Blocco `block-area-personale.php` da inserire in pagina dedicata.

Contenuto:
- Lista prenotazioni attive (uscite + eventi) ordinate per data
- Per ogni prenotazione: titolo evento, data, stato, num accompagnatori
- Pulsante "Cancella prenotazione" per ognuna
- Sezione storico prenotazioni passate/annullate

---

## 7. Email

### Template personalizzabili da admin
Editor nell'area admin WP. Ogni template ha: oggetto + corpo HTML.

| Template | Trigger |
|----------|---------|
| Conferma prenotazione | utente prenota |
| Prenotazione annullata (utente) | utente cancella |
| Promosso da lista attesa | cancellazione libera posto |
| Notifica admin nuova prenotazione | utente prenota |

### Variabili disponibili nei template
`{nome_utente}`, `{email_utente}`, `{titolo_evento}`, `{data_evento}`, `{luogo}`, `{num_accompagnatori}`, `{allergie}`, `{stato_prenotazione}`, `{link_area_personale}`, `{data_prenotazione}`

### Invio
`wp_mail()` — compatibile con qualsiasi plugin SMTP già installato sul sito.

### Lista notifiche admin
Campo testo nell'admin: lista di email separate da virgola che ricevono notifica ad ogni nuova prenotazione.

---

## 8. Ruoli e permessi

- **Admin WP**: accesso completo a tutti i CPT, impostazioni plugin, template email
- **Editor WP**: gestione CPT (uscite, eventi, corsi, docenti) senza accesso alle impostazioni plugin
- **Utente registrato**: solo prenotazione e area personale
- **Visitatore**: solo consultazione frontend

---

## 9. Internazionalizzazione

- Tutte le stringhe PHP wrapped in `__()` / `_e()` con text-domain `calypsosub`
- File `.pot` generato con WP-CLI
- Traduzione iniziale: solo italiano (`it_IT`)
- Predisposto per WPML/Polylang in futuro senza modifiche al core

---

## 10. Frontend

- Lista ordinata per data (più imminente prima)
- Filtri per tipo, luogo, data (definiti dal design reference `design.html`)
- Nessun calendario interattivo — lista cronologica con filtri
- Blocchi Gutenberg: file PHP autocontenuti in `block-templates/`
- Responsive: seguire layout da `design.html`

---

## 11. Decisioni tecniche

| Scelta | Decisione | Motivazione |
|--------|-----------|-------------|
| Architettura | Monolitico | Semplicità, tutto gestito da admin |
| Meta box | WP nativo | Zero dipendenze; CMB2 non mantenuto attivamente |
| Blocchi | File PHP monolitici separati | Modificabili senza toccare plugin core |
| Auth | WP nativo con wrapper | Estendibile per membership plugin futuro |
| Email | `wp_mail()` | Compatibile con SMTP plugin esistenti |
| i18n | `__()` / `_e()` | Predisposto multilingua, solo IT ora |
| Pagamenti | Nessuno | Solo prenotazione gratuita |
