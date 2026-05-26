# Istruzioni — Google Document AI per Jlune App

**Per:** Serenella / titolare progetto  
**Obiettivo:** far leggere i documenti (CI, tessera sanitaria) dall’app Jlune usando **il vostro** account Google Cloud, con fatturazione sul vostro metodo di pagamento.

---

## 1. Perché serve

L’app usa **Document AI (OCR)** per estrarre da foto:
- cognome, nome, data di nascita dalla carta d’identità;
- codice fiscale dalla tessera sanitaria.

Oggi il servizio è collegato a un account Google del team di sviluppo (**fatturazione temporanea**).  
Per andare in produzione stabile conviene spostare tutto su **un account Google di Serenella** (o società).

---

## 2. Cosa vi serve

- Un account Google (Gmail o Google Workspace).
- Carta di credito/debito per Google Cloud (fatturazione pay-as-you-go).
- Circa **15–30 minuti** la prima volta.

**Costi indicativi Google** (variabili, controllare sempre la console Google):  
Document AI OCR si paga a pagina elaborata; per pochi check-in al mese l’importo è di solito **molto basso** (ordine di pochi euro), ma dipende dal volume.

---

## 3. Passo passo — Creare progetto e Document AI

### 3.1 Accedi a Google Cloud
1. Apri: https://console.cloud.google.com/
2. Accedi con l’account Google che userete per Jlune.

### 3.2 Crea un progetto (se non ne avete già uno)
1. In alto: seleziona progetto → **Nuovo progetto**.
2. Nome esempio: `jlune-app` o `serenella-app`.
3. Annotate il **Project ID** (es. `serenella-app`) — serve per il file `.env` dell’app.

### 3.3 Abilita Document AI
1. Menu → **API e servizi** → **Libreria**.
2. Cerca **Cloud Document AI API**.
3. Clic **Abilita**.

### 3.4 Crea il processor OCR
1. Menu → **Document AI** → **Processori** (Processors).
2. Regione: **United States (us)** — importante, non EU se il progetto è configurato US.
3. **Crea processore** → tipo **Document OCR**.
4. Nome esempio: `jlune-document-ocr`.
5. Quando è **Abilitato**, aprite il processore e copiate l’**ID processore** (stringa tipo `2bf88abe4f2f2f04`).

### 3.5 Account di servizio e file credenziali (JSON)
1. Menu → **IAM e amministrazione** → **Account di servizio**.
2. **Crea account di servizio** (es. `jlune-document-ai`).
3. Ruolo: **Document AI API User** (o equivalente con permessi Document AI).
4. Scheda **Chiavi** → **Aggiungi chiave** → **Crea nuova chiave** → **JSON**.
5. Il browser scarica un file `.json` — **conservatelo in luogo sicuro** (non si può riscaricare).
6. Rinominate il file in: `google-credentials.json`.

> **Attenzione:** il file JSON contiene la chiave privata. Non inviatelo su WhatsApp in chiaro; preferite passaggio sicuro (email criptata, USB, o consegna al tecnico).

---

## 4. Cosa inviare al team Jlune

Inviate al tecnico che gestisce il server (Plesk) questi dati:

| Dato | Esempio |
|------|---------|
| Project ID | `serenella-app` |
| Regione processor | `us` |
| ID processore | `2bf88abe4f2f2f04` |
| File JSON | `google-credentials.json` (allegato sicuro) |

Il tecnico caricherà il file sul server in:  
`storage/app/google-credentials.json`  
e imposterà nel file `.env` di produzione:

```env
GOOGLE_CLOUD_PROJECT_ID=serenella-app
GOOGLE_DOCUMENT_AI_LOCATION=us
GOOGLE_DOCUMENT_AI_PROCESSOR_ID=il_vostro_id_qui
GOOGLE_APPLICATION_CREDENTIALS=/percorso/assoluto/sul/server/storage/app/google-credentials.json
```

Poi eseguirà sul server:  
`php artisan jlune:google-check`  
deve rispondere: **Connessione a Document AI OK**.

---

## 5. Verifica in app

1. Admin → **Arrivi e documenti** → prenotazione di test.
2. Approvate i documenti.
3. Clic **Estrai dati (Document AI)** (attendere 1–2 minuti).
4. Controllate cognome, nome, CF nel contratto.
5. Opzionale: **Esporta JSON / CSV / XML** sotto i dati estratti.

---

## 6. Domande frequenti

**Il JSON si può riscaricare?**  
No. Se lo perdete, create una **nuova chiave** nell’account di servizio e eliminate la vecchia.

**Possiamo usare l’account del tecnico per sempre?**  
Sì in fase iniziale, ma la fattura Google resta a suo carico. Per chiarezza conviene il **vostro** account.

**Serve anche per la Polizia di Stato (Alloggiati)?**  
Il formato ufficiale per la questura è **diverso** (XML/portale Alloggiati Web). Lo valuteremo con Serenella; potrebbe essere una voce extra in futuro.

---

## 7. Contatti

Per problemi tecnici: team sviluppo Jlune.  
Per decisioni su costi e formato Polizia: Serenella + team.

_Ultimo aggiornamento: maggio 2026_
