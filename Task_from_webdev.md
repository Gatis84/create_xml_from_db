A) Izveidot PHP skriptu, kas savienojas ar datubāzi, izvelk visus produktus un saglabā tos XML failā products.xml. Fails jāsaglabā tajā pašā mapē, kur php skripts.
Produkti atrodas tabulā `product`. To apraksti `product_description` un atlaides `product_special`.

XML failā jāizvada šādi dati:
1) <model> - produkta artikuls no product.model lauka.
2) <status> - statuss ieslēgts/izslēgts, ko apzīmē ar 1 vai 0. product.status lauks.
3) <name> - nosaukums katrā no 3 valodām. Atrodas product_description tabulā.
Valodām atbilst šādi ID:
Latviešu (LV) = 1
Angļu (EN) = 2
Krievu (RU) = 3

4) <description> - apraksts katrā no 3 valodām. Atrodas product_description tabulā. Apraksts pirms izvades jāsaīsina un beigās jāpieliek daudzpunkte (...). Tam jābūt pēc iespējas garākamkam, bet nedrīkst pārsniegt 200 rakstzīmes + daudzpunkte. Apraksts nedrīkst tikt pārrauts vārda vidū (drīkst pārraut teikuma vidū). Tas vienmēr jānoslēdz vārda beigās.
5) <quantity> - produkta atlikums no product.quantity.
6) <ean> - svītrkods no product.ean
7) <image_url> - attēla path no product.image, kuram priekšā pielikts bāzes URL 'https://www.webdev.lv/'. Atverot URL būs '404 Not Found', bet par to nav jāuztraucas.
8) <date_added> - pievienošanas datums no lauka product.date_added formātā d-m-Y.
9) <price> - cena no product.price.
10) <special_price> - cena no product_special, ja šodienas datums (NOW) ir mazāks vai vienāds ar `date_end`.
    Visas cenas datubāzē glabājas bez PVN. XML failā tās ir jāizvada aprēķinātas ar PVN 21% un noapaļotas līdz diviem cipariem aiz komata. Cipariem aiz komata ir jābūt arī tad, ja tie ir nulles.

B) Papildus uzdevums (nav obligāts). Pie katra produkta izvadīt kategoriju datus:
11) <category> - viszemākā līmeņa kategorijas nosaukums latviešu valodā. Zemākā līmeņa kategorija redzama product_to_category tabulā.
12) <full_category> - kategoriju pilnais ceļš latviešu valodā, ar visām virskategorijām. Kategorijas jāatdala ar ' >> ' (skat. paraugu)


Pielikumā datubāzes tabulu SQL dump un XML parauga fails products_example.xml. Uzģenerētā faila strūktūrai ir jābūt tādai, kā paraugā.
Skriptam ir jāspēj darboties ar MySQL versiju 5.7.39 un PHP 7.4

Ieteicamā sintakse:
* Funkciju un klašu nosaukumus rakstam camelCase
* Funkciju atverošā figūriekava tiek rakstīta tajā pašā rindā, kur funkcijas deklarācija. Noslēdzošā fīgūriekava atsevišķā rindā
* Mainīgo nosaukumus ar mazajiem burtiem, atdalot vārdus ar apakšsvītru
* Indentācijai izmantojam TAB

Lai veicas!