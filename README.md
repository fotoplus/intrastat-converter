# intrastat

Az intrastat import/export sablonoknak megfelelő CSV-t készít a beillesztett táblázat alapján.
A táblázatnak csak a kitöltött mezőit szabad bemásolni, fejlécek és egyéb sorok nélkül!


---

## OSAP 2010 - Export / Kiszállítás : 9 feldolgozandó oszlop

2010 INTRASTAT Kiszállítás CSV SABLON [INTRASTAT Dispatches, CSV file]
----------------------------------------------------------------
FEJEZET(karakter): Fejezet azonosító, 0 - Elõlap [Chapter ID, 0 - Preface]
SORREND(numerikus 3 karakter): Elõlap kötelezõen 1 [Numeric, 3 characters, must be 1]
MC01 (4 karakter): OSAP száma - kötelezõen 2010 [OSAP number, must be 2010]
M003_G (8 karakter): Gazdasági szervezet törzsszáma [tax ID number of organization]
M003 (8 karakter): Szakosodott egység törzsszáma (ha nincs, akkor a gazdasági szervezet törzsszáma) [Specialized unit, or tax ID nr]
MEV (2 karakter): Tárgyév két hosszan (23) [tev in two characters]
MHO (2 karakter): Tárgyhó két hosszan (01, 02, .., 12) [tho must be two characters WITH LEADING ZERO]
JHNEV: A kérdõívet jóváhagyó vezetõ neve [Name of executive]
JBEOSZTAS: A kérdõívet jóváhagyó vezetõ beosztása [Status of executive]
JTEL: A kérdõívet jóváhagyó vezetõ telefonszáma [Phone of executive]
JEMAIL: A kérdõívet jóváhagyó vezetõ e-mail címe [E-mail of executive]
KNEV: A kérdõívet kitöltõ neve [Name of contact person]
KBEOSZTAS: A kérdõívet kitöltõ beosztása [Status of contact person]
KTEL: A kérdõívet kitöltõ telefonszáma [Phone of contact person]
KEMAIL: A kérdõívet kitöltõ e-mail címe [E-mail of contact person]
MEGJEGYZES(max. 500 karakter): [Comment, max. 500 characters]
VGEA002(numerikus max. 5 karakter): Kérdõív kitöltésére fordított idõ percekben [Numeric, max. 5 characters, time spent filling in the questionnaire in minutes]
----------------------------------------------------------------
FEJEZET(karakter): Fejezet azonosító, 1 – Kiszállítás [Chapter ID, 1 - Dispatches]
SORREND(numerikus 3 karakter): Kiszállítás fejezet ismétlõdés sorrendje, 1-gyel kezdõdik [Repeat order nr of Dispatches chapter]
T_SORSZ(5 karakter): Tétel sorszáma VEZETÕ NULLÁKKAL[Serial number WITH LEADING ZEROES]
TEKOD(8 karakter): Termék kódja [Commodity code]
UKOD(2 karakter): Ügyletkód [Nature of transaction]
RTA(2 karakter): Rendeltetési tagállam [Member State of destination]
SZAORSZ(2 karakter): Származási ország [Country of origin]
KGM(numerikus(14,3)karakter): Nettó tömeg(kg), 1 kg alatt 3 tizedesjegyig kell megadni tizedesponttal, 1 kg felett egész kg-ra kell kerekíteni
 [Quantity in net mass(kg) is to be declared with three decimals (e.g.0.003); above 1 kg it is to be rounded to kgs];;;;;;;;;;;;;
KIEGME(numerikus(14,3)karakter): Mennyiség kiegészítõ mértékegységben. A KN-ben megjelölt termékekre kötelezõ 
 [Quantity in supplementary units. Only where a supplementary unit is specified to the commoditycode in the CN.]
SZAOSSZ(numerikus 14 karakter): Számlázott összeg Forintban [Invoiced amount (HUF)]
STAERT(numerikus 14 karakter): Statisztikai érték Forintban [Statistical value (HUF)]
PADO(nax. 40 karakter): Partner adószám [Partner tax ID number]
----------------------------------------------------------------

```
[0] = Harmonizációs kód
[1] = Termék megnevezése
[2] = db/kg
[3] = Rendeltetési tagállam
[4] = Származási ország
[5] = Összes nettó tömeg(kg)
[6] = Összes mennyiség db
[7] = Számlázott összeg (ft)
[8] = Partner adószáma
```
