# P.A.S.T.A.
**P**recompilatore  
**A**bnorme della  
**S**IR (Scheda Identificazione Rischi occupazionali)  
**T**otalmente  
**A**utomatico  

Il solito script PHP da riga di comando che prende il modulo della SIR non
compilato (T-MOD-SIR.pdf) del Politecnico di Torino, un csv con i dati dei
lavoratori, e produce `n` file pdf compilati con i dati del csv, attraverso il
potere di LaTeX e del posizionamento assoluto dei paragrafi.

Si può adattare in maniera relativamente facile per compilare altri moduli,
modificando `template.tex`.

## Requisiti

PHP 7.0 o successive e `pdflatex`.

## Installazione

1. Scaricare `T-MOD-SIR.pdf` e `F-MOD-LABORATORI.pdf` dal sito del Politecnico
(si trovano nell'intranet, solo per utenti abilitati)
2. Metterli nella cartella da cui si lancerà pasta.php (current working directory)
3. Eseguire `bin/pasta.php tests/pasta tests/pasta/output`: data.csv e template.tex
verranno letti da tests/pasta, il SIR vuoto dalla directory attuale e l'output
andrà in tests/pasta/output. I nomi dei file sono hardcodati, 'spiace, ma tanto
lo script da riga di comando è un fallback, normalmente si usa quello integrato
nel C.R.A.U.T.O.

Se nel csv sono presenti le colonne NAME, SURNAME e ID (case sensitive) verranno
usate per generare il nome del file, altrimenti verranno usati dei numeri
progressivi (che corrispondono alla riga nel csv, peraltro).

Il template è stato costruito sulla base della versione 9 (18/05/2017) della
SIR, altre versioni potrebbero richiedere aggiustamenti.

